<?php
namespace Controller\Front\Proxyorder;

class ProxyMedicalController extends \Controller\Front\Controller
{

	public function index()
	{
		$session = \Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/proxyorder/proxy_medical.php");</script>';
			exit;
		}
		
		$start_date=date("Y년 m월 d일");
		
		$tmp_date=date("Y-m-d",strtotime(" +5 year"));
		$end_date = date("Y년 m월 d일",strtotime($tmp_date."-1 day"));
		
		
		
		$this->setData("start_date",$start_date);
		$this->setData("end_date",$end_date);
		//공백
	}

}