<?php
namespace Controller\Front\Service;

use Request;
use Session;

class MandateEndController extends \Controller\Front\Controller
{
	
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$session = Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
			exit;
		}
		$sql = 'SELECT `regDt` FROM `co_mandate` WHERE `memNo` = '.$session['member']['memNo'];
		$sno = $this->db->query_fetch($sql, true);
		if(!$sno[0]['regDt']){
			$this->setData('memNo', $session['member']['memNo']);
			$this->setData('memNm', $session['member']['memNm']);
			$this->setData('cellPhone', explode('-',$session['member']['cellPhone']));
			$this->setData('email', $session['member']['email']);
		}else{
			$this->setData('regDt', $sno[0]['regDt']);
		}
	}
}

