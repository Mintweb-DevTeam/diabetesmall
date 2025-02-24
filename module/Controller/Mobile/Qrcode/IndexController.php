<?php
namespace Controller\Mobile\Qrcode;

use Cookie;
use Request;

class IndexController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$cookie = Cookie::all();
		$get = Request::get()->all();
		if($cookie['memNm'] && $cookie['memId'] && $cookie['cellPhone']){
			$cossia = new \Component\Cossia\Cossia;
			$cookie['cellPhone'] = $cossia->getCellPhone($cookie['cellPhone']);
			$data = $cossia->abbottRealMemberCheck($cookie);
			if($data['code'] === true){
				if( $cossia->getAgreementItemAbbott($cookie) )
					$this->alert('필수항목 동의가 필요합니다.', null, './co_mem_agree_new.php');
				
				$this->redirect('./co_join_stepe.php?type=uze&sno='.$data['sno'], null, 'self');
				exit;
			}else if ($data['code'] === false){
				Cookie::del('memNm');
				Cookie::del('memId');
				Cookie::del('cellPhone');
			}else{
				$this->alert('필수항목 동의가 필요합니다.', null, './co_mem_agree_new.php');
				exit;
			}
		}
		$this->redirect('./co_join_stepa.php?device=qr&pharmacy_code='.$get['code'], null, 'self');
		exit;
    }
}