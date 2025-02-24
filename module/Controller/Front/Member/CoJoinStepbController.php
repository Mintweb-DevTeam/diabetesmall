<?php
namespace Controller\Front\Member;

use Request;

class CoJoinStepbController extends \Controller\Front\Controller
{
	
    public function index()
	{
		$get = Request::get()->all();
		$this->setData('get', $get);
    }
}