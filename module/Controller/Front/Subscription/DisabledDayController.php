<?php
namespace Controller\Front\Subscription;

class DisabledDayController extends \Controller\Front\Controller
{
	public function index()
	{
		$result=[];

		$db = \App::load(\DB::class);
		$param = \Request::post()->toArray();

		$ym = $param['year']."-".str_pad($param['month'], 2, "0", STR_PAD_LEFT);

		$sql="select from_unixtime(stamp,'%Y-%c-%e') as regDt from wm_subscription_holiday where from_unixtime(stamp,'%Y-%m')='$ym'";

		$rows = $db->query_fetch($sql);

		foreach($rows as $k =>$t){
		
			$result[]=$t['regDt'];
		}
		
		echo json_encode($result);
		
		exit;
	
	}

}