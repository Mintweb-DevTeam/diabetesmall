<?php
namespace Controller\Admin\Member;
use Request;
class AbotteOrderController extends \Controller\Admin\Controller {
    public function index(){
		// if (!is_object($this->db)) $this->db = \App::load('DB');
		$this->callMenu('member', 'adela', 'abotte_order');
		$get = Request::get()->all();
		$this->setData('get', $get);
		$data = [];
		if( $get['sDate'] || $get['eDate'] ){
			$cossia = new \Component\Cossia\Cossia;
			$data = $cossia->getListAbboteOrder($get);
		}
		$this->setData('data', $data);
		$status = [
            'o' => __('주문'),
            'p' => __('입금'),
            'd' => __('배송'),
            's' => __('확정'),
            'c' => __('취소'),
            'f' => __('실패'),
            'b' => __('반품'),
            'e' => __('교환'),
            'r' => __('환불'),
        ];
		$this->setData('status', $status);
	}
}