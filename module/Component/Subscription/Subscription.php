<?php
namespace Component\Subscription;

use App;
use Request;
use Session;
use Component\Subscription\SubscriptionTrait;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\LmsMessage;
use Framework\Security\Otp;
use Component\Member\Util\MemberUtil;

class Subscription
{
    private $db;
    private $obj;
    private $idxCard;
	private $delivery_date="";
    use SubscriptionTrait;
    
    public function __construct()
    {
        $this->db = App::load('DB');
        $this->setCfg();    
            
       $this->cfg['pg'] = $this->cfg['pg']?$this->cfg['pg']:"inicis";
        switch ($this->cfg['pg']) {
            case "inicis": 
                $this->obj = App::load("\Component\Subscription\SubscriptionPgInicis");
                break;
            case "kcp" : 
                $this->obj = App::load("\Component\Subscription\SubscriptionPgKcp");
                break;
        }
        
        $this->cfg = array_merge($this->cfg, $this->obj->getPgCfg());
        
        $cfg = $this->getCfg();
    }
    
   public function getPgInstance()
   {
       return $this->obj;
   }
   
   /* 비밀번호 입력 키 추출 */
   public function getShuffleChars()
   {
        $chars = range(0,9);
        shuffle($chars);
        
        return $chars;
   }
   
   /* 결제 카드 리스트 */
   public function getCards($memNo = null)
   {
       $list = [];
       $conds = "";
       if ($memNo) {
           $memNo = $this->db->escape($memNo);
           $conds = " WHERE memNo='{$memNo}'";
       }
       
       if ($tmp = $this->db->query_fetch("SELECT * FROM wm_subscription_cards{$conds} ORDER BY idx desc")) {
           foreach ($tmp as $t) {
               $row = $this->db->fetch("SELECT COUNT(a.uid) as cnt FROM wm_subscription_apply a INNER JOIN wm_subscription_schedule_list b ON a.uid=b.uid WHERE a.idx_card='{$t['idx']}' and b.isPayed='0' and b.isStop='0'");
               if ($row['cnt'] > 0)
                   $t['underUse'] = true;
               
               $list[] = $t;
           }
       }

       return $list;
   }
   
   /* 결제 카드 선택 */
   public function setCard($idx = null, $mode = "")
   {
        $this->idxCard = $this->db->escape($idx);
        if (empty($mode))
            $mode = "getCardInfo";
        
       $this->mode = $mode;
        return $this;
   }
   
   public function setIdx($idx = null)
   {
       $this->idx = $idx;
       return $this;
   }
   
   public function setBoolean($bool = false)
   {
       $this->boolean = $bool;
       return $this;
   }
   
   public function setMode($mode = null)
   {
       if ($mode)
           $this->mode = $mode;
       
       return $this;
   }
      

   public function setDeliveryDate($delivery_date)
	{
		//if(!$delivery_date)
		//	$delivery_date=date("Y-m-d");
		
		if(!empty($delivery_date))
			$this->delivery_date = $delivery_date;
		else
			$this->delivery_date ="";

		return $this;

	}
   /* 결제카드 삭제 */
   public function delCard($idx = null)
   {
       if (empty($idx))
            $idx = $this->idxCard;
       
       if (!$idx)
           return false;
       
       $res = $this->db->query("DELETE FROM wm_subscription_cards WHERE idx='{$idx}'");
       return $res;
   }
   
   /* 결제 */
   public function pay($idx = null, $chkStamp = true, $isManual = false)
   {
      return $this->obj->pay($idx, $chkStamp, $isManual);
   }
   
   /* 결제취소 */
   public function cancel($orderNo = null, $isApplyRefund = true, $msg = null)
   {
       return $this->obj->cancel($orderNo, $isApplyRefund, $msg);
   }
   
   /* 정기배송 신청 정보 */
   public function getSubscription($idx = null)
   {
       $info = [];
       if (!$idx)
          return $info;
       
       if (!$tmp = $this->db->fetch("SELECT * FROM wm_subscription_schedule_list WHERE idx='{$idx}'"))
          return $info;
      
       $info = $this->setUid($tmp['uid'])
                        ->setMode("subscriptionInfo")
                        ->get();
       
       if (!$info)
           return $info;
       
       $info = array_merge($info, $tmp);
       return $info;
   }
   
