<?php
namespace Controller\Mobile\Member;
use Cookie;

class CoJoinStepaController extends \Controller\Mobile\Controller {
	
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');

        if(\Request::getRemoteAddress()=="182.216.219.157"){
            $this->setData('wmTest',1);
        }
	}
}