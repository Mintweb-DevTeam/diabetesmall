<?php
namespace Controller\Front\Service;

use Request;
use Component\Cossia\Mail;
class MandatePsController extends \Controller\Front\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		$charsang = ( $post['charsang'] == 'y' ) ? ', `charsang` = \'y\' ' : '';
		$sql = 'INSERT INTO `co_mandate` SET `memNo` = '.$post['memNo'].', `relation` = \''.$post['relation'].'\''.$charsang;
		$result = ($post['memNo']) ? $this->db->query($sql) : false;
		if($result){
			$sql = 'SELECT `cellPhone` FROM `es_member` WHERE `memNo` = '.$post['memNo'];
			$cellPhone =  $this->db->query_fetch($sql, true);
			if( $cellPhone[0]['cellPhone'] ) {
				$mail = new Mail;
				$mail->mam_date_sms($cellPhone[0]['cellPhone']);
			}
			echo '<script>parent.alert("처리되었습니다.");parent.window.location.replace("./mandate_end.php");</script>';
		}else{
			echo '<script>parent.alert("일시적 오류 입니다.\n다시 시도해주세요");parent.window.location.reload();</script>';
		}
		exit;
	}
}

