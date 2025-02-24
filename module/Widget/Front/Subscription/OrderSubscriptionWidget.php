<?php
namespace Widget\Front\Subscription;

use App;
use Request;

class OrderSubscriptionWidget extends \Widget\Front\Widget 
{
    public function index()
    {
        $obj = App::load(\Component\Subscription\Subscription::class);
        $cfg = $obj->getCfg();
        $this->setData("subCfg", $cfg);
        $scheduleList = $this->getData("scheduleList");
        $this->setData("scheduleList", $scheduleList);
        
        $period = $this->getData("period");
        $deliveryEa = $this->getData("deliveryEa");
        
        $this->setData("period", $period);
        $this->setData("deliveryEa", $deliveryEa);


		$disabled_date= $this->getData("disabled_date");
		$this->setData("disabled_date",$disabled_date);

		$memId = \Session::get("member.memId");


		$db = \App::load(\DB::class);
		
		$cartInfo=$this->getData($cartInfo);

		$autoExtendView='n';
		foreach($cartInfo['cartInfo'] as $key =>$val){
			foreach($val as $key1 =>$val2){
				foreach($val2 as $key2 =>$val3){

					$goodsNo = $val3['goodsNo'];
					$row = $db->query_fetch("select autoExtend from ".DB_GOODS." where goodsNo=?",['i',$goodsNo]);

					
					if($row[0]['autoExtend']=='y')
						$autoExtendView='y';
				}
			}
		}
		
		$this->setData("autoExtendView",$autoExtendView);

		$this->getView()->setPageName('subscription/_order_subscription2');

    }
}