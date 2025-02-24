<?php
namespace Controller\Admin\Order;

use App;
use Request;

class SubscriptionOrderListAllController extends \Controller\Admin\Controller 
{
    public function index()
    {
        $this->callMenu("order", "subscription", "order_list");
        $obj = App::load(\Component\Subscription\SubscriptionAdmin::class);
        $status = $obj->getOrderStatusList();
        $result = $obj->orderList();
        $this->setData("status", $status);
        $this->setData($result);
        
        $get = Request::get()->toArray();
        $this->setData("search", $get);
       
    }
}