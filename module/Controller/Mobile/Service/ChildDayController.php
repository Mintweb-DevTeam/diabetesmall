<?php
namespace Controller\Front\Service;
use Request;
use Session;
class ChildDayController extends \Controller\Front\Controller
{
	public function index()
	{
		$session = Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/child_day.php");</script>';
			exit;
		}
	}
}