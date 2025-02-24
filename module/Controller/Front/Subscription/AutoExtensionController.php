<?php
namespace Controller\Front\Subscription;

class AutoExtensionController extends \Controller\Front\Controller
{


	public function index()
	{
		exit;
		//사용안함
		if(\Request::getRemoteAddress()!="175.125.92.173"){

			exit;
		}
		//자동연장
		$db=\App::load(\DB::class);

		$subObj = new \Component\Subscription\Subscription();

		$list = [];
		$stamp = strtotime(date("Ymd"));
		$sql = "SELECT uid, deliveryEa, period FROM wm_subscription_apply WHERE autoExtend='1' ORDER BY regStamp asc";



		if ($tmp = $db->query_fetch($sql)) {


			foreach ($tmp as $li) {

				$sql = "SELECT COUNT(idx) as cnt FROM wm_subscription_schedule_list WHERE uid='{$li['uid']}' AND schedule_stamp >= {$stamp} AND isPayed='0' AND isStop='0'";
				$row = $db->fetch($sql);

						 
				if (empty($row['cnt'])) {

					$subObj->extendSchedule($li['uid'],1,$li['period']);
									 
				}
			}
		} 

		exit;

	
	}

}