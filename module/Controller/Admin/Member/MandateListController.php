<?php
namespace Controller\Admin\Member;
use Request;
class MandateListController extends \Controller\Admin\Controller {
	
    public function index(){
        $this->callMenu('member', 'adela', 'mandate_list');
		$get = Request::get()->all();
		$get['page'] = $get['page'] ? $get['page'] : 1;
		$get['pageNum'] = $get['pageNum'] ? $get['pageNum'] : 10;
		$this->setData('get', $get);
		
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->mandate_list($get);
		$this->setData('list', $data['data']);
		$this->setData('page', $data['page']);
		$this->setData('total', $data['total']);
    }
}