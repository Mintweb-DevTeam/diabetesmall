<?php
namespace Component\Proxyservice;

use App;
use Request;

class ProxyService
{
	private $db="";
	private $secret_key = "dnpqdosahqkdlf#";
	private $secret_iv = "#@$%^&*()_+=-";

	public function __construct()
	{
		if(!is_object($this->db)){
			$this->db= App::load(\DB::class);
		}
	
	}

	public function getCfg()
	{
		$sql="select * from wm_proxy_config";
		$row = $this->db->fetch($sql);
		
		$data['cellPhone']=$row['cellPhone'];
		$data['filePw']=$row['filePw'];
		return $data;
	}


	public function applyList()
	{
		

		$request = App::getInstance('request');	
		$page = $request->get()->get('page');
		$pageNum=$request->get()->get('pageNum');
		
		if(empty($page))
			$page=1;
	
		if(empty($pageNum))
			$pageNum=20;
	
		$first=($page-1)*$pageNum;
		
		$pageObject = new \Component\Page\Page($page_num, 0, 0, $pageNum);


		$tSQL="select count(a.idx) as cnt from wm_proxy_apply a INNER JOIN ".DB_MEMBER." m ON m.memNo=a.memNo where a.agree='y'";
		$tROW=$this->db->query_fetch($tSQL);
		$total=$tROW[0]['cnt'];

		$in=\Request::post()->all();

		$where="";

		
		if(!empty($in['entryDt'][0]) && !empty($in['entryDt'][1])){
			$where.=" and substr(a.regDt,1,10)>='{$in['entryDt'][0]}' and substr(a.regDt,1,10)<='{$in['entryDt'][1]}' ";
		}

		if(!empty($in['searchString'])){
		
			if($in['skey']=="all"){
				$where.=" and ( a.pname like '%{$in['searchString']}%' or m.memId like '%{$in['searchString']}%')";
			}else if($in['skey']=="memId"){
				$where.=" and m.memId like '%{$in['searchString']}%'";
			}else if($in['skey']=="pname"){
				$where.=" and a.pname like '%{$in['searchString']}%'";
			}
		}

		$sql="select count(a.idx) as cnt from wm_proxy_apply a INNER JOIN ".DB_MEMBER." m ON m.memNo=a.memNo where a.agree='y' ".$where;
		$rows = $this->db->query_fetch($sql);
		$amount=$rows[0]['cnt'];

		$sql="select a.*,m.memId,m.memNm from wm_proxy_apply a INNER JOIN ".DB_MEMBER." m ON m.memNo=a.memNo where a.agree='y' ".$where." order by a.idx DESC limit $first,$pageNum";
		$rows = $this->db->query_fetch($sql);

		$pageObject->setTotal($total);
		$pageObject->setCache(true);

		$pageObject->setUrl($request->getQueryString());
		$pageObject->setPage();

		$pageObject->setAmount($amount);
		
		return ["page"=>$pageObject,"data"=>$rows,"total"=>$total,"amount"=>$amount];

	}


	public function DownLogList($data=array())
	{
	
		$in = Request::request()->all();

		$table="wm_proxy_downLog";
		$request = App::getInstance('request');	
		$page = $request->get()->get('page');
		$pageNum=$request->get()->get('pageNum');
		
		if(empty($page))
			$page=1;
	
		if(empty($pageNum))
			$pageNum=20;
	
		$first=($page-1)*$pageNum;
		
		$pageObject = new \Component\Page\Page($page_num, 0, 0, $pageNum);

		$where=" and down_type='{$in['mode']}'";

		$tSQL="select count(a.idx) as cnt from $table a INNER JOIN wm_proxy_apply b ON b.idx=a.apply_idx where a.apply_idx=? $where";
		$tROW=$this->db->query_fetch($tSQL,['i',$in['idx']]);
		$total=$tROW[0]['cnt'];

		

		if(!empty($in['searchString'])){
		
			if($in['skey']=="all"){
				$where.=" and ( a.pname like '%{$in['searchString']}%' or m.memId like '%{$in['searchString']}%')";
			}else if($in['skey']=="memId"){
				$where.=" and m.memId like '%{$in['searchString']}%'";
			}else if($in['skey']=="pname"){
				$where.=" and a.pname like '%{$in['searchString']}%'";
			}
		}



		$sql="select count(a.idx) as cnt from $table a INNER JOIN wm_proxy_apply b ON b.idx=a.apply_idx where a.apply_idx=? $where";
		$rows = $this->db->query_fetch($sql,['i',$in['idx']]);
		$amount=$rows[0]['cnt'];

		$sql="select a.*,b.memNo,b.pname ,b.regDt as bRegDt from $table a INNER JOIN wm_proxy_apply b ON b.idx=a.apply_idx where a.apply_idx=? $where order by a.idx DESC limit $first,$pageNum";
		$rows = $this->db->query_fetch($sql,['i',$in['idx']]);

		//gd_debug($rows);

		$pageObject->setTotal($total);
		$pageObject->setCache(true);

		$pageObject->setUrl($request->getQueryString());
		$pageObject->setPage();

		$pageObject->setAmount($amount);
		
		return ["page"=>$pageObject,"data"=>$rows,"total"=>$total,"amount"=>$amount];

	
	}


	public function chkDel($mode="user",$idx="")
	{

		$server=\Request::server()->toArray();
		$basic_path=$server['DOCUMENT_ROOT']."/data/upfile/";

		$chkDay = date("Y-m-d",strtotime("-1 day"));

		if($mode=="user"){
			$sql="select * from wm_proxy_apply where agree!='y' and substr(regDt,1,10)<='$chkDay'";
		}else if($mode=="admin" && !empty($idx)){
			
			$sql="select * from wm_proxy_apply where idx='$idx'";
		}
		$chkRows = $this->db->query_fetch($sql);

		foreach($chkRows as $k =>$t){
			
		
			if(!empty($t['upfile'])){
				$file = $basic_path.$t['upfile'];
				if(is_file($file)){
					@unlink($file);
				}
			}

			$sql="delete from wm_proxy_apply where idx=?";
			$this->db->bind_query($sql,['i',$t['idx']]);
		
		}
		
	}


	public function Encrypt($str)
	{
		$key = hash('sha256', $this->secret_key);
		$iv = substr(hash('sha256', $this->secret_iv), 0, 32)    ;

		return str_replace("=", "", base64_encode(
					 openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv))
		);
	}


	public function Decrypt($str)
	{
		$key = hash('sha256', $this->secret_key);
		$iv = substr(hash('sha256', $this->secret_iv), 0, 32);

		return openssl_decrypt(
			base64_decode($str), "AES-256-CBC", $key, 0, $iv
		);
	}

}
