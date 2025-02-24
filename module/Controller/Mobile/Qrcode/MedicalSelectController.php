<?php

namespace Controller\Mobile\Qrcode;

use Request;
use App;

class MedicalSelectController extends \Controller\Mobile\Controller
{

	public function index()
	{
	
		//2023.07.04 웹앤모바일 추천인 검색팝업창 컨트롤러
		$in = Request::request()->all();
		$db = App::load(\DB::class);
		
		$limit = 10;
		$page = $in['page'];
		
		if(empty($page))
			$page=1;
		
		$request = App::getInstance('request');
		$pageObject = new \Component\Page\Page($page, 0, 0, $limit);

		
		$first	= ($page -1) *$limit;
		$order	= " order by sno DESC limit {$first},{$limit}";
		$where	= " where 1=1";
		
		if(!empty($in['name'])){
			$where.= " and name like '%{$in['name']}%' or code like '%{$in['name']}%'";
		}
		
		$this->setData("name",$in['name']);
		
		$sql	= "select count(sno) as cnt from co_pharmacy ";
		$row	= $db->fetch($sql);
		$amount = $row['cnt'];
		
		$sql	= "select count(sno) as cnt from co_pharmacy ".$where;
		$row	= $db->fetch($sql);
		$total	= $row['cnt'];
		
		$sql	= "select * from co_pharmacy".$where.$order;
		$rows	= $db->query_fetch($sql);
		
		$this->setData("data",$rows);
		$this->setData("total",$total);
		
		
		$pageObject->setTotal($total);
		$pageObject->setCache(true);

		$pageObject->setUrl($request->getQueryString());
		$pageObject->setPage();

		
		$pageObject->setAmount($amount);
		$this->setData('page', $pageObject);

		
	}
}