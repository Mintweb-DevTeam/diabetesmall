<?php
namespace Controller\Mobile\Order;
use Request;
use Exception;

class OrderEndController extends \Bundle\Controller\Mobile\Order\OrderEndController
{
	public function index()
	{
		parent::index();
		$cossia = new \Component\Cossia\Cossia;
		$codata = $cossia->getEduAuth( Request::get()->get('orderNo') );
		$this->setData('codata', $codata);
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			//2024.02.01 웹앤모바일 스타트 상품 구매시 링크 발송 시작
			$memNo = \Session::get("member.memNo");
			$db = \App::load(\DB::class);
			$orderNo = Request::get()->get('orderNo');
			
			if(!empty($memNo)){
				
				//if(\Request::getRemoteAddress()=="182.216.219.157"){
					
					$event_goodsNo = \Session::get("event_goodsNo");//2024.10.21웹앤모바일 추가-이벤트스타트 프로그램 조회여부
					if(empty($event_goodsNo)){
						//2024.10.18웹앤모바일 추가 및 변경 시작
						///기존 start goods 시작
						$sql = "select b.* from ".DB_ORDER_GOODS." og INNER JOIN  wm_start_goods b ON b.goodsNo=og.goodsNo where og.orderNo=? and (substr(og.orderStatus,1,1)='p' or substr(og.orderStatus,1,1)='o')";
						

						$row = $db->query_fetch($sql,['s',$orderNo]);
						
						
						$nowDate = date("Y-m-d");
						if(!empty($row[0]['idx'])){
							
							$this->setData("start_goods",1);
							
							$orderInfoSQL ="select * from ".DB_ORDER_INFO." where orderNo=?";
							$orderInfoROW = $db->query_fetch($orderInfoSQL,['s',$orderNo],false);
							
							$cellPhone = str_replace("-",'',$orderInfoROW['orderCellPhone']);
							$email = $orderInfoROW['orderEmail'];
							$memNm = $orderInfoROW['orderName'];

							$type = 'sha512';
							$encryptKey = "Vo4YzlGm12";
							$data = $encryptKey.$cellPhone;
							$encCellPhone = hash($type, $data);
				
							$this->setData("name",$memNm);
							$this->setData("hp",$cellPhone);
							$this->setData("hpCipher",$encCellPhone);
							$this->setData("email",$email);
											
							
							$memSQL = "select * from ".DB_MEMBER." where memNo=?";
							$memRow = $db->query_fetch($memSQL,['i',$memNo]);
							
							$logSQL="select count(idx) as cnt from wm_start_send_log where memNo=? and cellPhone=? and regDt=? and start_goods_idx=?";
							$logRow = $db->query_fetch($logSQL,['issi',$memNo,$memRow[0]['cellPhone'],$nowDate,$row[0]['idx']]);
							

							if($logRow[0]['cnt']<=0){
								$sql = "select * from wm_start_sms";
								$smsRow = $db->fetch($sql);
								
								$str_len = strlen($smsRow['smsTemplate']);
								
								$sendFl="sms";
								if($str_len>90)
									$sendFl="lms";
								
								$this->wmSms($memRow[0]['cellPhone'],$smsRow['smsTemplate'],$sendFl);
								
								$log = "insert into wm_start_send_log set memNo='$memNo',cellPhone='{$memRow[0]['cellPhone']}',regDt='$nowDate'";
							
								$db->query($log);
							}
						}					
						
						///기존 start goods 종료
						
						//추가 start goods 시작
						$other_sql = "select b.* from ".DB_ORDER_GOODS." og INNER JOIN  wm_start_goods b ON b.other_goodsNo=og.goodsNo where og.orderNo=? and (substr(og.orderStatus,1,1)='p' or substr(og.orderStatus,1,1)='o')";
						
						$other_row = $db->query_fetch($other_sql,['s',$orderNo]);
						
						
						if(!empty($other_row[0]['idx'])){
							
							$this->setData("other_start_goods",1);
							
							$orderInfoSQL ="select * from ".DB_ORDER_INFO." where orderNo=?";
							$orderInfoROW = $db->query_fetch($orderInfoSQL,['s',$orderNo],false);
							
							$cellPhone = str_replace("-",'',$orderInfoROW['orderCellPhone']);
							$email = $orderInfoROW['orderEmail'];
							$memNm = $orderInfoROW['orderName'];

							$type = 'sha512';
							$encryptKey = "Vo4YzlGm12";
							$data = $encryptKey.$cellPhone;
							$encCellPhone = hash($type, $data);
				
							$this->setData("other_name",$memNm);
							$this->setData("other_hp",$cellPhone);
							$this->setData("other_hpCipher",$encCellPhone);
							$this->setData("other_email",$email);
											
							
							$memSQL = "select * from ".DB_MEMBER." where memNo=?";
							$memRow = $db->query_fetch($memSQL,['i',$memNo]);
							
							$logSQL="select count(idx) as cnt from wm_start_send_log where memNo=? and cellPhone=? and regDt=? and start_goods_idx=?";
							$logRow = $db->query_fetch($logSQL,['issi',$memNo,$memRow[0]['cellPhone'],$nowDate,$other_row[0]['idx']]);
							

							if($logRow[0]['cnt']<=0){
								$sql = "select * from wm_start_sms";
								$smsRow = $db->fetch($sql);
								
								$str_len = strlen($smsRow['other_smsTemplate']);
								
								$sendFl="sms";
								if($str_len>90)
									$sendFl="lms";
								
								$this->wmSms($memRow[0]['cellPhone'],$smsRow['other_smsTemplate'],$sendFl);
								
								$log = "insert into wm_start_send_log set memNo='$memNo',cellPhone='{$memRow[0]['cellPhone']}',regDt='$nowDate',start_goods_idx='{$other_row[0]['idx']}'";
							
								$db->query($log);
							}
						}		
						//추가 start goods 종료
						//2024.10.18웹앤모바일 추가 및 변경 종료
					}else if(!empty($event_goodsNo)){
						//이벤트 스타트 프로그램시작
						
						$sql = "select b.* from ".DB_ORDER_GOODS." og INNER JOIN  wm_event_goods b ON b.goodsNo=og.goodsNo where og.orderNo=? and (substr(og.orderStatus,1,1)='p' or substr(og.orderStatus,1,1)='o')";
						

						$row = $db->query_fetch($sql,['s',$orderNo]);
						
						
						$nowDate = date("Y-m-d");
						if(!empty($row[0]['idx'])){
							
							\Session::del('event_goodsNo');
							
							$this->setData("event_goods",1);
							
							$orderInfoSQL ="select * from ".DB_ORDER_INFO." where orderNo=?";
							$orderInfoROW = $db->query_fetch($orderInfoSQL,['s',$orderNo],false);
							
							$cellPhone = str_replace("-",'',$orderInfoROW['orderCellPhone']);
							$email = $orderInfoROW['orderEmail'];
							$memNm = $orderInfoROW['orderName'];

							$type = 'sha512';
							$encryptKey = "Vo4YzlGm12";
							$data = $encryptKey.$cellPhone;
							$encCellPhone = hash($type, $data);
				
							$this->setData("name",$memNm);
							$this->setData("hp",$cellPhone);
							$this->setData("hpCipher",$encCellPhone);
							$this->setData("email",$email);
											
							
							$memSQL = "select * from ".DB_MEMBER." where memNo=?";
							$memRow = $db->query_fetch($memSQL,['i',$memNo]);
							
							$logSQL="select count(idx) as cnt from wm_event_send_log where memNo=? and cellPhone=? and regDt=?";
							$logRow = $db->query_fetch($logSQL,['iss',$memNo,$memRow[0]['cellPhone'],$nowDate]);
							

							//if($logRow[0]['cnt']<=0){
							if($logRow[0]['cnt']<=0 && $orderInfoROW['orderStatus']=='p1'){
								$sql = "select * from wm_event_sms";
								$smsRow = $db->fetch($sql);
								
								$str_len = strlen($smsRow['smsTemplate']);
								
								$sendFl="sms";
								if($str_len>90)
									$sendFl="lms";
								
								$this->wmSms($memRow[0]['cellPhone'],$smsRow['smsTemplate'],$sendFl);
								
								$log = "insert into wm_event_send_log set memNo='$memNo',cellPhone='{$memRow[0]['cellPhone']}',regDt='$nowDate'";
							
								$db->query($log);
							}
						}	
						
						//이벤트 스타트 프로그램종료
					}
					
				/*	
				}else{
					$sql = "select b.* from ".DB_ORDER_GOODS." og INNER JOIN  wm_start_goods b ON b.goodsNo=og.goodsNo where og.orderNo=?";
					$row = $db->query_fetch($sql,['s',$orderNo]);
					
					
					$nowDate = date("Y-m-d");
					if(!empty($row[0]['idx'])){
						$this->setData("start_goods",1);
						
						$orderInfoSQL ="select * from ".DB_ORDER_INFO." where orderNo=?";
						$orderInfoROW = $db->query_fetch($orderInfoSQL,['s',$orderNo],false);
						
						$cellPhone = str_replace("-",'',$orderInfoROW['orderCellPhone']);
						$email = $orderInfoROW['orderEmail'];
						$memNm = $orderInfoROW['orderName'];

						$type = 'sha512';
						$encryptKey = "Vo4YzlGm12";
						$data = $encryptKey.$cellPhone;
						$encCellPhone = hash($type, $data);
			
						$this->setData("name",$memNm);
						$this->setData("hp",$cellPhone);
						$this->setData("hpCipher",$encCellPhone);
						$this->setData("email",$email);

						
						$memSQL = "select * from ".DB_MEMBER." where memNo=?";
						$memRow = $db->query_fetch($memSQL,['i',$memNo]);
						
						$logSQL="select count(idx) as cnt from wm_start_send_log where memNo=? and cellPhone=? and regDt=?";
						$logRow = $db->query_fetch($logSQL,['iss',$memNo,$memRow[0]['cellPhone'],$nowDate]);
						

						if($logRow[0]['cnt']<=0){
							$sql = "select * from wm_start_sms";
							$smsRow = $db->fetch($sql);
							
							$str_len = strlen($smsRow['smsTemplate']);
							
							$sendFl="sms";
							if($str_len>90)
								$sendFl="lms";
							
							$this->wmSms($memRow[0]['cellPhone'],$smsRow['smsTemplate'],$sendFl);
							
							$log = "insert into wm_start_send_log set memNo='$memNo',cellPhone='{$memRow[0]['cellPhone']}',regDt='$nowDate'";
						
							$db->query($log);
						}
					}
				
				}
				*/
				
			}
			

			//2024.02.01 웹앤모바일 스타트 상품 구매시 링크 발송 종료
		//}		
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			//2024.06.24 웹앤모바일 어도비 전송데이터 생성시작
			$orderInfo = $this->getData("orderInfo");
			
			$goods = new \Component\Goods\Goods();
			
			$goodsInfoArr=[];
			
			foreach($orderInfo['goods'] as $key => $t){
				
				$goodsIn = $goods->getGoodsView($t['goodsNo']);
				
				if(empty($goodsIn['brandNm']))
					$goodsIn['brandNm']="아델라";

				$goodsInfoArr[$t['goodsNo']]['brandNm']=$goodsIn['brandNm'];
				$goodsInfoArr[$t['goodsNo']]['cateNm']=$goodsIn['cateNm'];
				
				
				if(empty($t['optionNm']))
					$variant=$t['goodsNm'];
				else
					$variant = $t['optionNm'];
				
				$goodsInfoArr[$t['goodsNo']]['variant']=strip_tags($variant);						

				
			}
			

			$this->setData("goodsInfoArr",$goodsInfoArr);
			
			if($orderInfo['orderStatus']=='o' || $orderInfo['orderStatus']=='p'){
				
				$sql="select * from ".DB_ORDER_COUPON." where orderNo=?";
				$rows =$db->query_fetch($sql,['s',$orderNo]);

				$couponData=[];
				foreach($rows as $key =>$t){

					$couponData[$key]['couponNo']=$t['memberCouponNo'];
					$couponData[$key]['couponPrice']=$t['couponPrice'];
				}
				
				$this->setData("couponData",$couponData);
				
			}
			$transactionId=\Session::get("transactionId");
			$this->setData("transactionId",$transactionId);
			
			//2024.06.24 웹앤모바일 어도비 전송데이터 생성종료
		//}		
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