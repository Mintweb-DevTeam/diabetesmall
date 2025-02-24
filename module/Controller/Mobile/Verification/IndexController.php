<?php
namespace Controller\Mobile\Verification;
class IndexController extends \Controller\Mobile\Controller
{
	public function index()
	{
		
		if (!is_object($this->db)) $this->db = \App::load('DB');
	}
}