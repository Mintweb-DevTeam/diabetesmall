<?php
namespace Controller\Admin\Member;
//2024.07.25 웹앤모바일 -처방전 파일업로드 

use App;
use Request;

class PrescriptionImgUploadController extends \Controller\Admin\Controller
{
	public function index()
	{
		
        $this->callMenu('member', 'adela', 'priscribe_img');
		
		$db = App::load(\DB::class);
		
		$limit=20;
		
		$page=Request::get()->get("page");
		$pageNum=Request::get()->get("pageNum");

		if(empty($pageNum))
			$pageNum=$limit;

		$request = App::getInstance('request');
		$pageObject = new \Component\Page\Page($page, 0, 0, $pageNum);

		if(empty($page))$page=1;

		$first=($page-1)*$limit;

		$where=" where 1=1";
		
		$in = Request::request()->all();
		
		$this->setData($in);
		
		if(!empty($in['regDt'][0]) && !empty($in['regDt'][1])){
		
			$where.=" and date_format(a.regDt,'%Y-%m-%d') BETWEEN '{$in['regDt'][0]}' and  '{$in['regDt'][1]}'";
		}
		
		if(!empty($in['skey']) && !empty($in['searchString'])){
			if($in['skey']=="all"){
				$where.=" and (m.memNm like '%{$in['searchString']}%' or m.memId like '%{$in['searchString']}%')";
			}else{
				$where.=" and m.".$in['skey']." like '%{$in['searchString']}%'";
			}
		}

		$table="wm_prescription_img a LEFT JOIN ".DB_MEMBER." m ON m.memNo=a.memNo";
		
		$strSQL="select a.*,m.memNm,m.memId,m.email,m.cellPhone from ".$table.$where." order by a.idx DESC limit $first,$limit";
		$rows=$db->query_fetch($strSQL);
		
		$this->setData("strSQL",$strSQL);
		
		$strSQL="select count(a.idx) as cnt from ".$table.$where;
		$row=$db->fetch($strSQL);
		$total = $row['cnt'];//검색총갯수
		$pageObject->setTotal($total);


		$strSQL="select count(a.idx) as cnt from ".$table;
		$row=$db->fetch($strSQL);
		$amount=$row['cnt'];//전체총갯수
		$pageObject->setAmount($amount);

		$pageObject->setCache(true);
		$pageObject->setUrl($request->getQueryString());

		$pageObject->setPage();//순서중요
		$this->setData('page', $pageObject);

		$this->setData("data",$rows);
		$this->setData("total",$total);
		
	
		
		
	}

}