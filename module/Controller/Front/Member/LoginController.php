<?php
namespace Controller\Front\Member;
use Session;
class LoginController extends \Bundle\Controller\Front\Member\LoginController
{
	
	public function pre()
	{
	
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			
				
			//2024.11.25웹앤모바일 추가시작
			
			$direct_goodsView=\Session::get("direct_goodsView");
			if(!empty($direct_goodsView)){
				\Session::del("direct_goodsView");
				$return_url="/goods/goods_view.php?goodsNo=".$direct_goodsView;
				$this->setData("return_url",$return_url);
			}	
			
			$direct_event_goodsView=\Session::get("direct_event_goodsView");
			
			if(!empty($direct_event_goodsView)){
				\Session::del("direct_event_goodsView");
				$return_url="/goods/event_view.php?goodsNo=".$direct_goodsView;
				$this->setData("return_url",$return_url);
			}	
			
			$session_all = \Session::all();
			$session_param="";
			
			foreach($session_all as $session_key => $session_val){
				
				//if(!empty($direct_goodsView) || !empty($direct_event_goodsView)){

				//}
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
			}
			

		
			//2024.11.25웹앤모바일 추가종료
		//}	
	}	
	public function index()
	{
		parent::index();
		$reurl = Session::has('claim') ? urlencode('https://diabetesmall.co.kr/proxyorder/proxy_medical.php') : $this->getData('returnUrl');
		$this->setData('returnUrl', $reurl);
	}
}