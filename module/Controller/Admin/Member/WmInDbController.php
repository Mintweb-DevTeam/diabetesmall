<?php
namespace Controller\Admin\Member;

//2023.08.08웹앤모바일 메디컬샵 삭제기능추가
class WmInDbController extends \Controller\Admin\Controller
{

	public function index()
	{
	
		$db = \App::load(\DB::class);
		$in	= \Request::request()->all();
		
		$strSQL="delete from co_pharmacy where sno = ?";
		$db->bind_query($strSQL,['i',$in['no']]);
		echo "success";
		exit();
	}


}