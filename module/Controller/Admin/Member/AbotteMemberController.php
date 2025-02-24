<?php
namespace Controller\Admin\Member;
use Request;
class AbotteMemberController extends \Controller\Admin\Controller {
    public function index(){
		// if (!is_object($this->db)) $this->db = \App::load('DB');
		$this->callMenu('member', 'adela', 'abotte_member');
		
		$get = Request::get()->all();
		$this->setData('get', $get);
		$data = [];
		if( $get['sDate'] || $get['eDate'] ){
			$cossia = new \Component\Cossia\Cossia;
			$data = $cossia->getListAbboteMember($get);
		}
		$this->setData('data', $data);
	}
}

