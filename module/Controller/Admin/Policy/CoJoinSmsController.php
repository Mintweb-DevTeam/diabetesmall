<?php
namespace Controller\Admin\Policy;

class CoJoinSmsController extends \Controller\Admin\Controller
{

	public function index()
	{
		$this->callMenu('policy', 'coJoinConfig', 'jsms');
		
		$db = \App::load(\DB::class);
		$sql="select * from wmJoinSmsConfig";
		$row = $db->query_fetch($sql);
		
		$this->setData("androidContent",$row[0]['androidContent']);
		$this->setData("iphoneContent",$row[0]['iphoneContent']);
		$this->setData("idx",$row[0]['idx']);
		
	}

}