<?php
namespace Controller\Front\Member;

use Cookie;
use Request;
use Exception;
use Session;
use Framework\Debug\Exception\AlertBackException;
class PrescriptionImgUpController extends \Controller\Front\Controller
{
	public function index()
	{
		
		
		if(Session::has('member')) {
			Session::del('prePage');
			
			
		} else {
			Session::set('prePage', 1);
			$this->js("document.location.href='./login.php';");
			//throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
		}		
	
	}

}