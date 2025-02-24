<?php
namespace Controller\Mobile\Member;

use Request;

class CoJoinStepbController extends \Controller\Mobile\Controller
{
	
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
    }
}