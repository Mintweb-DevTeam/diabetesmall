<?php
namespace Controller\Admin\Order;

use App;
use Request;

class SubscriptionOrderListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->getView()->setDefine("layout", "layout_blank");
        if (!$uid = Request::get()->get("uid"))
            return $this->js("alert('잘못된 접근입니다.');self.close();");
        
        $obj = App::load(\Component\Subscription\SubscriptionAdmin::class);
        $result = $obj->getListByUid($uid);
        $status = $obj->getOrderStatusList();
        $this->setData("status", $status);
     
        $this->setData($result);
    }
}