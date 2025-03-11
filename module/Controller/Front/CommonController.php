<?php
namespace Controller\Front;

use Request;
use Framework\Utility\StringUtils;
use Session;
use Validation;

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


        /* 웹앤모바일 카카오싱크 수정 25-03-11 - kakaosyncReturnUrl 세팅 */
        $request = \App::getInstance('request');
        $phpSelf = gd_php_self();
        $urlDefaultCheck = true;

        if ($request->request()->has('returnUrl')) {
            // returnUrl 데이타 타입 체크
            try {
                Validation::setExitType('throw');
                Validation::defaultCheck(gd_isset($request->request()->get('returnUrl')), 'url');
            } catch (\Exception $e) {
                $urlDefaultCheck = false;
                $kakaosyncReturnUrl = $request->getReferer();
            }

            if ($urlDefaultCheck) {
                $kakaosyncReturnUrl = $request->getReturnUrl();// url의 특이한 형태로 인해 치환코드 설정
                // 웹앤모바일 수정 21-09-10 - 본 조건에서는 returnUrl이 원하는 형태로 생성되지않아서 형태를 맞춰줌
                $kakaosyncReturnUrl = $request->getScheme() . "://" . $request->getServerName() . $kakaosyncReturnUrl;
            }
        } else {
            $kakaosyncReturnUrl = $request->getReferer();
            // 로그인, 회원가입 페이지나 PS컨트롤러가 아니면 카카오싱크 returnUrl 재정의
            if (strpos($phpSelf, 'member/login.php') === false && strpos($phpSelf, 'member/join_method.php') === false && strpos($phpSelf, '_ps.php') === false) {
                $kakaosyncReturnUrl = $request->getScheme() . "://" . $request->getServerName() . $request->getRequestUri();
            }
        }
        $controller->setData('kakaosyncReturnUrl', urlencode($kakaosyncReturnUrl));
        /* 웹앤모바일 수정 끝 */
	}
}
?>