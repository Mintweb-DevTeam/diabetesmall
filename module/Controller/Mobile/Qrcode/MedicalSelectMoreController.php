<?php

namespace Controller\Mobile\Qrcode;

use Request;
use App;
class MedicalSelectMoreController extends \Controller\Mobile\Controller
{

	public function index()
	{
	
	
		//2023.07.04 웹앤모바일 추천인 검색 더보기 컨트롤러
		$in = Request::request()->all();
		$db = App::load(\DB::class);
		
		$limit = 10;
		$page = $in['page'];
		
		if(empty($page))
			$page=1;
		
		
		$first	= ($page -1) *$limit;
		$order	= " order by sno DESC limit {$first},{$limit}";
		$where	= " where 1=1";
		
		if(!empty($in['name'])){
			$where.= " and name like '%{$in['name']}%' or code like '%{$in['name']}%'";
		}
		
		$sql	= "select count(sno) as cnt from co_pharmacy ";
		$row	= $db->fetch($sql);
		$amount = $row['cnt'];
		
		$sql	= "select count(sno) as cnt from co_pharmacy ".$where;
		$row	= $db->fetch($sql);
		$total	= $row['cnt'];
		
		$sql	= "select * from co_pharmacy".$where.$order;
		$rows	= $db->query_fetch($sql);
		
		$data = [];
		foreach($rows as $key =>$v){
		
			$data[$key]['sno']=$v['sno'];
			$data[$key]['code']=$v['code'];
			$data[$key]['name']=$v['name'];
		
			$data[$key]['address1']="";
			
			if(!empty($v['address1']))
				$data[$key]['address1']=$v['address1'];

			
			$data[$key]['address2']="";
			
			if(!empty($v['address2']))
				$data[$key]['address2']=$v['address2'];
			
		}
		$this->json($data);
	
		exit;
	}

}

?>