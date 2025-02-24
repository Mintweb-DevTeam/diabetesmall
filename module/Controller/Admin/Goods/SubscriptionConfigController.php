<?php
namespace Controller\Admin\Goods;
use App;

class SubscriptionConfigController extends \Controller\Admin\Controller 
{
    public function index()
    {
        $this->callMenu("goods", "subscription", "config");
        $obj = App::load("\Component\Subscription\Subscription");
        $cfg = $obj->getCfg();
        $this->setData($cfg);
    }
}