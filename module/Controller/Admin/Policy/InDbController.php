<?php
namespace Controller\Admin\Policy;

class InDbController extends \Controller\Admin\Controller
{

	public function index()
	{
		$in = \Request::request()->all();
		$db = \App::load(\DB::class);
		
		$ip = \Request::getRemoteAddress();
		
		$table="wmJoinSmsConfig";
		
		$sql="delete from ".$table;
		$db->query($sql);
		
		$sql="insert into ".$table." set androidContent='{$in['androidContent']}',iphoneContent='{$in['iphoneContent']}',ip='{$ip}',regDt=sysdate()";
		$db->query($sql);
		$this->layer("처리되었습니다.");
		exit();
	
	}

}