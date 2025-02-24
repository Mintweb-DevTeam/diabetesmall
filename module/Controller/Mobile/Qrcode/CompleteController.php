<?php
namespace Controller\Mobile\Qrcode;
use Request;
class CompleteController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$get = Request::get()->all();
		if( !$get['type'] && !$get['memId'] && !$get['cellPhone'] && !$get['memNm'] && !$get['memPw'] ){
			$this->alert('잘못된 접근 입니다.', null, '/Qrcode/');
		}
		if($get['type'] == 'success'){
			foreach($get as $key => $val){
				$this->setData($key, $val);
			}
		}
	}
}