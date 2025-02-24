<?php

namespace Controller\Front\Subscription;

use Exception;

class AutoPayController extends \Controller\Front\Controller
{

	public function index()
	{

		if(\Request::getRemoteAddress()!="175.125.92.173"){

			exit;
		}
		set_time_limit(0); 

		$db = \App::load(\DB::class);

		$stamp = strtotime(date("Y-m-d"));

		$sql="select * from wm_subscription_schedule_list where schedule_stamp='$stamp' and isPayed='0' and isStop='0'";
		$rows = $db->query_fetch($sql);
	
		foreach($rows as $key =>$val){

			$inipay = new \Component\Subscription\Inicis\INIpay41Lib();
			$order =new \Component\Order\OrderAdmin();
			$cart = new \Component\Subscription\Cart();
			$iniObj = new \Component\Subscription\SubscriptionPgInicis();
			$obj = new \Component\Subscription\Subscription();


			$cfg = $iniObj->getPgCfg();

			$order->orderNo="";
			$info='';
			$orderNo="";

			try{
				

				if ($tmp = $db->fetch("SELECT * FROM wm_subscription_apply WHERE uid='".$val['uid']."' and idx_card<>''")) {

					$card_sql="select * from wm_subscription_cards where idx=?";
					$cardInfo = $db->query_fetch($card_sql,['i',$tmp['idx_card']],false);

					$info = $tmp;
					$items = [];
					$cartSno=[];

					$sql = "SELECT * FROM wm_subscription_apply_items WHERE uid='".$val['uid']."'";
				
					if ($tmps = $db->query_fetch($sql))
						$items = $tmps;

					$info['items'] = $items;

					if(count($info['items']) > 0 && !empty($cardInfo['payKey'])){
					
						/* 주문서 생성 */
						/* 임시장바구니에 데이터 넣기 */
						//$items = $info['items'];
						$sql = "DELETE FROM wm_subscription_cart WHERE memNo='{$info['memNo']}' AND isTemp='1'";
						$db->query($sql);

						foreach ($info['items'] as $it) {
							$sql = "INSERT INTO wm_subscription_cart 
									   SET 
										   memNo='{$info['memNo']}', 
										   goodsNo='{$it['goodsNo']}',
										   optionSno='{$it['optionSno']}',
										   goodsCnt='{$it['goodsCnt']}',
										   addGoodsNo='{$it['addGoodsNo']}',
										   addGoodsCnt='{$it['addGoodsCnt']}',
										   optionText='{$it['optionText']}',
										   deliveryCollectFl='{$it['deliveryCollectFl']}',
										   deliveryMethodFl='{$it['deliveryMethodFl']}',
										   regStamp='".time()."', 
										   isTemp='1'";

							if ($db->query($sql)) {
								$cartSno[] = $db->insert_id();
							}
						}

						
						/* 현재 회차 */
						try{
							$postValue = $order->setOrderDataValidation($info, true);
						}catch(Exception $e){
							continue;

						}

						$postValue['settleKind'] = 'pc';
						unset($postValue['items']);
						$address = str_replace(' ', '', $postValue['receiverAddress'] . $postValue['receiverAddressSub']);

						$cart->deliveryFree = $postValue['deliveryFree'];

						try {
							$db->begin_tran();
							$cnt = $obj->getSubscriptionCnt($postValue['uid']);

							$discount = $cfg['discount'][$cnt];
							if (empty($discount) && $cnt > 0) 
							$discount = $cfg['discount'][count($cfg['discount']) - 1];

							$cartInfo = $cart->getCartList($cartSno, $address, $postValue, true, $postValue['memNo'], $discount);
							$orderPrice = $cart->setOrderSettlePayCalculation($postValue);
							
							if ($orderPrice['settlePrice'] < 0)
								continue;

							$db->commit();
						} catch (Exception $e) {
							$db->rollback();   
							continue;
						}

						// 결제금액이 0원인 경우 전액할인 수단으로 강제 변경 및 주문 채널을 shop 으로 고정
						if ($orderPrice['settlePrice'] == 0) { 
							$postValue['settleKind'] = Order::SETTLE_KIND_ZERO;
							$postValue['orderChannelFl'] = 'shop';
						}

						//멤버 그룹번호
						$groupSql = "select groupSno from ".DB_MEMBER." where memNo=?";
						$groupRow = $db->query_fetch($groupSql,['i',$postValue['memNo']],false);

						// 사은품 증정 정책
						$giftConf = gd_policy('goods.gift');

						if (gd_is_plus_shop(PLUSSHOP_CODE_GIFT) === true && $giftConf['giftFl'] == 'y') {
							// 사은품리스트
							$gift = new \Component\Gift\Gift();
							$giftInfo = $gift->getGiftPresentOrder($cart->giftForData,$groupRow['groupSno'],true);
							$postValue['giftForData'] = $cart->giftForData;
						}

						foreach($giftInfo as $giftinfo_key =>$giftinfo_val){

							if(is_array($giftinfo_val['goodsNo'])){
								$postValue['gift'][$giftinfo_key]['0']['goodsNo']=$giftinfo_val[0]['goodsNo'];

								$postValue['gift'][$giftinfo_key]['0']['scmNo']=$giftinfo_val[0]['scmNo'];
								$postValue['gift'][$giftinfo_key]['0']['selectCnt']=$giftinfo_val[0]['gift'][0]['selectCnt'];
								$postValue['gift'][$giftinfo_key]['0']['giveCnt']=$giftinfo_val[0]['gift'][0]['giveCnt'];
								$postValue['gift'][$giftinfo_key]['0']['minusStockFl']=$giftinfo_val[0]['gift'][0]['multiGiftNo'][0]['stockFl'];
								$postValue['gift'][$giftinfo_key]['0']['giftNo']=$giftinfo_val[0]['gift'][0]['multiGiftNo'][0]['giftNo'];

							}else{
								$postValue['gift'][$giftinfo_key]['0']['goodsNo']=$giftinfo_val['goodsNo'];
								$postValue['gift'][$giftinfo_key]['0']['scmNo']=$giftinfo_val['scmNo'];
								$postValue['gift'][$giftinfo_key]['0']['selectCnt']=$giftinfo_val['gift'][0]['selectCnt'];
								$postValue['gift'][$giftinfo_key]['0']['giveCnt']=$giftinfo_val['gift'][0]['giveCnt'];
								$postValue['gift'][$giftinfo_key]['0']['minusStockFl']=$giftinfo_val['gift'][0]['multiGiftNo'][0]['stockFl'];
								$postValue['gift'][$giftinfo_key]['0']['giftNo']=$giftinfo_val['gift'][0]['multiGiftNo'][0]['giftNo'];
							}

						}		   
						/*
						* 주문정보 발송 시점을 트랜잭션 종료 후 진행하기 위한 로직 추가
						*/


						// 주문 저장하기 (트랜젝션)
						$postValue['schedule_stamp']=$stamp;
						
						$result = \DB::transaction(function () use ($order, $cartInfo, $postValue, $orderPrice, $cart) {
							$postValue['isSubscription'] = 1;			
							return $order->saveOrderInfo($cartInfo, $postValue, $orderPrice);
						});
						

						if ($result==1) {

							$order->updateCartWithOrderNo($cart->cartSno, $order->orderNo);
							$orderNo = $order->orderNo;

							if ($idx = $obj->updateScheduleOrder($postValue['uid'], $postValue['schedule_stamp'], $orderNo)) {
								
								$orderData = $order->getOrderView($orderNo);

							}else{
								continue;
							}
						}else{
							continue;
						}
						
					
						if(empty($orderNo) || empty($orderData))
							continue;
						
						$isPayed = 0;
						

						if (substr($orderData['orderStatus'], 0, 1)  == 'f' && $orderData['settlePrice'] > 0) { 
							// 결제 시도 상태이고  결제금액이 0이상인 경우 PG 결제 처리 
							if (!$orderData['goods'])
								continue;
							
													   
							$settleprice = (Integer)$orderData['settlePrice'];

							$inipay->m_inipayHome = dirname(__FILE__) . "/../../../../subscription_module/inicis";   // INIpay Home (절대경로로 적절히 수정)
							$inipay->m_keyPw = "1111";        // 키패스워드(상점아이디에 따라 변경)
							$inipay->m_type = "reqrealbill";       // 고정 (절대 수정금지)
							$inipay->m_pgId = "INIpayBill";       // 고정 (절대 수정금지)
							$inipay->m_payMethod = "Card";           // 고정 (절대 수정금지)
							$inipay->m_billtype = "Card";              // 고정 (절대 수정금지)
							$inipay->m_subPgIp = "203.238.3.10";       // 고정 (절대 수정금지)
							$inipay->m_debug = "true";        // 로그모드("true"로 설정하면 상세한 로그가 생성됨)
							$inipay->m_mid = $cfg['mid'];         // 상점아이디
							$inipay->m_billKey = $cardInfo['payKey'];        // billkey 입력
							$inipay->m_goodName = iconv("UTF-8", "EUC-KR",$orderData['orderGoodsNm']);       // 상품명 (최대 40자)
							$inipay->m_currency = "WON";       // 화폐단위
							$inipay->m_price = $settleprice;        // 가격
							$inipay->m_buyerName = iconv("UTF-8", "EUC-KR",$orderData['orderName']);       // 구매자 (최대 15자)
							$inipay->m_buyerTel = str_replace("-", "", $orderData['orderCellPhone']);       // 구매자이동전화
							$inipay->m_buyerEmail = $orderData['orderEmail'];       // 구매자이메일
							$inipay->m_cardQuota = "00";       // 할부기간
							$inipay->m_quotaInterest = "0";      // 무이자 할부 여부 (1:YES, 0:NO)
							$inipay->m_url = $cfg['domain'];    // 상점 인터넷 주소
							$inipay->m_cardPass = "";       // 키드 비번(앞 2자리)
							$inipay->m_regNumber = "";       // 주민 번호 및 사업자 번호 입력
							$inipay->m_authentification = "01"; //( 신용카드 빌링 관련 공인 인증서로 인증을 받은 경우 고정값 "01"로 세팅)
							$inipay->m_oid = $orderNo;        //주문번호
							$inipay->m_merchantreserved1 = $MerchantReserved1;  // Tax : 부가세 , TaxFree : 면세 (예 : Tax=10&TaxFree=10)
							$inipay->m_merchantreserved2 = $MerchantReserved2;  // 예비2
							$inipay->m_merchantreserved3 = $MerchantReserved3;  // 예비3
							$inipay->startAction();
							$tid = $inipay->m_tid;
						  
							$settlelog  = '';
							$settlelog  .= '===================================================='.PHP_EOL;
							$settlelog  .= 'PG명 : 이니시스 정기결제'.PHP_EOL;
							$settlelog .= "거래번호 : ". $tid . chr(10);
							$settlelog .= "결과코드 : ". $inipay->m_resultCode . PHP_EOL;
							
							//gd_debug(iconv("euc-kr","utf-8",$inipay->m_resultMsg));
							//$inicis_result_msg=iconv("euc-kr","utf-8",$inipay->m_resultMsg);
							//gd_debug($inicis_result_msg);
							//gd_debug($inipay->m_resultCode);
							
							
							
							if ($inipay->m_resultCode == "00") {
								$settlelog .= "처리결과 : 성공".PHP_EOL;
								$bool = true;

								$sql = "UPDATE " . DB_ORDER . " SET orderStatus='p1',paymentDt=sysdate() WHERE orderNo='{$orderNo}'";
								$db->query($sql);

								$sql = "UPDATE " . DB_ORDER_GOODS . " SET orderStatus='p1',paymentDt=sysdate() WHERE orderNo='{$orderNo}'";;
								$db->query($sql);

								$sql = "UPDATE " . DB_ORDER_INFO . " SET isSubscription='1' WHERE orderNo='{$orderNo}'";;
								$db->query($sql);

								$order->sendOrderInfo("INCASH","all",$orderNo);
									
								$isPayed = 1;
							} else {
								$settlelog .= "처리결과 : 실패".chr(10);
								$sql = "UPDATE " . DB_ORDER . " SET orderStatus='f3' WHERE orderNo='{$orderNo}'";
								$db->query($sql);
								$sql = "UPDATE " . DB_ORDER_GOODS . " SET orderStatus='f3' WHERE orderNo='{$orderNo}'";;
								$db->query($sql);
								
								/* 처리 실패했을경우 실 결제가 되는 경우도 있는데 이런 경우는 결제 취소 처리 */
								if ($tid) {
									$inipay->m_inipayHome = dirname(__FILE__) . "/../../../../subscription_module/inicis";   // INIpay Home (절대경로로 적절히 수정)
									$inipay->m_keyPw = "1111";        // 키패스워드(상점아이디에 따라 변경)
									$inipay->m_type = "cancel";       // 고정 (절대 수정금지)
									$inipay->m_pgId = "INIpayBill";       // 고정 (절대 수정금지)
									$inipay->m_subPgIp = "203.238.3.10";       // 고정 (절대 수정금지)
									$inipay->m_debug = "true";        // 로그모드("true"로 설정하면 상세한 로그가 생성됨)
									$inipay->m_mid = $cfg['mid'];         // 상점아이디
									$inipay->m_tid = $tid;
									$inipay->m_merchantreserved = $MerchantReserved1;  // Tax : 부가세 , TaxFree : 면세 (예 :

									$inipay->startAction();
									$settlelog = "";
									$settlelog .= '====================================================' . PHP_EOL;
									$settlelog .= 'PG명 : 이니시스 정기결제 취소' . PHP_EOL;

									$settlelog .= "결과코드 : " . $inipay->m_resultCode . PHP_EOL;
									$settlelog .= "결과내용 : " . iconv("EUC-KR", "UTF-8", $inipay->m_resultMsg) . PHP_EOL;
									$settlelog .= "취소날짜 : " . $inipay->m_pgCancelDate . PHP_EOL;
									$settlelog .= "취소시각 : " . $inipay->m_pgCancelTime . PHP_EOL;
									$settlelog	.= '===================================================='.PHP_EOL;
								}
								
								$isPayed = 0;
							}
							
							$settlelog .= "결과내용 : " . iconv("EUC-KR", "UTF-8",strip_tags($inipay->m_resultMsg)) . PHP_EOL;
							$settlelog .= "승인번호 : " . $inipay->m_authCode . PHP_EOL;
							$settlelog .= "승인날짜 : " . $inipay->m_pgAuthDate . PHP_EOL;
							$settlelog .= "승인시각 : " . $inipay->m_pgAuthTime . PHP_EOL;
							$settlelog .= "부분취소가능여부 : " . $inipay->m_prtcCode . PHP_EOL;
							$settlelog	.= '===================================================='.PHP_EOL;
							$db->query("UPDATE wm_subscription_schedule_list SET orderNo='{$orderNo}', isPayed='{$isPayed}', tid='{$tid}' WHERE idx='{$idx}'");
							
							$sql = "UPDATE " . DB_ORDER . "
											SET
												pgName='inicis',
												pgResultCode='".$inipay->m_resultCode."',
												pgTid='".$tid."',
												pgAppNo='".$inipay->m_authCode."',
												pgAppDt='".$inipay->m_pgAuthDate."',
												orderPGLog='".$settlelog."'
									 WHERE orderNo='{$orderNo}'";
						   $db->query($sql);

						} else {
							if (empty($orderPrice['settlePrice']) && $orderData['settleKind'] == Order::SETTLE_KIND_ZERO) { // 전액 결제인 경우 처리 
								$isPayed = 1;
								$db->query("UPDATE wm_subscription_schedule_list SET orderNo='{$orderNo}', isPayed='{$isPayed}' WHERE idx='{$idx}'");
							}
						}


						$log_sql="insert into wm_subscription_pay_log set uid=?,schedule_idx=?,orderNo=?,inicis_result_code=?,card_idx=?,stamp=?,regDt=sysdate(),inicis_result_msg=?";
						$db->bind_query($log_sql,['sissiis',$postValue['uid'],$val['idx'],$orderNo,$inipay->m_resultCode,$cardInfo['idx'],$stamp,$inicis_result_msg]);
						
						if ($isPayed) {
						   /* 정기결제 장바구니 상품 삭제 */
						   if ($cartSno)
							  $cart->cartDelete($cartSno);
						   
						}

					}//end count(items);

				}
			
			}catch(Exception $e){
			
				gd_debug($e->getMessage());
			}
			
		}
		exit();
	}


}