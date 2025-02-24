<?php
namespace Controller\Front\Member;

use Request;
use Encryptor;

class CoJoinStepEndController extends \Controller\Front\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$get = Request::get()->all();
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			$get['memId'] = Encryptor::decrypt($get['memId']);
		//}
		
		if( !$get['memId'] && !$get['abbott_sno'] ){
			$this->alert('잘못된 접근 입니다.', null, '/member/co_join_stepa.php');
			exit;
		}
		if( \Session::has('claim') )
			$this->redirect('../member/login.php');
    }
}