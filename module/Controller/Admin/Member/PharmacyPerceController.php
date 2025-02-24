<?php
namespace Controller\Admin\Member;
use Request;
class PharmacyPerceController extends \Controller\Admin\Controller {
    public function index(){
        $this->callMenu('member', 'pharmacy', 'pharmacy_perce');
		$get = Request::get()->all();
		unset($get['pageNum']);
		$this->setData('get', $get);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->pharmacy_perce_list($get);
		$this->setData('list', $data['list']);
		$this->setData('total', $data['total']);
    }
}