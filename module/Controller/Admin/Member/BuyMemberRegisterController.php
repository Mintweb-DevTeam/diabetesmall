<?php
namespace Controller\Admin\Member;

use Request;
use App;


class BuyMemberRegisterController extends \Controller\Admin\Controller
{
	public function index()
	{
		//if(\Request::getRemoteAddress() != "182.216.219.157"){
		//	exit();	
		//}
		
		$this->callMenu('member', 'buy_member', 'buy_member_register');
        
		$db = App::load('DB');
		
		$param =Request::request()->all();
		
		$idx = $param['idx'];
		$this->setData("idx",$idx);
		if(!empty($idx)){
		
			$strSQL="select * from wm_buyGoodsMember where idx=?";
			$row = $db->slave()->query_fetch($strSQL,['i',$idx]);
			
			$this->setData("data",$row[0]);
		}
		
		
		if($param['mode']=="excel"){
		
			$this->setData("excel",1);
		}
		
		
		
	}

}