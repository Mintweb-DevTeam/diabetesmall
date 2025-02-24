<?php

namespace Controller\Front\Subscription;

class AutoSmsController extends \Controller\Front\Controller
{

	public function index()
	{
		exit;
		if(\Request::getRemoteAddress()!="175.125.92.173"){

			exit;
		}
		//sms auto 발송
		$db = \App::load(\DB::class);
		$subObj = new \Component\Subscription\Subscription();

		$cfg = $subObj->getCfg();

		$smsDays = $cfg['smsDays']?$cfg['smsDays']:0;
		
		$stamp = //strtotime("2021-10-03");//time();
		$stamp = time();

		
		
		$stamp += (60 * 60 * 24 * $smsDays);
		$estamp = $stamp + (60 * 60 * 24);

		$smsTemplate = $cfg['smsTemplate'];

		$sql = "SELECT a.*,b.memNo,b.orderCellPhone FROM wm_subscription_schedule_list a INNER JOIN wm_subscription_apply b ON a.uid=b.uid WHERE a.schedule_stamp >= {$stamp} AND a.schedule_stamp < {$estamp} AND a.isPayed='0' AND a.isStop='0' AND a.smsStamp='0' ORDER BY a.idx";

		$tmp = $db->query_fetch($sql);



		foreach($tmp as $key =>$li){

			$subObj->updatePayPrice($li['uid']);

			$r = $db->fetch("select payPrice from wm_subscription_schedule_list where idx='{$li['idx']}'");

			if ($info = $db->fetch("SELECT * FROM wm_subscription_apply WHERE uid='".$li['uid']."'")) {

				$items = [];
				$sql = "SELECT * FROM wm_subscription_apply_items WHERE uid='".$li['uid']."'";
				if ($tmps = $db->query_fetch($sql))
					$items = $tmps;
				
				$info['items'] = $items;

				if ($li['orderCellPhone']) {

					$info['date'] = date('Y.m.d', $li['schedule_stamp']);
					$info['payPrice'] = $r['payPrice'];


					if ($subObj->sendSms($li['orderCellPhone'], $smsTemplate, $info)) {

						
						$sql = "UPDATE wm_subscription_schedule_list SET smsStamp='".time()."' WHERE idx='".$li['idx']."'";
						$db->query($sql);
						

					}
					
					
				}
			}							
								
		}

					
		exit();

	}

}