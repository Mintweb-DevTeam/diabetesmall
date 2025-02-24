<?php
namespace Controller\Front\Subscription;

class ExtensionUidController extends \Controller\Front\Controller
{

	public function index()
	{


		$db = \App::load(\DB::class);
		
		
		$uid = \Request::get()->get("uid");
		$this->setData("uid",$uid);

		$sql="select deliveryEa,period from wm_subscription_apply where uid=?";
		$row = $db->query_fetch($sql,['s',$uid],false);

		$memId = \Session::get("member.memId");


			$subObj = new \Component\Subscription\Subscription();
			$subCfg = $subObj->getCfg();
			
			$sql="select count(idx) as cnt from wm_subscription_schedule_list where uid='$uid' and isPayed='1'";
			$srow=$db->query_fetch($sql,['i',$uid],false);

			$cnt = $srow['cnt']% $row['deliveryEa'];
			
			$deliveryEaLoop=[];
			foreach($subCfg['deliveryEa'] as $k =>$t){
				if($row['deliveryEa']<$t){
					$deliveryEaLoop[]=$t;
				}else if($row['deliveryEa']>$t && $t>$cnt){
					$deliveryEaLoop[]=$t;
				}
			}
			
			$this->setData("deliveryEaLoop",$deliveryEaLoop);


		$this->setData("period",$row['period']);
	}

}