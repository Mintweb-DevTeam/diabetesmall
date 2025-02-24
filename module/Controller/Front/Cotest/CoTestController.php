<?php
namespace Controller\Front\Cotest;

use Request;
use Component\Cossia\Mail;

class CoTestController extends \Controller\Front\Controller
{
	public function index()
	{
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->getEduAuth();
		print_r($data);
		// 
		
		
 
		/*
		$mail = new Mail;
		$param = ['email'=>'cossia@naver.com', 'memNm'=>'홍길동', 'cellPhone'=>'010-5035-0219'];
		$mail->adela_join($param);
		
		print_r($result);
		*/
		exit;
	}
}

