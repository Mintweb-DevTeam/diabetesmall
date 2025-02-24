<?php
namespace Controller\Admin\Member;
use Request;
class PharmacyListController extends \Controller\Admin\Controller {
    public function index(){
        $this->callMenu('member', 'pharmacy', 'pharmacy_list');
		$get = Request::get()->all();
		$get['page'] = $get['page'] ? $get['page'] : 1;
		$get['pageNum'] = $get['pageNum'] ? $get['pageNum'] : 10;
		$this->setData('get', $get);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->pharmacy_list($get);
		$this->setData('list', $data['data']);
		$this->setData('page', $data['page']);
		$this->setData('total', $data['total']);
		$this->setData('qr_url', str_replace('gdadmin', 'm', Request::server()->get('HTTP_HOST')));
    }
}