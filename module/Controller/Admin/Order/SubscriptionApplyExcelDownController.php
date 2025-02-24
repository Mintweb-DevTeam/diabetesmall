<?php
namespace Controller\Admin\Order;

use App;
use Request;


class SubscriptionApplyExcelDownController extends \Controller\Admin\Controller
{
	protected $db="";
	public $obj="";
	public function index()
	{
		
		set_time_limit(0);
		//민트웹 2022.11.07 추가 시작-정기결제신청리스트 엑셀다운
		header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment; filename=정기결제신청리스트.xls");  //File name extension was wrong
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);			


		$this->db =App::load(\DB::class);
        $this->obj = App::load(\Component\Subscription\Subscription::class);
        $adminobj = App::load(\Component\Subscription\SubscriptionAdmin::class);
        $status = $this->obj->getOrderStatusList();

        $get = Request::get()->toArray();
        
        $list = $q = [];
        $conds = "";
        
        if ($get['searchDate'][0]) {
            $stamp = strtotime($get['searchDate'][0]);
            $q[] = "a.regStamp >= {$stamp}";
        }
        if ($get['searchDate'][1]) {
            $stamp = strtotime($get['searchDate'][1]) + (60 * 60 * 24);
            $q[] = "a.regStamp < {$stamp}";
        }
        
        if ($get['autoExtend'])
            $q[] = "a.autoExtend='1'";
        
        $total = $amount = 0;
        $sql = "SELECT COUNT(*) as cnt FROM wm_subscription_apply";
        $row = $this->db->fetch($sql);
        $amount = $row['cnt'];
        
        $sql = "SELECT COUNT(*) as cnt FROM wm_subscription_apply AS a 
                        LEFT JOIN " . DB_MEMBER . " AS m ON a.memNo = m.memNo 
                        {$conds} 
                        ORDER BY a.regStamp desc";
        $row = $this->db->fetch($sql);
        $total = $row['cnt'];
        if ($get['sopt'] && $get['skey']) {
            $fields = "";
            switch ($get['sopt']) {
                 case "all": 
                    $fields = "CONCAT(a.uid,a.orderName,a.receiverName,m.memNm,a.orderPhone, a.orderCellPhone,a.receiverPhone,a.receiverCellPhone)";
                    break;
                 case "name" :
                    $fields = "CONCAT(a.orderName,a.receiverName, m.memNm)";
                    break;
                 case "mobile" : 
                    $fields = "CONCAT(a.orderPhone,a.orderCellPhone,a.receiverPhone,a.receiverCellPhone)";
                    break;
                 default : 
                    $fields = $get['sopt'];
            }
            
            $q[] = $fields . " LIKE '%{$get['skey']}%'";
        }
        
        if ($q)
            $conds = " WHERE " . implode(" AND ", $q);
        
        $sql = "SELECT a.*,  m.memId, m.memNm FROM wm_subscription_apply AS a 
                        LEFT JOIN " . DB_MEMBER . " AS m ON a.memNo = m.memNo 
                        {$conds} 
                        ORDER BY a.regStamp desc";



		echo "<table border='1'><tr><th>신청번호</th><th>신청일</th><th>주문자/신청회원</th><th>받는분</th><th>주문상품번호</th><th>정기배송기간</th><th>배송주기</th><th>배송횟수</th><th>배송회차</th><th>연장횟수</th><th>자동연장</th><th>결제카드</th><th>배송주소</th></tr>";
         if ($tmp = $this->db->query_fetch($sql)) {


			
             foreach ($tmp as $t) {
                 $card = $this->obj->setCard($t['idx_card'])->get();
                 $t['cardNm'] = $card['cardNm'];
                 $totalPrice = 0;
                 if ($items = $adminobj->getApplyItems($t['uid'])) {
                     foreach ($items as $it) {
                         $totalPrice += $it['totalPrice'];
                     }
                 }

                 $t['items'] = $items;
                 
                 $t['totalPrice'] = $totalPrice;
                 
                 $scheduleList = $this->obj->getScheduleListByUid($t['uid']);
                 $t['scheduleList'] = $scheduleList;
				
				 $orderCnt=0;
				foreach($scheduleList as $skey =>$st){
					if(!empty($st['orderNo']))
						$orderCnt++;
				}
				
				


				if(empty($orderCnt) || empty($t['deliveryEa']))
					$schedule_count_mod=0;
				else
					$schedule_count_mod = $orderCnt % $t['deliveryEa'];


				if($schedule_count_mod==0){
					$t['schedule_number']=$t['deliveryEa'];
				}else{
					$t['schedule_number']=$schedule_count_mod."/".$t['deliveryEa'];

					
				}

				 $logSQL="select count(idx) as cnt from wm_subscription_extension_num where uid='{$t['uid']}'";
				 $logRow=$this->db->fetch($logSQL);
				 $t['extend_count']=$logRow['cnt'];


				 $period = explode("_", $t['period']);

				 if($t['autoExtend']==1)
					 $extend="자동연장";
				 else
					 $extend="";
				?>
				<tr>
					<td style='mso-number-format:\@;'><?=$t['uid']?></td>
					<td><?=date("Y.m.d", $t['regStamp'])?></td>
					<td>
						<div><?=$t['orderName']?></div>
						<?php if ($t['memId']) : ?>
						(<?=$t['memId']?>)
						<?php endif; ?>
					</td>
					<td><?=$t['receiverName']?></td>
					<td>
						<?=$t['items'][0]['goodsNo']?>
						<?//=$t['items'][0]['goodsNm']?>
						<?php //if (count($t['items']) > 1) echo "외".(count($t['items'])  - 1) . "건"; ?>

					</td>
					<td>
						<?php if ($t['scheduleList']) : ?>
						<?=date("Y.m.d", $t['scheduleList'][0]['schedule_stamp'])?>~<?=date("Y.m.d", $t['scheduleList'][count($t['scheduleList']) - 1]['schedule_stamp'])?>
						<?php endif; ?>
					</td>
					<td>
						<?=$period[0]?><?=($period[1] == 'week')?"주":"달"?> 마다

					</td>
					<td><?=$t['deliveryEa']?>(총<?=count($t['scheduleList'])?>회)</td>

					<td style='mso-number-format:\@;'>
						<?=$t['schedule_number']?>
					</td>
					<td>
						<?=$t['extend_count']?>
					</td>
					<td>
						
						<?=$extend?>
					</td>


					<td>
						<?=$t['cardNm']?><br>
					</td>
					<td>
						(<?=$t['receiverZonecode']?>)<?=$t['receiverAddress']?> <?=$t['receiverAddressSub']?>
					</td>
				</tr>

				<?php


               
             }
         }

		echo"</table>";
		exit;
		//민트웹 2022.11.07 추가 종료
	
	}

}