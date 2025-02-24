<?php
namespace Controller\Mobile\Order;
use Session;
class OrderController extends \Bundle\Controller\Mobile\Order\OrderController
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
		
			//if(\Request::getRemoteAddress()=="182.216.219.157"){
				
				//웹앤모바일 2024.06.24 guid생성 시작
				//\Session::del("transactionId",$transactionId);
				//transactionId 아이디 생성
				
				if (!\Session::has('transactionId')) {
					$GuidCrate = new \Component\Wm\GuidCrate();		
					
					$transactionId = $GuidCrate->simpleGetuuid();
					
					\Session::set("transactionId",$transactionId);
					
				}else{
					$transactionId=\Session::get("transactionId");
				}
				$this->setData("transactionId",$transactionId);

			//}		
	}
}