<?php
namespace Controller\Mobile\Main;
use Session;
class IndexController extends \Bundle\Controller\Mobile\Main\IndexController
{
	public function index(){
		parent::index();
		if( Session::get('member.memNo') ){
			$cossia = new \Component\Cossia\Cossia;
			$bool = $cossia->getAgreementItem( Session::get('member.memNo') );
			if( $bool ){	// 필수항목이 n 으로 되어 있음
				echo '<script>alert("필수항목 동의가 필요 합니다. 마이페이지로 이동합니다.");window.location.replace("/mypage/my_page_password.php");</script>';
				exit;
			}
		}
	}
}