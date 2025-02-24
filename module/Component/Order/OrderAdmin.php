<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Component\Order;

/**
 * 주문 class
 * 주문 관련 관리자 Class
 *
 * @package Bundle\Component\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderAdmin extends \Bundle\Component\Order\OrderAdmin
{
    public function getOrderListForAdmin($searchData, $searchPeriod, $isUserHandle = false)
    {

		$result = parent::getOrderListForAdmin($searchData, $searchPeriod, $isUserHandle);

		//if(\Request::getRemoteAddress()=="112.145.36.156"){

			$db = \App::load(\DB::class);

            foreach ($result['data'] as $orderNo => $orderData) {
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    foreach ($sVal as $dKey => $dVal) {
                        foreach ($dVal as $key => $val) {

							//사은품 존재 여부체크시작
							/*
							$giftSQL="select count(orderNo) as cnt from ".DB_ORDER_GIFT." where orderNo=?";
							$giftROW=$db->query_fetch($giftSQL,['i',$val['orderNo']],false);

							if($giftROW['cnt']>0)
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['gift']="추가증정대상";
								*/

							//사은품 존재 여부체크종료

							
							$sql="select a.idx,b.period,b.uid,b.deliveryEa,b.autoExtend from wm_subscription_schedule_list a INNER JOIN wm_subscription_apply b ON a.uid=b.uid where a.orderNo='{$val['orderNo']}'";
							$krow = $db->fetch($sql);

							
						
							if(!empty($krow['period'])){
								
								$period = explode("_",$krow['period']);

								$tmp=$period[0]; 

								if($period[1]=="week")
									$tmp.="주";
								if($period[1]=="month")
									$tmp.="달";

								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['period']= $tmp;

							}else{
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['period']= "";
							}



							if(!empty($krow['uid'])){
								$sql="select count(idx) as cnt from wm_subscription_schedule_list where uid='{$krow['uid']}'";

								$crow = $db->fetch($sql);
								$tcount =$crow['cnt'];




								$sql="select count(idx) as cnt from wm_subscription_schedule_list where uid='{$krow['uid']}' and orderNo<>'' and idx<='{$krow['idx']}'";
								$rows = $db->fetch($sql);
								
								
								$order_cnt =$rows['cnt'];
								
								if(!empty($krow['deliveryEa'])){
									$schedule_count_mod = $order_cnt % $krow['deliveryEa'];
								}

								if($schedule_count_mod==0){
									if(!empty($krow['deliveryEa'])){
										$schedule_multi = $order_cnt / $krow['deliveryEa'];
									}
									$schedule_count = $schedule_multi * $krow['deliveryEa'];

									$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['schedules']=$krow['deliveryEa'];
								}else{
									$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['schedules']=$schedule_count_mod."/".$krow['deliveryEa'];

									
									if($krow['deliveryEa']>6){
				
										$mod=6;
									
										$multi=$krow['deliveryEa']%2;
										$schedule=$schedule_count_mod%$mod;

										if($schedule_count_mod>1 && $schedule==1 && $multi==0){
											if($val['goodsNo'] != "1000000060" && $val['goodsNo'] != "1000000076"){
												$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['gift']="추가증정대상";
											}
										
										}
									}

								}
								 //$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['schedules']= $order_cnt."/".$tcount;

								 $logSQL="select count(idx) as cnt from wm_subscription_extension_num where uid='{$krow['uid']}'";
								 $logRow=$this->db->fetch($logSQL);
								 $result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['autoExtendCount']=$logRow['cnt'];
								 //$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['autoExtendCount']=$tcount/$krow['deliveryEa']-1;

								 if($krow['autoExtend']==1)
									 $autoExtend="자동연장";
								 else
									 $autoExtend="연장안함";
								 $result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['autoExtend']=$autoExtend;


								
								
								if($val['goodsNo'] == "1000000060"){
									$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['text']="12회 센서증정";
								}
								
								if($krow['deliveryEa']==12 && $val['goodsNo'] == "1000000076"){
									$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['text']="12회 1증정";
								}
								
							}else{
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['schedules']= "";
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['autoExtendCount']="";
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['autoExtend']="";
																
								$result['data'][$orderNo]['goods'][$sKey][$dKey][$key]['text']="";
								
							}


						}
					}
				}
			}

			if(\Request::getRemoteAddress()=="182.216.219.50"){
				//gd_debug($result);
			}
		//}
		return $result;
	}
}