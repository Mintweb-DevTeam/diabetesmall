<?php
namespace Controller\Admin\Goods;

use App;

class SubscriptionHolidayController extends \Controller\Admin\Controller 
{
    public function index()
    {
        $this->callMenu("goods", "subscription", "holiday");
        
        $obj = App::load(\Component\Subscription\Subscription::class);
        $list = $obj->getHolidayList();
        $this->setData("list", $list);
    }
}