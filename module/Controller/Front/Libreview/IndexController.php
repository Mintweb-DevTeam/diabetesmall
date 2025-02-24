<?php
namespace Controller\Front\Libreview;
class IndexController extends \Controller\Front\Controller {
    public function index(){
		$this->redirect('https://m.diabetesmall.co.kr/libreview/');
		exit;
	}
}