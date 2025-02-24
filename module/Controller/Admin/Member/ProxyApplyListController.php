<?php
namespace Controller\Admin\Member;

use Component\Proxyservice\ProxyService;

class ProxyApplyListController extends \Controller\Admin\Controller
{

	public function index()
	{
		$this->callMenu('member', 'adela', 'proxy_apply_list');
		
		$obj = new Proxyservice();

		$result=$obj->applyList();
		
		$this->setData($result);

		$in = \Request::request()->all();

		$this->setData($in);
		
	}

}