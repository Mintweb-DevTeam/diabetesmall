<?php
namespace Controller\Front;

use Request;
use Framework\Utility\StringUtils;

class CommonController
{
	public function index($controller)
	{

        $memId = \Session::get("member.memId");
		//if(\Request::getRemoteAddress()=="182.216.219.50" || $memId=="dev@mintweb.co.kr" || \Request::getRemoteAddress()=="211.49.123.117"){
			$controller->setData("remote",1);
		//}
			
		//2024.10.21웹앤모바일 상품상세 보기후 로그인시 다시 상품상세로 이동 추가시작
		
		/*if(\Request::getRemoteAddress()=="182.216.219.50"){
		}else{
			$direct_goodsView=\Session::get("direct_goodsView");
			
			
			if(!empty($memId) && !empty($direct_goodsView)){
				

				\Session::del("direct_goodsView");
				echo"<script>document.location.href='/goods/goods_view.php?goodsNo={$direct_goodsView}';</script>";
				exit;
			}
		
		}
		
		
		$direct_event_goodsView=\Session::get("direct_event_goodsView");
		
		if(!empty($memId) && !empty($direct_event_goodsView)){
			\Session::del("direct_event_goodsView");
			echo"<script>document.location.href='/goods/event_view.php?goodsNo={$direct_event_goodsView}';</script>";
			exit;
		}		
		*/
		//2024.10.21웹앤모바일 상품상세 보기후 로그인시 다시 상품상세로 이동 추가종료
		
		$controller->setData("chk",1);

		//미승인 신청건은 1일 지난후 삭제시작
		$proxyObj = new \Component\Proxyservice\ProxyService();
		$proxyObj->chkDel();
		//미승인 신청건은 1일 지난후 삭제종료
		
		
		if(\Request::getRemoteAddress()=="211.49.123.117" || \Request::getRemoteAddress()=="112.146.205.124" || \Request::getRemoteAddress()=="182.216.219.157"){
		    $controller->setData("view",1);
		}
		
		
		//2024.01.31웹앤모바일 추가시작
		$now_date=date("Y-m-d");
		//
		//if($now_date <= "2024-04-30"){
			//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
				//$controller->setData("banner_set",1);
			//}
			//$controller->setData("goods_set",1);
		//}
		//2024.01.31웹앤모바일 추가종료
		
		//2024.02.05 웹앤모바일 추가시작
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			$controller->setData("join_simple",1);
		//}
		//2024.02.05 웹앤모바일 추가종료
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			
			//웹앤모바일 2024.06.21 guid생성 시작
			$server = \Request::server()->toArray();
			$GuidCrate = new \Component\Wm\GuidCrate();		
			
			if($server['PHP_SELF'] =="/order/order_end.php"){
				$adobeCartId = $GuidCrate->getuuid("delete");
			}else{
			
				$adobeCartId = $GuidCrate->getuuid();
				$controller->setData("adobeCartId",$adobeCartId);
			
			}
			//웹앤모바일 2024.06.21 guid생성 종료
			$controller->setData("adobe",1);
		//}
		
		if(\Request::getRemoteAddress()=="182.216.219.157"){
			$controller->setData("ip",1);
		}
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			//회원가입시 어도비전송
			$controller->setData("adobe_member",1);
		//}
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			//2024.04.16 웹앤모바일 추가시작
			$prePage = \Session::get('prePage');
			
			if($prePage == 1){
				\Session::del('prePage');
				$this->js("document.location.href='./member/prescription_img_up.php';");
			}
			//2024.04.16 웹앤모바일 추가종료
			//$controller->setData("add_menu",1);
		//}
		
	}
}
?>