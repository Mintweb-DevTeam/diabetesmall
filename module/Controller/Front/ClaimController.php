<?php
namespace Controller\Front;
use Session;
use Request;
class ClaimController extends \Controller\Front\Controller
{
	public function index()
	{
		Session::set('claim', true);
		# if( Session::has('claim') ){
		# 	echo 'claim<br>';
		# }
		# Session::del('claim');
		# echo 'QR URL';
		# https://diabetesmall.co.kr/claim.php
		$url = Request::isMobileDevice() ? 'https://m.diabetesmall.co.kr' : 'https://diabetesmall.co.kr';
		$this->redirect( $url.'/member/co_join_stepb.php' );
		exit;
	}
}

