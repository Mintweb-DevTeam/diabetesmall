<?php
namespace Controller\Admin\Member;
use Request;
class PharmacyRegPsController extends \Controller\Admin\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
        $post = Request::post()->all();
		foreach($post as $key => $val){
			if( $key != 'sno' ) $data[] = ' `'.$key.'` = \''.$val.'\' ';
		}
		$sql = $post['sno'] ? 'UPDATE `co_pharmacy` SET '.implode(', ', $data).' WHERE `sno` = '.$post['sno'] : 'INSERT INTO `co_pharmacy` SET '.implode(', ', $data);
		$this->db->query($sql);
		echo '<script>parent.alert("처리되었습니다.");parent.window.location.replace("./pharmacy_list.php");</script>';
        exit;
    }
}