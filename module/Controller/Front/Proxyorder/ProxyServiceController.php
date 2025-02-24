<?php
namespace Controller\Front\Proxyorder;

use App;
use Request;

class ProxyServiceController extends \Controller\Front\Controller
{

	public function index()
	{
		$session = \Session::all();
		//if( !$session['member']['memNo'] ){
		//	echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
		//	exit;
		//}
		$proxy = new \Component\Proxyservice\ProxyService();
		$cfg = $proxy->getCfg();
		$memInfo=\Session::get("member");

		$this->setData("memNm",$memInfo['memNm']);
		$this->setData("cellPhone",str_replace("-","",$memInfo['cellPhone']));
		$this->setData("cfg",$cfg);

		
		if(\Request::getRemoteAddress()=="182.216.219.157"){
			$this->getView()->setPageName('proxyorder/proxy_service_new.php');	
		}
		
	}

}