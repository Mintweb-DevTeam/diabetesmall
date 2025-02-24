<?php
namespace Controller\Mobile\Member;

use Request;
use Encryptor;
class CoJoinStepEndController extends \Controller\Mobile\Controller
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
		$db = \App::load('DB');
		$sql = 'SELECT memNm, cellPhone, email FROM `es_member` WHERE `memId` = \''.$get['memId'].'\'';
		$this->setData('data', $db->query_fetch($sql)[0]);
		if( \Session::has('claim') )
			$this->redirect('../member/login.php');
    }
}