   /* 정기결제 현재까지 유효 회차 */
   public function getSubscriptionCnt($uid = null)
   {
       if (!$uid)
           return false;
       
       $sql = "SELECT COUNT(*) as cnt FROM wm_subscription_schedule_list AS a 
                        INNER JOIN " . DB_ORDER . " AS o ON a.orderNo = o.orderNo 
                   WHERE a.uid='{$uid}' AND a.orderNo <> '' AND SUBSTR(o.orderStatus, 1, 1) IN ('p', 'g', 'd', 's')";
       
       $row = $this->db->fetch($sql);
       return $row['cnt'];
   }
   
   /* 정기배송 등록 처리 START */
   public function registerSubscription($post = [])
   {

		$this->mode="getScheduleList";

       $memNo = Session::get("member.memNo");
       if ($post && $post['cartSno'] && $memNo) {
          
           foreach ($post as $k => $v) {
               if (is_array($v)) {
                   foreach ($v as $_k => $_v) {
                        $post[$k][$_k] = $this->db->escape($_v);
                   }
               } else {
                   $post[$k] = $this->db->escape($v);
               }
           }
           
           $sql = "SELECT * FROM wm_subscription_cart WHERE idx IN (".implode(",", $post['cartSno']).") ORDER BY idx ";
           if (!$items = $this->db->query_fetch($sql))
               return false;
           
           $order = App::load(\Component\Order\Order::class);
           
           $uid = $order->generateOrderNo();
           
           try { 
               $this->db->begin_tran();
               
               foreach ($items as $it) {
                   foreach ($it as $k => $v) 
                       $it[$k] = $this->db->escape($v);
                        
                   $sql = "INSERT INTO wm_subscription_apply_items  
                                 SET 
                                    uid='{$uid}',
                                    goodsNo='{$it['goodsNo']}',
                                    optionSno='{$it['optionSno']}',
                                    goodsCnt='{$it['goodsCnt']}',
                                    addGoodsNo='{$it['addGoodsNo']}',
                                    addGoodsCnt='{$it['addGoodsCnt']}',
                                    optionText='{$it['optionText']}',
                                    deliveryCollectFl='{$it['deliveryCollectFl']}',
                                    deliveryMethodFl='{$it['deliveryMethodFl']}'";
                 $this->db->query($sql);
               } // endforeach 
               
               $sql = "INSERT INTO wm_subscription_apply 
                            SET 
                                uid='{$uid}',
                                memNo='{$memNo}',
                                regStamp='".time()."',
                                deliveryEa='{$post['deliveryEa']}', 
                                period='{$post['period']}',
								autoExtend='{$post['autoExtend']}',
                                idx_card='{$post['idx_card']}',
                                orderPhone='{$post['orderPhone']}',
                                orderZonecode='{$post['orderZonecode']}',
                                orderAddress='{$post['orderAddress']}',
                                orderAddressSub='{$post['orderAddressSub']}',
                                orderName='{$post['orderName']}',
                                orderCellPhone='{$post['orderCellPhone']}',
                                orderEmail='{$post['orderEmail']}',
                                receiverName='{$post['receiverName']}',
                                receiverZonecode='{$post['receiverZonecode']}',
                                receiverAddress='{$post['receiverAddress']}',
                                receiverAddressSub='{$post['receiverAddressSub']}',
                                receiverZipcode='{$post['receiverZipcode']}',
                                receiverPhone='{$post['receiverPhone']}',
                                receiverCellPhone='{$post['receiverCellPhone']}',
                                orderMemo='{$post['orderMemo']}',
                                receiptFl='{$post['receiptFl']}',
                                receiverUseSafeNumberFl='{$post['receiverUseSafeNumberFl']}',
                                orderZipcode='{$post['orderZipcode']}',
                                totalMemberDcPrice='{$post['totalMemberDcPrice']}',
                                totalMemberOverlapDcPrice='{$post['totalMemberOverlapDcPrice']}',
                                deliveryFree='{$post['deliveryFree']}',
                                totalDeliveryFreePrice='{$post['totalDeliveryFreePrice']}',
                                totalDeliveryCharge='{$post['totalDeliveryCharge']}',
                                deliveryAreaCharge='{$post['deliveryAreaCharge']}',
								delivery_date='{$post['deliverydate']}'";
              $this->db->query($sql);
              
              /* 스케줄 리스트 */
              if ($list = $this->getScheduleList($post['deliveryEa'], 0, $post['period'],$post['deliverydate'])) {
                  foreach ($list as $li) {
                      $sql = "INSERT INTO wm_subscription_schedule_list 
                                    SET 
                                        uid='{$uid}',
                                        schedule_stamp='{$li['stamp']}'";
                       $this->db->query($sql);
                  }
                  
              }

              $this->db->commit();
              
              return $uid;
           } catch (Exception $e) {
               $this->db->rollback();
           }
       }
   }
   
