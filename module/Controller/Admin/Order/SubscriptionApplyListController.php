<?php
namespace Controller\Admin\Order;

use App;
use Request;

class SubscriptionApplyListController extends \Controller\Admin\Controller 
{
    public function index()
    {
        $this->callMenu("order", "subscription", "apply_list");
        
        $obj = App::load(\Component\Subscription\SubscriptionAdmin::class);
        $status = $obj->getOrderStatusList();
        $this->setData("status", $status);
        $result = $obj->getApplyList(10);


        $this->setData($result);
        
        $get = Request::get()->toArray();
        $this->setData("search", $get);


		
    }
}