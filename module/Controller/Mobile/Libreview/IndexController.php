<?php
namespace Controller\Mobile\Libreview;
class IndexController extends \Controller\Mobile\Controller {
    public function index(){
		$this->getView()->setDefine('header', 'libreview/_header.html');
		$this->getView()->setDefine('footer', 'libreview/_footer.html');
	}
}