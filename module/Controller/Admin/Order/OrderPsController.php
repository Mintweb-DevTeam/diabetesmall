<?php
namespace Controller\Admin\Order;

class OrderPsController extends \Bundle\Controller\Admin\Order\OrderPsController
{


	public function index()
	{
	
	
		
		
		$in = \Request::request()->all();

		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			//2024.10.31웹앤모바일 관리자 결제완료변경시 sms발송시작
			$db = \App::load(\DB::class);
			if($in['mode']=="combine_status_change" || $in['mode']=="modifyOrderStatusDelivery"){
			
				if($in['changeStatus']=="p1"){
				
					$nowDate = date("Y-m-d");
					
					foreach($in['statusCheck'] as $key1 => $val1){
					
						foreach($val1 as $key2 => $val2){
							
							$val_arr = explode("||",$val2);
							
							$orderNo = $val_arr[0];
							$sql="select o.memNo,og.*,oi.orderCellPhone from ".DB_ORDER." o INNER JOIN ".DB_ORDER_GOODS." og ON o.orderNo=og.orderNo INNER JOIN ".DB_ORDER_INFO." oi ON oi.orderNo=og.orderNo where og.orderNo=?";
							$row = $db->query_fetch($sql,['s',$orderNo]);
							
							$goodsNo = $row[0]['goodsNo'];
							
							$eventStartSQL  = "select * from wm_event_goods where goodsNo=?";
							$evntStartROW = $db->query_fetch($eventStartSQL,['i',$goodsNo]);
							
							
							$logSQL="select count(idx) as cnt from wm_event_send_log where memNo=? and cellPhone=? and regDt=?";
							$logRow = $db->query_fetch($logSQL,['iss',$row[0]['memNo'],$row[0]['orderCellPhone'],$nowDate]);

							
							if(!empty($evntStartROW[0]['idx']) && empty($logRow[0]['cnt'])){
								
								$sql = "select * from wm_event_sms";
								$smsRow = $db->fetch($sql);
								
								$str_len = strlen($smsRow['smsTemplate']);
								
								$sendFl="sms";
								if($str_len>90)
									$sendFl="lms";
								$this->wmSms($row[0]['orderCellPhone'],$smsRow['smsTemplate'],$sendFl);
								
								$log = "insert into wm_event_send_log set memNo='{$row[0]['memNo']}',cellPhone='{$row[0]['orderCellPhone']}',regDt='$nowDate'";
								echo $log;
								$db->query($log);
								
							}							
							
						}
					
					}
				}

			}
			//2024.10.31웹앤모바일 관리자 결제완료변경시 sms발송종료
		//}
		
		parent::index();
	}

	//2024.02.01 웹앤모바일 스타트 상품 구매시 sms발송함수 
	public function wmSms($cellPhone, $text="",$sendFl="sms"){
		
		if(empty($text) || empty($cellPhone))
			return false;
		
		$smsAdmin = new \Component\Sms\SmsAdmin;
        $smsUtil = \App::load(\Component\Sms\SmsUtil::class);
		
		
		$param = array(
			'mode' => 'smsSend',
			'sendFl' => $sendFl,
			'directReceiverNumbers' => array($cellPhone),
			'password' => $smsUtil->getPassword(),
			'smsSendType' => 'now',
			'receiverType' => 'direct',
			'smsContents' => $text,
		);
		
		try{
			$smsAdmin->sendSms($param);
		}catch(Exception $e){
			
		}
		
	}	
}