<?php
namespace Controller\Admin\Member;
use Request;
class PharmacyRegController extends \Controller\Admin\Controller {
    public function index(){
        $this->callMenu('member', 'pharmacy', 'pharmacy_reg');
		if (!is_object($this->db)) $this->db = \App::load('DB');
        $get = Request::get()->all();
		if($get['sno']){
			$sql = 'SELECT * FROM `co_pharmacy` WHERE `sno` = '.$get['sno'];
			$data = $this->db->query_fetch($sql, true);
			$this->setData('data', $data[0]);
		}
		/*
		echo '<script>parent.alert("내정보가 수정되었습니다");parent.window.location.replace("/mypage/");</script>';
        exit;		
		*/
    }
}