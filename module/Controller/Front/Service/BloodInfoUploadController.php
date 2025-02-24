<?php
namespace Controller\Front\Service;
use Request;
use Session;
class BloodInfoUploadController extends \Controller\Front\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$session = Session::all();
		
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/blood_info_upload.php");</script>';
			exit;
		}
		$sql = 'SELECT `patientName`, `patientBirth`, `agent`, `gender` FROM `co_bloodInfo` WHERE `memNo` = '.$session['member']['memNo'].' ORDER BY `sno` DESC LIMIT 1';
		$memInfo = $this->db->query_fetch($sql, true)[0];
		$this->setData('memInfo', $memInfo);
	}
}