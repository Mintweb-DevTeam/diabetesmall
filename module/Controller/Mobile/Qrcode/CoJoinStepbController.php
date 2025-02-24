<?php
namespace Controller\Mobile\Qrcode;

use Request;

class CoJoinStepbController extends \Controller\Mobile\Controller
{
	
    public function index()
	{
		$this->setData('get', Request::get()->all());
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="112.146.205.124" || \Request::getRemoteAddress()=="211.49.123.117"){
			//$this->setData("remote",2);	
			
			
		//}
    }
}