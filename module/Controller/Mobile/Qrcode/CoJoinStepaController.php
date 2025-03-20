<?php
namespace Controller\Mobile\Qrcode;

use Request;
use Cookie;
use Session;

class CoJoinStepaController extends \Controller\Mobile\Controller {
	
    public function index()
	{
		$get = Request::get()->all();

        if(\Request::getRemoteAddress()=="182.216.219.157"){
            $this->setData('wmTest',1);
        }
		
		if( $get['pharmacy_code'] == 'TRYNOWevent' ) Session::set('join_view', '1');
		else Session::set('join_view', '2');
		
		$sms_code = Session::get("sms_code");
		if($get['pharmacy_code']=="MSCOMMON" && empty($sms_code)){
			Session::set('sms_code', '1');
		}else if($get['pharmacy_code']!="MSCOMMON" && !empty($sms_code)){
			Session::del('sms_code');
		}
		
		$this->setData('get', $get);
		$cookie = Cookie::all();
		
		if($cookie['cellPhone']){
			$cossia = new \Component\Cossia\Cossia;
			$data = $cossia->abbottRealMemberCheck($cookie);
			if($data['code'] === true){
				if( $data['pharmacy_code'] == 'TRYNOWevent' && Session::get('join_view') == '1' ){
					$this->redirect('http://www.cgmedu.co.kr/_online1?ad_name='.$data['memNm'].'&ad_tel='.$data['cellPhone'].'&ad_email='.$data['email'], null, 'self');
				} else if ($data['pharmacy_code'] != 'TRYNOWevent') {
					Session::set('join_view', '2');
					$this->redirect('./co_join_stepe.php?type=uze&sno='.$data['sno'], null, 'self');
				}
			}else if ($data['code'] === false){
				Cookie::del('memNm');
				Cookie::del('memId');
				Cookie::del('cellPhone');
			}else{
				Cookie::del('memNm');
				Cookie::del('memId');
				Cookie::del('cellPhone');
				$this->alert('동의하지 않은 회원 입니다.');
			}
		}
	}
}
