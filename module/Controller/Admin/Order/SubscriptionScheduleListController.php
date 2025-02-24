<?php
namespace Controller\Admin\Order;

use App;
use Request;

class SubscriptionScheduleListController extends \Controller\Admin\Controller 
{
    public function index() 
    {
        $this->getView()->setDefine("layout", "layout_blank");
        $obj = App::load(\Component\Subscription\Subscription::class);
        $db = App::load('DB');
        
        if (!$uid = Request::get()->get("uid"))
            return $this->js("alert('잘못된 접근입니다.');self.close();");
        
        $list = $obj->getScheduleListByUid($uid);


		$card_cnt=0;
		foreach($list as $k =>$t){
		
			$sql="select count(a.idx) as cnt from wm_subscription_cards a INNER JOIN wm_subscription_apply b ON a.idx=b.idx_card where b.uid='{$t['uid']}'";
			$row = $db->fetch($sql);
			$list[$k]['card']=$row['cnt'];

			if($row['cnt']>0)
				$card_cnt=1;
		}

        $this->setData("list", $list);
        $this->setData("uid", $uid);
        $this->setData("card_cnt", $card_cnt);

    }
}