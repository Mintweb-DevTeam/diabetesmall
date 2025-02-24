<?php
namespace Widget\Front\Subscription;

use App;
use Session;

class SelectCardWidget extends \Widget\Front\Widget
{
    public function index()
    {
         if (!gd_is_login()) 
             return;
         
         $obj = App::load(\Component\Subscription\Subscription::class);
         $cfg = $obj->getCfg();
         

        $member = Session::get("member");
        $this->setData("member", $member);
        
        $list = $obj->getCards($member['memNo']);
        $this->setData("list", $list);
         
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			
			//웹앤모바일 2024.06.21 guid생성 시작
			$server = \Request::server()->toArray();
			$GuidCrate = new \Component\Wm\GuidCrate();		
			
			if($server['PHP_SELF'] =="/order/order_end.php"){
				$adobeCartId = $GuidCrate->getuuid("delete");
			}else{
			
				$adobeCartId = $GuidCrate->getuuid();
				$this->setData("adobeCartId",$adobeCartId);
			
			}
			//웹앤모바일 2024.06.21 guid생성 종료
			
			
			$cartInfo = $this->getData("cartInfo");
			$this->setData("cartInfo",$cartInfo);
			
			
		//}
		
    }
}