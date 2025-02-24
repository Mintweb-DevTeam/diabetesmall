<?php
namespace Controller\Admin\Member;
use Request;
class BloodAjaxController extends \Controller\Admin\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$sql = 'SELECT `controlNo`, `patientName`, `patientBirth`, `fileData` FROM `co_bloodInfo` WHERE `sno` = '.Request::post()->get('sno');
		$data = $this->db->query_fetch($sql, true)[0];
		$data['fileData'] = json_decode($data['fileData'], true);
		$this->json($data);
		exit;
	}
}