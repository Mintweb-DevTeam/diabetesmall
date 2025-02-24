<?php
namespace Controller\Mobile\Member;

use Request;

class PharmacyAjaxController extends \Controller\Mobile\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		$sql = 'SELECT * FROM `co_pharmacy` WHERE `code` = \''.$post['code'].'\'';
		$data = $this->db->query_fetch($sql, true);
		$this->json($data[0]);
		exit;
	}
}