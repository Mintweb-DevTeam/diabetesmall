<?php
namespace Controller\Mobile\Libreview;
use Request;
class AgreeEndController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$get = Request::get()->all();
		if( !$get['sno'] ){
			$this->alert('잘못된 접근 입니다.', null, './index.php');
		}
		$this->getView()->setDefine('header', 'libreview/_header.html');
		$this->getView()->setDefine('footer', 'libreview/_footer.html');
		$sql = 'SELECT * FROM `co_aboottAgree` WHERE `sno` = '.$get['sno'];
		$data = $this->db->query_fetch($sql, true)[0];
		$this->setData('data', $data);
		if( $data ){
			$co_Mail = new \Component\Cossia\Mail;
			$param = ['email'=>$data['email'], 'memNm'=>$data['name'], 'cellPhone'=>$data['phone']];
			$co_Mail->agree_send($param);
		}
	}
}