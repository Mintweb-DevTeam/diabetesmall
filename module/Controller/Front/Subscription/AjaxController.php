<?php
namespace Controller\Front\Subscription;

use App;
use Request;
use Session;
 
class AjaxController extends \Controller\Front\Controller 
{
    public function index()
    {
        $get = Request::get()->toArray();
        $post = Request::post()->toArray();
        $in = array_merge($get, $post);
        $db = App::load('DB');
        $obj = App::load(\Component\Subscription\Subscription::class);
        
		//wm_auto_log에 추가 웹앤모바일 2025.01.16
		
		if(!empty($in['uid'])){
			$memNo = \Session::get("member.memNo");
			$before_sql = "SELECT * FROM wm_subscription_apply WHERE uid = " . $in['uid'];
			$before_list = $db->fetch($before_sql);	
		}
        switch ($in['mode']) {
            case "getSchedule" : 

                $cart = App::load(\Component\Subscription\Cart::class);
                $cartInfo = $cart->getCartList($in['cartSno']);
                $obj = App::load(\Component\Subscription\Subscription::class);
                $cfg = $obj->getCfg();
                $this->setData("subCfg", $cfg);
            
                $list = $obj->setCartInfo($cartInfo)
                              ->setPrice($cart->totalGoodsPrice)
                              ->setMode("getScheduleList")
                              ->setPeriod($in['period'])
                              ->setScheduleEa($in['ea'])
							  ->setDeliveryDate($in['hope_date'])
                              ->get();
                if ($list) {
                    foreach ($list as $k => $v) {
                        $v['date'] = date("Y.m.d", $v['stamp']);
                        $v['delivery_date'] = date("Y.m.d", $v['delivery_stamp']);                        
                        $list[$k] = $v;
                    }
                }
                echo json_encode($list);
                break;
             case "chkCardPassword" : 
                 if ($in['idx_card'] && $in['password']) {
                     
                     $card = $obj->setCard($in['idx_card'])
                                      ->setMode("getCardInfo")
                                      ->get();
                                      
                     echo password_verify($in['password'], $card['password']);
                 }
                 
                break;
             case "chkCardExists" : 
                if ($memNo = Session::get("member.memNo")) {
                    if ($card = $obj->getCards($memNo)) 
                        echo 1;                   
                }
                break;
             case "changeAutoExtend" : 
                if ($in['uid']) {
                    $chk = $in['chk']?1:0;
                    $sql = "UPDATE wm_subscription_apply SET autoExtend='{$chk}' WHERE uid='{$in['uid']}'";
                    echo $db->query($sql);
                }
                break;
             case "changeCard" : 
                if ($in['idx_card'] && $in['uid']) {
                    $sql = "UPDATE wm_subscription_apply SET idx_card='{$in['idx_card']}' WHERE uid='{$in['uid']}'";
                    echo $db->query($sql);
                }
                break;
             case "changeSchedule" :
                if ($in['idx'] && $in['date']) {
                    $today = strtotime(date("Ymd"));
                    $stamp = strtotime($in['date']);
                    if ($stamp <= $today)
                        echo 2;
                    else {
                        $sql = "UPDATE wm_subscription_schedule_list SET schedule_stamp='{$stamp}' WHERE idx='{$in['idx']}'";
                        echo $db->query($sql);
                    }
                }
                break;
			case "ExtendAdd" :
				$obj->extendSchedule($in['uid'],$in['schedule_num'],$in['period'],"user");
				echo '1';
				break;
			case "ExtendAddUser":

					//2022.05.27추가

					$memNo = Session::get("member.memNo");

					$sql="select * from wm_subscription_apply where uid=?";
					$row = $db->query_fetch($sql,['i',$in['uid']],false);


					$upSQL="update wm_subscription_apply set deliveryEa=? where uid=?";
					$db->bind_query($upSQL,['ii',$in['schedule_num'],$in['uid']]);

					$strSQL="select count(uid) as cnt from wm_subscription_schedule_list where uid=? order by idx DESC";
					$strROw = $db->query_fetch($strSQL,['s',$in['uid']]);

					$scheduleSQL="select * from wm_subscription_schedule_list where uid=? order by idx DESC";
					$scheduleROWS = $db->query_fetch($scheduleSQL,['i',$in['uid']]);
					
					
					if($row['deliveryEa']<$in['schedule_num']){
						
						if($strROw[0]['cnt']<$in['schedule_num'])
							$schedule_num=$in['schedule_num']-$strROw[0]['cnt'];
						else{
							$schedule_num=$strROw[0]['cnt'] % $in['schedule_num'];

						}

						if($schedule_num>0)
							$obj->extendSchedule($in['uid'],$schedule_num,$in['period'],"user");


					}else{

						
						if($strROw[0]['cnt']<$in['schedule_num'])
							$schedule_num=$in['schedule_num']-$strROw[0]['cnt'];
						else if($strROw[0]['cnt']>$in['schedule_num']){
							
							$schedule_num=$strROw[0]['cnt'] % $in['schedule_num'];
							

							if($schedule_num==0)
								$schedule_num=$in['schedule_num'];//count($scheduleROWS);
						}else{
							$schedule_num=0;
						}
						

						$i=0;
						if($schedule_num>0){
							foreach($scheduleROWS as $k =>$v){
							
								if($i>=$schedule_num){

									break;
								}
								
								if(!empty($v['idx'])){
									//gd_debug("delete from wm_subscription_schedule_list where idx='{$v['idx']}' and isPayed='0' and orderNo=''");
									$db->query("delete from wm_subscription_schedule_list where idx='{$v['idx']}' and isPayed='0' and orderNo=''");
								}
								$i++;
							}
						}
					
						
					
					}
					
					$content="배송회차".$row['deliveryEa']."에서".$in['schedule_num']."회차로 변경함";
					$db->query("insert into wm_log set memNo='$memNo',content='{$content}',regDt=sysdate()");

				break;
        }
		
		
		
		
		//wm_auto_log에 추가 웹앤모바일 2025.01.16	
		if(!empty($in['uid'])){
			$after_sql = "SELECT * FROM wm_subscription_apply WHERE uid = " . $in['uid'];
			$after_list = $db->fetch($after_sql);	

			$after_content=json_encode($after_list);
			$before_content=json_encode($before_list);		

			$ip= \Request::getRemoteAddress();
			$db->query("insert into wm_auto_log set uid='{$after_list['uid']}',before_content='{$before_content}',after_content='{$after_content}',memNo='$memNo',change_position='user'				,regDt=sysdate(),ip='$ip'");						
		}
        exit;
    }
}