<?php
namespace Controller\Front\Member;

class CoJoinStepaController extends \Controller\Front\Controller {
	
    public function index(){
		$this->setData('device', 'pc');

        if(\Request::getRemoteAddress()=="182.216.219.157"){
            $this->setData('wmTest',1);
        }
	}
}