   public function extendSchedule($uid = null, $deliveryEa = 0, $period = 0,$type="")
   {


        if ($uid && $deliveryEa && $period) {
			
           $sql = "SELECT * FROM wm_subscription_schedule_list WHERE uid='{$uid}' ORDER BY schedule_stamp desc";
           $row = $this->db->fetch($sql);
           $stamp = $row['schedule_stamp'];

           if ($stamp <= time()) {
               if (!$ro = $this->db->fetch("SELECT period FROM wm_subscription_apply WHERE uid='{$uid}'"))
                   return false;

                $period = explode("_", $ro['period']);
                $str = "+".$period[0]." ".$period[1];				
                $stamp = strtotime($str, $stamp);

                if ($stamp < time())
                    $stamp = strtotime(date('Ymd'));
           }

		   if($type=="user" && $stamp > time()){
				
				if(is_array($period))
					$period_=$period;
				else{
					$period_ = explode("_",$period);

					$schedule_ymd = date("Y-m-d",$stamp);
					$stamp = strtotime($schedule_ymd." +".$period_[0]." ".$period_[1]);
				}

		   }
			
		   if ($list = $this->getScheduleList($deliveryEa, $stamp, $period)) {
			   foreach ($list as $li) {
				   $sql = "INSERT INTO wm_subscription_schedule_list 
								SET 
									uid='{$uid}',
									schedule_stamp='{$li['stamp']}'";

				   $this->db->query($sql);

				   //연장로그
				   $this->db->query("insert into wm_subscription_extension_log set uid='$uid',schedule_stamp='{$li['stamp']}',regDt=sysdate()");
				   //$log_uid[]=$uid;
			   }
		   }

			$strSQL="insert into  wm_subscription_extension_num set uid='{$uid}' ,regDt=sysdate()";
			$this->db->query($strSQL);

		   		
           return true;
        }
            
   }
   
   public function updateScheduleOrder($uid = null, $stamp = 0, $orderNo = 0)
   {
        if ($uid && $stamp && $orderNo) {
            $uid = $this->db->escape($uid);
            $stamp = $this->db->escape($stamp);
            $orderNo = $this->db->escape($orderNo);
            
            $sql = "UPDATE wm_subscription_schedule_list 
                            SET 
                                orderNo='{$orderNo}'
                           WHERE uid='{$uid}' AND schedule_stamp='{$stamp}'";
                           
            if ($this->db->query($sql)) {
                $sql = "SELECT idx FROM wm_subscription_schedule_list WHERE uid='{$uid}' AND schedule_stamp='{$stamp}'";
                $row = $this->db->fetch($sql);
                return $row['idx'];
            }
        }
   }
   
   public function rollbackSubscription($uid = null) 
   {
        if ($uid) {
            try {
                
                $this->db->begin_tran();
                $sql = "DELETE FROM wm_subscription_apply WHERE uid='{$uid}'";
                $this->db->query($sql);
                
                $sql = "DELETE FROM wm_subscription_apply_items WHERE uid='{$uid}'";
                $this->db->query($sql);
                
                $sql = "DELETE FROM wm_subscription_schedule_list WHERE uid='{$uid}'";
                $this->db->query($sql);
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollback();
            }
        }
   }
   
   /* 정기배송 등록 처리 END */
   
   public function setUid($uid)
   {
       $this->uid = $uid;
       return $this;
   }
   
   public function setGoods($goodsNo = null)
   {
       $this->goodsNo = $goodsNo;
       return $this;
   }
   
