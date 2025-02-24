<?php

namespace Controller\Front\Subscription;

use App;
use Request;

class CronController extends \Controller\Front\Controller 
{
    public function index()
    {
        $obj = App::load(\Component\Subscription\Subscription::class);
        $hash = $obj->getPasswordHash("webnmobile is the best!");
        $hash = Request::post()->get("seed");
        //if (!password_verify("webnmobile is the best!", $hash))
        //    exit;

        $date = date("Ymd");

		$db=\App::load(\DB::class);
       
        /* 결제 목록 추출 */
        $list = $obj->setMode("batch_pay_list")
                      ->setDate($date)
                      ->get();
		
		if(\Request::getRemoteAddress()=="182.216.219.157"){
			unset($list[0]);

		}
		
         if ($list) {
             $obj->setMode("batch_pay");
             foreach ($list as $li) {

				 if(\Request::getRemoteAddress()=="182.216.219.157" && $li['uid']=="2211171001271211"){
					$mrow = $db->fetch("select count(memNo) as cnt from ".DB_MEMBER." where memNo='{$li['memNo']}'");
					
					if(!empty($mrow['cnt']))
						$obj->setIdx($li['idx'])->setBoolean(true)->pay_procss($li['idx']);
				 }else{

					$mrow = $db->fetch("select count(memNo) as cnt from ".DB_MEMBER." where memNo='{$li['memNo']}'");
					
					if(!empty($mrow['cnt']))
						$obj->setIdx($li['idx'])->setBoolean(true)->pay_procss($li['idx']);
				 }

             }
         }

		if(\Request::getRemoteAddress()=="182.216.219.157"){
			exit;
		 }
             
        /* SMS 전송 목록 추출 */

		if(date("H")=="09"){
			$list = $obj->setMode("batch_sms_list")
						  ->setDate($date)
						  ->get();
			
			if ($list) {
				$obj->setMode("batch_sms");
				foreach ($list as $li) {

					$mrow = $db->fetch("select count(memNo) as cnt from ".DB_MEMBER." where memNo='{$li['memNo']}'");
					
					if(!empty($mrow['cnt']))
					   $obj->setIdx($li['idx'])->sms_procss($li['idx']);

				}
			}
		}
		
        /* 자동 연장 처리 */
        $list = $obj->setMode("batch_auto_extend_list")
                      ->setDate($date)
                      ->get();
        if ($list) {
            
            foreach ($list as $li) {
				$obj->setMode("batch_auto_extend");
                $obj->setIdx($li['idx'])->process();
            }
        }
        
        
        exit;
    }
}