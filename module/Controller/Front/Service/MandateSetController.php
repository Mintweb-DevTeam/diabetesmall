<?php
namespace Controller\Front\Service;

use Request;
use Session;

class MandateSetController extends \Controller\Front\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$session = Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
			exit;
		}
		$this->setData('memNo', $session['member']['memNo']);
		$this->setData('memNm', $session['member']['memNm']);
		$this->setData('cellPhone', explode('-',$session['member']['cellPhone']));
		$this->setData('email', $session['member']['email']);
	}
}