   public function setCartInfo($cartInfo = []) 
   {
       $this->cartInfo = $cartInfo;
       return $this;
   }
   
   public function setScheduleEa($ea = 1)
   {
       $this->scheduleEa = $ea;
       return $this;
   }
   
   public function setPeriod($period = null)
   {
       $this->period = $period;
       return $this;
   }
   
   public function setStamp($stamp = 0)
   {
       $this->stamp = $stamp;
       return $this;
   }
   
   public function setDate($date = null)
   {
       if (empty($date))
           $date = date("Ymd");

       $stamp = strtotime($date);
       $this->stamp = $stamp;
       return $this;
   }
   
   public function setPrice($price = 0)
   {
       $this->price = $price;
       return $this;
   }
      
    public function getGoodsCfg($goodsNo = null)
    {
        $cfg = $this->getCfg();
        if (empty($goodsNo))
            $goodsNo = $this->goodsNo;
        if ($goodsNo) {
            $sql = "SELECT * FROM wm_subscription_goods_config WHERE goodsNo='{$goodsNo}'";
            if ($tmp = $this->db->fetch($sql)) {
                if ($tmp['discount'])
                    $cfg['discount'] = explode(",", $tmp['discount']);
            }
        }
        
        $discount_amount = [];
        if ($this->price) {
           if ($cfg['discount']) {
               foreach ($cfg['discount'] as $k => $v) {
                   $discount_amount[$k] = round($this->price * $v / 1000) * 10;
               }
           }
        }
        $cfg['discount_amount'] = $discount_amount;
        return $cfg;
    }
    
    public function isSubscriptionGoods($goodsNo = null)
    {
        $sql = "SELECT isSubscription FROM " . DB_GOODS . " WHERE goodsNo='{$goodsNo}'";
        $row = $this->db->fetch($sql);
       
        if ($row['isSubscription'])
            return true;
        
    }
  
