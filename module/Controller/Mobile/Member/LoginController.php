<?php
namespace Controller\Mobile\Member;
use Session;
class LoginController extends \Bundle\Controller\Mobile\Member\LoginController
{
	
	public function pre()
	{
	
			$direct_goodsView=\Session::get("direct_goodsView");
			if(!empty($direct_goodsView)){
				Session::del("direct_goodsView");
				$return_url="/goods/goods_view.php?goodsNo=".$direct_goodsView;
				$this->setData("return_url",$return_url);
			}
			
			
			$direct_event_goodsView=\Session::get("direct_event_goodsView");
			
			if(!empty($direct_event_goodsView)){
				\Session::del("direct_event_goodsView");
				$return_url="/goods/event_view.php?goodsNo=".$direct_goodsView;
				$this->setData("return_url",$return_url);
			}		
		if(\Request::getRemoteAddress()=="182.216.219.157"){
		//exit;	
		}
			//2024.11.25웹앤모바일 추가시작
			$session_all = \Session::all();
			$session_param="";
			
			$params = \Request::get()->toArray();
			
			$memNo = \Session::get("member.memNo");
			
			//if(\Request::getRemoteAddress()=="182.216.219.157"){
				
				$urlParts = parse_url($params['returnUrl']);
				

				// 쿼리 문자열을 파싱하여 배열로 변환합니다.
				parse_str($urlParts['query'], $queryParams);

				$direct_goodsNo = $queryParams['goodsNo'] ?? null;
			//}
			
			
			
			foreach($session_all as $session_key => $session_val){
				
				if(preg_match("/param_field_/",$session_key)){
					
					$session_key_arr = explode("_",$session_key);
					
					if($session_key_arr[2] == "goodsNo")
						continue;					
					
					$session_param.="&".$session_key_arr[2]."=".$session_val;	
					\Session::del($session_key);
				}
			}
			
			if(!empty($session_param)){
				$this->setData("session_param",$session_param);
			}else{
			
				if(empty($memNo) && !empty($direct_goodsNo) && empty($direct_goodsView) && empty($direct_event_goodsView)){
					$return_url="/goods/goods_view.php?goodsNo=".$direct_goodsNo;
					$this->setData("return_url",$return_url);
				}			
			}
			
		
			//2024.11.25웹앤모바일 추가종료
		//}	
	}
	
	
	public function index()
	{

//        if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="118.176.136.91" || \Request::getRemoteAddress()=="211.49.123.117"){
//            $this->setData('wmTest',1);
//        }

		parent::index();
		
		
		$reurl = Session::has('claim') ? urlencode('https://m.diabetesmall.co.kr/proxyorder/proxy_medical.php') : $this->getData('returnUrl');
		$this->setData('returnUrl', $reurl);
	}
}