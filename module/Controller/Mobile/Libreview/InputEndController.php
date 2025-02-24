<?php
namespace Controller\Mobile\Libreview;
use Request;
use Exception;
class InputEndController extends \Controller\Mobile\Controller {
    public function index(){
		$post = Request::post()->all();
		$cossia = new \Component\Cossia\Cossia;
		if( !$post['name'] ){
			$this->alert('잘못된 접근 입니다.', null, './index.php');
		}
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$sql_ = [];
		foreach( $post as $key => $val ){
			if( $key == 'phone' ) $sql_[] = ' `'.$key.'` = \''.$cossia->getCellPhone($val).'\' ';
			else $sql_[] = ' `'.$key.'` = \''.$val.'\' ';
		}
		
		$sql = 'INSERT INTO `co_aboottAgree` SET '.implode(', ', $sql_);
		$this->db->query($sql);
		$sno = $this->db->query_fetch('SELECT LAST_INSERT_ID()', true)[0]['LAST_INSERT_ID()'];
		echo '<script>alert("완료되었습니다.");parent.location.href = "./agree_end.php?sno='.$sno.'";</script>';
		exit;
	}
	
	
}