    public function getScheduleListByUid($uid = null)
    {
        $list = $cartSno = [];
        if ($uid) {
            $obj = App::load(\Component\Subscription\Subscription::class);
            $order = App::load(\Component\Order\Order::class); 
            $cart = App::load(\Component\Subscription\Cart::class);
            $info = $obj->setUid($uid)
                            ->setMode("subscriptionInfo")
                            ->get();
							


			if(empty($info['receiverPhone']))
				$info['receiverPhone']=$info['receiverCellPhone'];

			if(empty($info['receiverCellPhone'])){
				$info['receiverCellPhone']="010-000-0000";

			}

			if(empty($info['receiverAddressSub']))
				$info['receiverAddressSub']="공백";


           $postValue = $order->setOrderDataValidation($info, true);
           $cart->deliveryFree = $postValue['deliveryFree'];
           $postValue['settleKind'] = 'pc';
           unset($postValue['items']);
           $address = str_replace(' ', '', $postValue['receiverAddress'] . $postValue['receiverAddressSub']);
           
            /* 임시장바구니에 데이터 넣기 */
            $items = $info['items'];
            $sql = "DELETE FROM wm_subscription_cart WHERE memNo='{$info['memNo']}' AND isTemp='2'";
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
                                   isTemp='2'";
               if ($this->db->query($sql)) {
                   $cartSno[] = $this->db->insert_id();
               }
           }
            
            $hList = $this->getHolidayList();
            $cfg = $this->getCfg();
             
            $deliveryDays = $cfg['deliveryDays']?$cfg['deliveryDays']:0;
			
			$order_time = date("H",$info['regStamp']);
			
			if(!empty($cfg['am_type']) && $order_time<$cfg['am_type']){
				$deliveryDays = $cfg['am_deliveryDays'];
			}else if(!empty($cfg['af_type']) && $order_time>=$cfg['af_type']){
				$deliveryDays = $cfg['af_deliveryDays'];
			}


            $smsDays = $cfg['smsDays']?$cfg['smsDays']:0;
            
            $sql = "SELECT * FROM wm_subscription_schedule_list WHERE uid='{$uid}' ORDER BY schedule_stamp asc";
            
            $status = $obj->getOrderStatusList();
            if ($tmp = $this->db->query_fetch($sql)) {
                $no = 0;

				
                foreach ($tmp as $n => $t) {

                    $cart->totalSettlePrice = $cart->totalGoodsPrice = $cart->totalDeliveryCharge = $cart->totalGoodsDcPrice = $cart->totalSumMemberDcPrice = $cart->totalCouponGoodsDcPrice = 0;
                    $stamp = $t['schedule_stamp'];
					
					if($n==0)
						 $delivery_stamp = strtotime($info['delivery_date']);
					else
						$delivery_stamp = $stamp + (60 * 60 * 24 * $deliveryDays);
                    
                    $yoil =date("w", $delivery_stamp);
                    if ($yoil == 6)
                        $delivery_stamp += 60 * 60 * 24 * 2;
                    else if ($yoil == 0)
                        $delivery_stamp += 60 * 60 * 24;
                    
                    foreach ($hList as $h) {
                        if ($delivery_stamp == $h['stamp'])
                            $delivery_stamp += 60 * 60 * 24;
                    }
                    
                    $t['delivery_stamp'] = $delivery_stamp;
                    $sms_stamp = $stamp - (60 * 60 * 24 * $smsDays);
                    $t['sms_stamp'] = $sms_stamp;
                    $isCal = true;
                    if ($t['orderNo']) {
                        $row = $this->db->fetch("SELECT COUNT(*) as cnt FROM " . DB_ORDER . " WHERE orderNo='{$t['orderNo']}'");
                        $od = [];
			
                        if ($row['cnt'] > 0) {
							$sql = "SELECT * FROM ". DB_ORDER . " WHERE orderNo='{$t['orderNo']}'";
							if ($tmp = $this->db->fetch($sql)) {
								$od = $tmp;
								$od['orderStatusStr'] = $status[$od['orderStatus']];
                                $s = substr($od['orderStatus'], 0, 1);
                                if (!in_array($s, ['p','g','d','s']))
                                    $no--;
							}
                        }
	
                        $t['order'] = $od;
                        $isCal = false;
                    }
                    
                    if ($no >= 0) {
                        $discount = $cfg['discount'][$no]?$cfg['discount'][$no]:$cfg['discount'][count($cfg['discount']) - 1];
                    } else {
                        $discount = 0;
                    }
                    
                    $t['discount'] = $discount;

                    if ($isCal) {
                        $cart->getCartList($cartSno,$address, $postValue, true, $info['memNo'], $discount, true);
                        $orderPrice = $cart->setOrderSettlePayCalculation($postValue);
                        $orderPrice['totalDc'] = $orderPrice['totalGoodsDcPrice'] + $orderPrice['totalSumMemberDcPrice'] + $orderPrice['totalCouponGoodsDcPrice'];
                        $t['orderPrice'] = $orderPrice;
                    }
                    $no++;
                    $list[] = $t;
                }
            }
        }

