<?php
namespace Component\Subscription;

use App;
use Request;
use Component\Member\Member;
use Component\Order\Order;
use Exception;

class SubscriptionPgInicis extends \Component\Subscription\SubscriptionPg
{
    /* PG 설정 추출 */
    public function getPgCfg()
    {
        $cfg = $this->getCfg();

		if(\Request::getRemoteAddress()=="112.145.36.156"){
			//$cfg['useMode']="test";
		}
        if ($cfg['useMode'] != 'real') {
            $cfg['mid'] = 'INIBillTst';
            $cfg['signKey'] = "SU5JTElURV9UUklQTEVERVNfS0VZU1RS";
            $cfg['lightKey'] = "b09LVzhuTGZVaEY1WmJoQnZzdXpRdz09";
        }
        
        $SignatureUtil = App::load("\Component\Subscription\Inicis\INIStdPayUtil");
        $cfg['timestamp'] = $SignatureUtil->getTimestamp();
        $cfg['mKey'] = hash('sha256', $cfg['signKey']);
        $cfg['modulePath'] = dirname(__FILE__) . "/../../../subscription_module/inicis";
        $cfg['pg_gate'] = "subscription/inicis/pg_gate.html";
        return $cfg;
    }
    
    public function getPgSign($uid, $price = 0, $timestamp = 0)
    {
        $data = [];
        $cfg = $this->getPgCfg();
        $timestamp = $timestamp?$timestamp:$cfg['timestamp'];
        if ($this->isMobile) 
            $params = $cfg['mid'] . $uid . $timestamp . $cfg['lightKey'];
        else 
            $params = "oid=" . $uid . "&price=" . $price . "&timestamp=" . $timestamp;
        
        return hash("sha256", $params);
    }
    
    //--- 카드사 코드
    public function getPgCards()
    {
        $pgCards	= array(
                 '01'	=> '하나(외환)카드',
                 '03'	=> '롯데카드',
                 '04'	=> '현대카드',
                 '06'	=> '국민카드',
                 '11'	=> 'BC카드',
                 '12'	=> '삼성카드',
                 '13'	=> '(구)LG카드',
                 '14'	=> '신한카드',
                 '15'	=> '한미카드',
                 '16'	=> 'NH카드',
                 '17'	=> '하나SK카드',
                 '21'	=> '해외비자카드',
                 '22'	=> '해외마스터카드',
                 '23'	=> '해외JCB카드',
                 '24'	=> '해외아멕스카드',
                 '25'	=> '해외다이너스카드',
                 '98'	=> '페이코(포인트 100% 사용)',
            );

            return $pgCards;
         }

         //--- 은행 코드
         public function getPgBanks()
         {
            $pgBanks	= array(
                 '02'	=> '한국산업은행',
                 '03'	=> '기업은행',
                 '04'	=> '국민은행',
                 '05'	=> '하나은행(구외환)',
                 '07'	=> '수협중앙회',
                 '11'	=> '농협중앙회',
                 '12'	=> '단위농협',
                 '16'	=> '축협중앙회',
                 '20'	=> '우리은행',
                 '21'	=> '신한은행',
                 '23'	=> '제일은행',
                 '25'	=> '하나은행',
                 '26'	=> '신한은행',
                 '27'	=> '한국씨티은행',
                 '31'	=> '대구은행',
                 '32'	=> '부산은행',
                 '34'	=> '광주은행',
                 '35'	=> '제주은행',
                 '37'	=> '전북은행',
                 '38'	=> '강원은행',
                 '39'	=> '경남은행',
                 '41'	=> '비씨카드',
                 '53'	=> '씨티은행',
                 '54'	=> '홍콩상하이은행',
                 '71'	=> '우체국',
                 '81'	=> '하나은행',
                 '83'	=> '평화은행',
                 '87'	=> '신세계',
                 '88'	=> '신한은행',
                 '98'	=> '페이코(포인트 100% 사용)',
          );

          return $pgBanks;
     }
     
