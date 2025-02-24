<?php

namespace Controller\Front\Subscription;

use App;
use Request;

class ChangeDeliveryController extends \Controller\Front\Controller 
{
    public function index()
    {
        if (!$uid= Request::get()->get("uid"))
            return $this->js("alert('잘못된 접근입니다.');");
        
		$db=\App::load(\DB::class);

		$sql="select * from wm_subscription_apply where uid=?";
		$info = $db->query_fetch($sql,['s',$uid],false);

		$this->setData("uid",$uid);
		$this->setData("period",$info['period']);
        $obj = App::load(\Component\Subscription\Subscription::class);
        $cfg = $obj->getCfg();
        $this->setData("subCfg", $cfg);
        
    }
}