<?php
namespace Controller\Admin\Member;

use Component\Proxyservice\ProxyService;

class DownLogController extends \Controller\Admin\Controller
{

	public function index()
	{
		$this->getView()->setDefine("layout", "layout_blank");
		
		
		$ProxyService = new ProxyService();

		$result=$ProxyService->DownLogList();

		$in = \Request::request()->all();

		if($in['mode']=="j")
			$title="다운로드";
		else
			$title="저장및인쇄";

		$this->setData("title",$title);
		$this->setData($result);

		//gd_debug($result);
		//exit;
		
		
	}

}