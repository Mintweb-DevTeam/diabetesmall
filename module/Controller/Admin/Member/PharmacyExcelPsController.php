<?php
namespace Controller\Admin\Member;
use Request;
class PharmacyExcelPsController extends \Controller\Admin\Controller {
	public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		$max_ = count($post['name']);
		for($i=0; $i<$max_; $i++){
			$sql_ = ($post['code'][$i]) ? ' `code` = \''.$post['code'][$i].'\' ' : ' `code` = NULL ';
			$sql_ .= ($post['name'][$i]) ? ', `name` = \''.$post['name'][$i].'\' ' : ', `name` = NULL ';
			$sql_ .= ($post['phone'][$i]) ? ', `phone` = \''.$post['phone'][$i].'\' ' : ', `phone` = NULL ';
			$sql_ .= ($post['post'][$i]) ? ', `post` = \''.$post['post'][$i].'\' ' : ', `post` = NULL ';
			$sql_ .= ($post['address1'][$i]) ? ', `address1` = \''.$post['address1'][$i].'\' ' : ', `address1` = NULL ';
			$sql_ .= ($post['address2'][$i]) ? ', `address2` = \''.$post['address2'][$i].'\' ' : ', `address2` = NULL ';
			
			$sql = ($post['sno'][$i]) ? 'UPDATE `co_pharmacy` SET '.$sql_.' WHERE `sno` = '.$post['sno'][$i] : 'INSERT INTO `co_pharmacy` SET '.$sql_;
			$this->db->query($sql);
		}
		echo '<script>parent.alert("처리되었습니다.");parent.window.location.replace("./pharmacy_list.php");</script>';
		exit;
	}
}

