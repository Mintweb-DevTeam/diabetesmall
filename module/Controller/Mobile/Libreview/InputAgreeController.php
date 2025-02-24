<?php
namespace Controller\Mobile\Libreview;
use Request;
class InputAgreeController extends \Controller\Mobile\Controller {
    public function index(){
		$post = Request::post()->all();
		if( !$post['name'] ){
			$this->alert('잘못된 접근 입니다.', null, './index.php');
		}
		$this->setData('post', $post);
		$this->getView()->setDefine('header', 'libreview/_header.html');
		$this->getView()->setDefine('footer', 'libreview/_footer.html');
		
		$this->getView()->setDefine('agree1', 'libreview/_agree1.html');
		$this->getView()->setDefine('agree2', 'libreview/_agree2.html');
		$this->getView()->setDefine('agree3', 'libreview/_agree3.html');
		$this->getView()->setDefine('agree4', 'libreview/_agree4.html');
		$this->getView()->setDefine('agree5', 'libreview/_agree5.html');
	}
}