        return $list;
    }
    
   public function get()
   {
       switch ($this->mode) {
            case "getCardInfo" : 
                $info = [];
                if ($this->idxCard) {
                    $sql = "SELECT a.*, b.memNm, b.memId FROM wm_subscription_cards AS a 
                                    LEFT JOIN " . DB_MEMBER . " AS b ON a.memNo = b.memNo 
                                    WHERE a.idx='".$this->idxCard."'";
                    if ($tmp = $this->db->fetch($sql)) {
                        $tmp['payKey'] = $this->decrypt($tmp['payKey']);
                        $info = $tmp;
                    }
                }
                
                return $info;
                break;
             case "getPayKey" : 
                if ($this->idxCard) {
                    $sql = "SELECT payKey FROM wm_subscription_cards WHERE idx='".$this->idxCard."'";
                    if ($tmp = $this->db->fetch($sql))
                        return $this->decrypt($tmp['payKey']);
                }
                break;
             case "getGoodsCfg" : 
                return $this->getGoodsCfg();
                break;
             case "getScheduleList" : 
                $cfg = $this->getCfg();
                $scheduleEa = $this->scheduleEa?$this->scheduleEa:$cfg['deliveryEa'][0];
                $period = $this->period?$this->period:$cfg['period'][0];
                $stamp = $this->stamp?$this->stamp:0;


                $list = $priceList = [];
                if ($this->cartInfo) {
                    foreach ($this->cartInfo as $keys => $values) {
                        foreach ($values as $key => $value) {
                            foreach ($value as $k => $v) {
                                $priceList[] = ["goodsPrice" => $v['price']['goodsPrice'], 'goodsCnt' => $v['goodsCnt']];
                            }
                        }
                    }
                }

				//gd_debug($this->delivery_date);

                if ($tmp = $this->getScheduleList($scheduleEa, $stamp, $period,$this->delivery_date)) {
                    foreach ($tmp as $k => $v) {
                        $dc = $cfg['discount'][$k]?$cfg['discount'][$k]:$cfg['discount'][count($cfg['discount']) - 1];
                        $data = $v;
                        if ($priceList) {
                            $discount = 0;
                            foreach ($priceList as $li)
                               $discount += round($li['goodsPrice'] * $dc / 1000) * 10 * $li['goodsCnt'];
                            
                           $data['price'] = $this->price;
                           $data['discount'] = $discount;
                           $data['rate'] = $dc;
                        } else {
                          $discount = round($this->price * $dc / 1000) * 10;
                          $data['price'] = $this->price;
                          $data['discount'] = $discount;
                          $data['rate'] = $dc;                          
                        } 
                        
                        $list[] = $data;
                    }
                }
                
                return $list;
                break;
             case "subscriptionInfo" : 
                $info = [];
                if ($this->uid) {
                    if ($tmp = $this->db->fetch("SELECT * FROM wm_subscription_apply WHERE uid='".$this->uid."'")) {
                        $info = $tmp;
                        $items = [];
                        $sql = "SELECT * FROM wm_subscription_apply_items WHERE uid='".$this->uid."'";
                        if ($tmps = $this->db->query_fetch($sql))
                            $items = $tmps;
                        
                        $info['items'] = $items;
                    }
                }
                return $info;
                break;
             /* 일괄처리 START */
             case "batch_pay_list" :  
                $list = array();
                if ($this->stamp) {
                    $stamp = strtotime(date("Ymd", $this->stamp));
                    $estamp = $stamp + (60 * 60 * 24);
                    $sql = "SELECT a.*,b.memNo FROM wm_subscription_schedule_list a INNER JOIN wm_subscription_apply b ON a.uid=b.uid  WHERE a.schedule_stamp >= {$stamp} AND a.schedule_stamp < {$estamp} AND a.isPayed='0' AND a.isStop='0'  ORDER BY a.idx";
                    if ($tmp = $this->db->query_fetch($sql))
                        $list = $tmp;
                }

                return $list;
                break;
             case "batch_sms_list" : 
                $list = array();
                if ($this->stamp) {
                    $cfg = $this->getCfg();
                    $smsDays = $cfg['smsDays']?$cfg['smsDays']:0;
                    $stamp = strtotime(date("Ymd", $this->stamp));
                    $stamp += (60 * 60 * 24 * $smsDays);
                    $estamp = $stamp + (60 * 60 * 24);
                    $sql = "SELECT a.*,b.memNo FROM wm_subscription_schedule_list a INNER JOIN wm_subscription_apply b ON a.uid=b.uid WHERE a.schedule_stamp >= {$stamp} AND a.schedule_stamp < {$estamp} AND a.isPayed='0' AND a.isStop='0' AND a.smsStamp='0'  ORDER BY a.idx";
                    if ($tmp = $this->db->query_fetch($sql))
                        $list = $tmp;
                }

                return $list;
                break;
             case "batch_auto_extend_list" : 
                $list = [];
                $stamp = strtotime(date("Ymd"));
                $sql = "SELECT uid, deliveryEa, period FROM wm_subscription_apply WHERE autoExtend='1' ORDER BY regStamp asc";
                if ($tmp = $this->db->query_fetch($sql)) {
                    foreach ($tmp as $li) {
                       $sql = "SELECT COUNT(*) as cnt FROM wm_subscription_schedule_list WHERE uid='{$li['uid']}' AND schedule_stamp >= {$stamp} AND isPayed='0' AND isStop='0'";
                       $row = $this->db->fetch($sql);
                                             
                       if (empty($row['cnt'])) {
                           $sql = "SELECT * FROM wm_subscription_schedule_list WHERE uid='{$li['uid']}' ORDER BY schedule_stamp desc";
                           $ro = $this->db->fetch($sql);
                           $list[] = $ro;
                            
                        }
                     }
                  } 
                  return $list;
                break;
            /* 일괄처리 END */
       }
   }
    public function pay_procss($idx)
	{

		if ($idx)
            $this->pay($idx, $this->boolean);
   }

   public function sms_procss($idx)
	{
		$cfg = $this->getCfg();
		 if ($idx) {
			if (!$info = $this->getSubscription($idx))
				return false;
			
			$this->updatePayPrice($info['uid']);
			$info = $this->getSubscription($idx);
			
			$smsTemplate = $cfg['smsTemplate'];
			$info['date'] = date('Y.m.d', $info['schedule_stamp']);
			if ($info['orderCellPhone']) {
				if ($this->sendSms($info['orderCellPhone'], $smsTemplate, $info)) {

					
					$sql = "UPDATE wm_subscription_schedule_list SET smsStamp='".time()."' WHERE idx='".$idx."'";
					$this->db->query($sql);
					return true;
				}
				
			}
		 }
   }   
   public function process()
   {
       $cfg = $this->getCfg();
       switch ($this->mode) {
          /* 일괄처리 START */
          case "batch_pay" : 
            if ($this->idx)
                return $this->pay($this->idx, $this->boolean);
            break;
          case "batch_sms" : 
             if ($this->idx) {
                if (!$info = $this->getSubscription($this->idx))
                    return false;
                
                $this->updatePayPrice($info['uid']);
                $info = $this->getSubscription($this->idx);
                
                $smsTemplate = $cfg['smsTemplate'];
                $info['date'] = date('Y.m.d', $info['schedule_stamp']);
                if ($info['orderCellPhone']) {
                    if ($this->sendSms($info['orderCellPhone'], $smsTemplate, $info)) {
                        $sql = "UPDATE wm_subscription_schedule_list SET smsStamp='".time()."' WHERE idx='".$this->idx."'";
                        $this->db->query($sql);
                        return true;
                    }
                    
                }
             }
             break; 
           case "batch_auto_extend" : 
             if ($this->idx) {
			   
                if (!$info = $this->getSubscription($this->idx))
                    return false;
                
				return $this->extendSchedule($info['uid'],$info['deliveryEa'], $info['period']);

             }
             break;
          /* 일괄처리 END */
       }
   }
   
   public function updatePayPrice($uid = null)
   {
       if ($uid) {
           if ($list = $this->getScheduleListByUid($uid)) {
               foreach ($list as $li) {
                   $settlePrice = $li['order']['settlePrice']?$li['order']['settlePrice']:$li['orderPrice']['settlePrice'];
                    $sql = "UPDATE wm_subscription_schedule_list SET payPrice='{$settlePrice}' WHERE idx='{$li['idx']}'";
                   $this->db->query($sql);
               }
           }
           
       }
   }
   
   /* SMS 전송 처리 */
   public function sendSms($mobile, $contents, $changeCode = array())
   {
       $cfg = $this->getCfg();
       $bool = false;
       $smsPoint = Sms::getPoint();


       if ($smsPoint >= 1) {
            foreach ($changeCode as $k => $v) {
                if (is_numeric($v))
                    $v = number_format($v);

                $contents = str_replace("{{$k}}", "{$v}", $contents);
            }

            $adminSecuritySmsAuthNumber = Otp::getOtp(8);
            $receiver[0]['cellPhone'] = $mobile;
            $smsSender = \App::load('Component\\Sms\\SmsSender');
            $smsSender->setSmsPoint($smsPoint);

            if(mb_strlen($contents, 'euc-kr')>90){
              $smsSender->setMessage(new LmsMessage($contents));
            }else{
              $smsSender->setMessage(new SmsMessage($contents));
            }

            $smsSender->validPassword($cfg['smsPass']);
            $smsSender->setSmsType('user');
            $smsSender->setReceiver($receiver);
            $smsSender->setLogData(['disableResend' => false]);
            $smsSender->setContentsMask([$adminSecuritySmsAuthNumber]);
            $smsResult = $smsSender->send();
            $smsResult['smsAuthNumber'] = $adminSecuritySmsAuthNumber;

            if ($smsResult['success'] === 1)
              $bool = true;
       }

       return $bool;
   }
}