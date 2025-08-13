<?php
namespace Controller\Front\Member;

class CoJoinStepaController extends \Controller\Front\Controller {
	
    public function index(){
		$this->setData('device', 'pc');

//        if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="118.176.136.91" || \Request::getRemoteAddress()=="211.49.123.117"){
//            $this->setData('wmTest',1);
//        }
	}
}