     public function pay($idx = null, $chkStamp = true, $isManual = false)
     {
         if (!$idx)
             return false;

         $obj = new \Component\Subscription\Subscription();

         if (!$info = $obj->getSubscription($idx))
             return false;

        if (!$isManual && ($info['isStop'] || !$info['items'] || !$info['idx_card'] || $info['isPayed']))
            return false;

        $card = $obj->setCard($info['idx_card'])->get();
        if (!$card['payKey'])
            return false;
        
        /* 결제일 유효성 검사 */
        if ($chkStamp) {
            $stamp = strtotime(date("Ymd"));
            if ($stamp != $info['schedule_stamp'])
                return false;
        }

        $server = Request::server()->toArray();
		$memNo = \Session::get("member.memNo");
		if (gd_is_login() && $memNo == $info['memNo']) {
			$order =new \Component\Order\Order();
		} else {
			$order =new \Component\Order\OrderAdmin();
		}

		$cart = new \Component\Subscription\Cart();

        $cfg = $this->getPgCfg();
     
        $orderData = $cartSno = [];
        if ($orderNo = $info['orderNo']) {
            $row = $this->db->fetch("SELECT COUNT(*) as cnt FROM " . DB_ORDER . " WHERE orderNo='{$orderNo}'");
            if ($row['cnt'] > 0) {
                $orderData = $order->getOrderView($info['orderNo']);
                $status = substr($orderData['orderStatus'], 0, 1);
                if ($status != 'f')
                    unset($orderData);
             }
        }
        
        if (empty($orderData)) {
            /* 주문서 생성 */
            /* 임시장바구니에 데이터 넣기 */
            $items = $info['items'];
            $sql = "DELETE FROM wm_subscription_cart WHERE memNo='{$info['memNo']}' AND isTemp='1'";
            $this->db->query($sql);
            
            foreach ($items as $it) {
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
               if ($this->db->query($sql)) {
                   $cartSno[] = $this->db->insert_id();
               }
           }
           
			if(empty($info['receiverPhone']))
				$info['receiverPhone']=$info['receiverCellPhone'];

			if(empty($info['receiverCellPhone'])){
				$info['receiverCellPhone']="010-000-0000";

			}

			if(empty($info['receiverAddressSub']))
				$info['receiverAddressSub']="공백";

           /* 현재 회차 */
           try{
			    $postValue = $order->setOrderDataValidation($info, true);
		   }catch(Exception $e){
				return false;
		   }

           $postValue['settleKind'] = 'pc';
           unset($postValue['items']);
           $address = str_replace(' ', '', $postValue['receiverAddress'] . $postValue['receiverAddressSub']);
           
           $cart->deliveryFree = $postValue['deliveryFree'];
           try {
                $this->db->begin_tran();
                $cnt = $obj->getSubscriptionCnt($postValue['uid']);
                $discount = $cfg['discount'][$cnt];
                if (empty($discount) && $cnt > 0) 
                    $discount = $cfg['discount'][count($cfg['discount']) - 1];
                
                $cartInfo = $cart->getCartList($cartSno, $address, $postValue, true, $postValue['memNo'], $discount);
                $orderPrice = $cart->setOrderSettlePayCalculation($postValue);
                if ($orderPrice['settlePrice'] < 0)
                    return false;
                
                $this->db->commit();
           } catch (Exception $e) {
               $this->db->rollback();           
           }
           
           // 결제금액이 0원인 경우 전액할인 수단으로 강제 변경 및 주문 채널을 shop 으로 고정
           if ($orderPrice['settlePrice'] == 0) { 
                $postValue['settleKind'] = Order::SETTLE_KIND_ZERO;
                $postValue['orderChannelFl'] = 'shop';
           }
			
			//멤버 그룹번호
			$groupSql = "select groupSno from ".DB_MEMBER." where memNo=?";
			$groupRow = $this->db->query_fetch($groupSql,['i',$postValue['memNo']],false);


			
				//$scheduleRow = $this->db->fetch("select count(idx) as cnt from wm_subscription_schedule_list where uid='{$info['uid']}' and isPayed='1'");
				//$schedule_count = $scheduleRow['cnt']+1;
				//$bool = $this->presentGoods($info['deliveryEa'],$schedule_count);

			// 사은품 증정 정책
			$giftConf = gd_policy('goods.gift');

				
				/*if($bool==true){
					$giftConf['giftFl']='y';

					foreach($cartInfo as $cartKey1 =>$cartVal1){
					
						foreach($cartVal1 as $cartKey2 =>$cartVal2){
							
							foreach($cartVal2 as $cartKey3 =>$cartVal3){
							
								$goodsNo=$cartVal3['goodsNo'];
								$giftForData[$goodsNo]['scmNo'] = $cartVal3['scmNo'];
								$giftForData[$goodsNo]['cateCd'] = $cartVal3['cateCd'];
								$giftForData[$goodsNo]['brandCd'] = $cartVal3['brandCd'];
								$giftForData[$goodsNo]['price'] = gd_isset($giftForData[$goodsNo]['price'], 0) + $cartVal3['price']['goodsPriceSubtotal'];
								$giftForData[$goodsNo]['cnt'] = gd_isset($giftForData[$goodsNo]['cnt'], 0) + $cartVal3['goodsCnt'];
								$addGoodsCnt = gd_isset($giftForData[$goodsNo]['addGoodsCnt'], 0);
								if(empty($dataVal['addGoods']) == false) {
									foreach ($dataVal['addGoods'] as $addGoodsVal) {
										$addGoodsCnt += $addGoodsVal['addGoodsCnt'];
									}
								}
								$giftForData[$goodsNo]['addGoodsCnt'] = $addGoodsCnt;
							}
						}
					}
				}


				if (gd_is_plus_shop(PLUSSHOP_CODE_GIFT) === true && $giftConf['giftFl'] == 'y') {
					// 사은품리스트

					$gift = new \Component\Gift\Gift();
					$giftInfo = $gift->getGiftPresentOrder($giftForData,$groupRow['groupSno'],true);
					$postValue['giftForData'] = $giftForData;
				}
				*/


			/*
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
			*/
           /*
            * 주문정보 발송 시점을 트랜잭션 종료 후 진행하기 위한 로직 추가
            */

            // 주문 저장하기 (트랜젝션)
            $result = \DB::transaction(function () use ($order, $cartInfo, $postValue, $orderPrice, $cart) {
				$postValue['isSubscription'] = 1;
                return $order->saveOrderInfo($cartInfo, $postValue, $orderPrice);
            });
            
            
            if ($result) {
                $orderNo = $order->orderNo;
				
                if ($idx = $obj->updateScheduleOrder($postValue['uid'], $postValue['schedule_stamp'], $orderNo)) {
					if (gd_is_login() && $memNo == $info['memNo']) {
						$orderData = $order->getOrderView2($orderNo);
					} else {
						$orderData = $order->getOrderView($orderNo);
					}
                }
            }  
        }
        
        $isPayed = 0;
        if (substr($orderData['orderStatus'], 0, 1)  == 'f' && $orderData['settlePrice'] > 0) { 
            // 결제 시도 상태이고  결제금액이 0이상인 경우 PG 결제 처리 
            if (!$orderData['goods'])
                return false;
            
            $arrOrderGoodsSno = [];
            foreach ($orderData['goods'] as $li) {
                $arrOrderGoodsSno['sno'][] = $li['sno'];
            }
            
            $inipay = App::load(\Component\Subscription\Inicis\INIpay41Lib::class);
           
            $settleprice = (Integer)$orderData['settlePrice'];
            //$settleprice = 1000;
            $inipay->m_inipayHome = dirname(__FILE__) . "/../../../subscription_module/inicis";   // INIpay Home (절대경로로 적절히 수정)
            $inipay->m_keyPw = "1111";        // 키패스워드(상점아이디에 따라 변경)
            $inipay->m_type = "reqrealbill";       // 고정 (절대 수정금지)
            $inipay->m_pgId = "INIpayBill";       // 고정 (절대 수정금지)
            $inipay->m_payMethod = "Card";           // 고정 (절대 수정금지)
            $inipay->m_billtype = "Card";              // 고정 (절대 수정금지)
            $inipay->m_subPgIp = "203.238.3.10";       // 고정 (절대 수정금지)
            $inipay->m_debug = "true";        // 로그모드("true"로 설정하면 상세한 로그가 생성됨)
            $inipay->m_mid = $cfg['mid'];         // 상점아이디
            $inipay->m_billKey = $card['payKey'];        // billkey 입력
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
            $inipay->m_oid = $oid;        //주문번호
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
            
            if ($inipay->m_resultCode == "00") {
                $settlelog .= "처리결과 : 성공".PHP_EOL;
                $bool = true;
                
                $arrOrderGoodsSno['changeStatus'] = "p1";
               
				if (gd_is_login() && $memNo == $info['memNo']) {
					$order->statusChangeCodeP($orderNo, $arrOrderGoodsSno); // 입금확인으로 변경 

				}else{
					$sql = "UPDATE " . DB_ORDER . " SET orderStatus='p1',paymentDt=sysdate() WHERE orderNo='{$orderNo}'";
					$this->db->query($sql);

					$sql = "UPDATE " . DB_ORDER_GOODS . " SET orderStatus='p1',paymentDt=sysdate() WHERE orderNo='{$orderNo}'";;
					$this->db->query($sql);

					$sql = "UPDATE " . DB_ORDER_INFO . " SET isSubscription='1' WHERE orderNo='{$orderNo}'";;
					$this->db->query($sql);

					$order->sendOrderInfo("INCASH","all",$orderNo);
					
				}
                $isPayed = 1;
            } else {
                $settlelog .= "처리결과 : 실패".chr(10);
                $sql = "UPDATE " . DB_ORDER . " SET orderStatus='f3' WHERE orderNo='{$orderNo}'";
                $this->db->query($sql);
                $sql = "UPDATE " . DB_ORDER_GOODS . " SET orderStatus='f3' WHERE orderNo='{$orderNo}'";;
                $this->db->query($sql);
                
                /* 처리 실패했을경우 실 결제가 되는 경우도 있는데 이런 경우는 결제 취소 처리 */
                if ($tid) {
                    $inipay->m_inipayHome = dirname(__FILE__) . "/../../../subscription_module/inicis";   // INIpay Home (절대경로로 적절히 수정)
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
            $this->db->query("UPDATE wm_subscription_schedule_list SET orderNo='{$orderNo}', isPayed='{$isPayed}', tid='{$tid}' WHERE idx='{$idx}'");
            
            $sql = "UPDATE " . DB_ORDER . "
                            SET
                                pgName='inicis',
                                pgResultCode='".$inipay->m_resultCode."',
                                pgTid='".$tid."',
                                pgAppNo='".$inipay->m_authCode."',
                                pgAppDt='".$inipay->m_pgAuthDate."',
                                orderPGLog='".$settlelog."'
                     WHERE orderNo='{$orderNo}'";
           $this->db->query($sql);
        } else {
            if (empty($orderPrice['settlePrice']) && $orderData['settleKind'] == Order::SETTLE_KIND_ZERO) { // 전액 결제인 경우 처리 
                $isPayed = 1;
                $this->db->query("UPDATE wm_subscription_schedule_list SET orderNo='{$orderNo}', isPayed='{$isPayed}' WHERE idx='{$idx}'");
            }
        }
        
        if ($isPayed) {
           /* 정기결제 장바구니 상품 삭제 */
           if ($cartSno)
              $cart->cartDelete($cartSno);
           
           return true;
        }
     }
     
    public function cancel($orderNo = null, $isApplyRefund = true, $msg = null)
    {
        if (!$orderNo)
            return false;
        
        if (!$msg)
            $msg = "관리자 취소";
        
        $order = App::load(\Component\Order\Order::class);
        $cfg = $this->getCfg();
        
        $inipay = App::load(\Component\Subscription\Inicis\INIpay41Lib::class);
        $orderReorderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
        $od = [];
        if ($tmp  = $this->db->fetch("SELECT * FROM " . DB_ORDER . " WHERE orderNo='{$orderNo}'"))
            $od = $tmp;
        
        if (!$od)
            return false;
        
        $tid = $od['pgTid'];
        
        $inipay->m_inipayHome = dirname(__FILE__) . "/../../../subscription_module/inicis";   // INIpay Home (절대경로로 적절히 수정)
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
        if ($inipay->m_resultCode == "00") {
            $bool = true;
            
            if ($isApplyRefund) {
                if ($list = $this->db->query_fetch("SELECT sno, orderStatus FROM " . DB_ORDER_GOODS . " WHERE orderNo='{$orderNo}'")) {
                  $arrData = ['mode' => 'refund', 'orderNo'=> $orderNo, "orderChannelFl" => "shop"];
                  foreach ($list as $li) {
                       $arrData['refund']['statusCheck'][$li['sno']] = $li['sno'];
                       $arrData['refund']['statusMode'][$li['sno']] = $li['orderStatus'];
                       $arrData['refund']['goodsType'][$li['sno']] = 'goods';
                  }
                  $return = \DB::transaction(function () use ($orderReorderCalculation, $arrData) {
                     return $orderReorderCalculation->setBackRefundOrderGoods($arrData, 'refund');
                  });
               } // endif
            } // endif 

			$sub_where=",orderStatus='r3'";
        }
        
        $settlelog	.= '===================================================='.PHP_EOL;
        $settlelog = $this->db->escape($settlelog);
        $sql = "UPDATE " . DB_ORDER . " SET orderPGLog=concat(ifnull(orderPGLog,''),'".$settlelog."'){$sub_where} WHERE orderNo='{$orderNo}'";
        $this->db->query($sql);
		
		if( $bool == true){
			$sql="update ".DB_ORDER_GOODS." set orderStatus='r3' where WHERE orderNo='{$orderNo}'";
			$this->db->query($sql);
		}
        return true;
    }


	//2022.05.30 민트웹 추가시작
	public function presentGoods($deliveryEa=0,$scheduleCount=0)
	{
		if($deliveryEa<=6)
			return false;

		$mod=6;
		
		$multi=$deliveryEa%2;
		$schedule=$scheduleCount%$mod;

		if($scheduleCount>1 && $schedule==1 && $multi==0)
			return true;
		else
			return false;



		/*if(empty($deliveryEa) || empty($scheduleCount))
			return false;

		$schedule=$scheduleCount;

		$oriDeliveryEa=$deliveryEa;

		$tmp=0;
		
		$remaind = $deliveryEa  %2;

		$mod=2;

		if($remaind>0)
			$mod=3;

		while(1){
		
			$remaind = $deliveryEa % $mod;
			$multi = $deliveryEa / $mod;

			if(!is_int($remaind))
				break;

			if($remaind==0 ){
				
				if($tmp==0){
					$tmp=$multi;
					$deliveryEa=$multi;
				}else if($tmp>$multi){
				
					$tmp=$multi;
					$deliveryEa=$multi;
				}else{
					break;
				}

			}else if((int)$multi<=2){
				
				if($multi>2)
					break;
				else if($multi==2)
					$tmp=$multi;

				break;
			}
		}
	
		$calcu=$mod *$tmp;

		$bool=false;

		if($calcu==$oriDeliveryEa){

			$chkRemaind = $schedule % $tmp;

			$chkCalcu=$schedule-$chkRemaind;

			if($oriDeliveryEa==$chkCalcu){

			}else{

				if($chkRemaind==1)
					$bool=true;
				
			}
		}else{

			//$chkRemaind = $schedule % $k;

			//if($chkRemaind==1)
			//	$bool=true;
			
		}

		return $bool;
		*/
	}

	//2022.05.30 민트웹 추가 종료
}