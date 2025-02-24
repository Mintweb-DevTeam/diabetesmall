<?php
namespace Controller\Mobile\Qrcode;
class NewMemberController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
    }
}