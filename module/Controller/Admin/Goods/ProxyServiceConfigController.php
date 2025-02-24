<?php
namespace Controller\Admin\Goods;

class ProxyServiceConfigController extends \Controller\Admin\Controller
{

	public function index()
	{

		$this->callMenu("goods", "proxysevice", "proxyconfig");
		
		$proxy = new \Component\Proxyservice\ProxyService();
		$cfg = $proxy->getCfg();

		$this->setData($cfg);
	}

}