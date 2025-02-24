<?php
namespace Controller\Mobile\Goods;
use Session;
use Request;
use Framework\Debug\Exception\AlertRedirectException;
class GoodsViewController extends \Bundle\Controller\Mobile\Goods\GoodsViewController
{
	public function index()
	{
		
		
		$goodsNo = \Request::get()->get("goodsNo");

		//2024.10.21웹앤모바일 상품상세 보기후 로그인시 다시 상품상세로 이동 추가시작		
		$goodsCheck = new \Component\Wm\StartEventGoodsCheck();
		$goodsCheckResult = $goodsCheck->GoodsTypeCheck($goodsNo);
			
			
		//2024.11.25웹앤모바일 추가시작
		$params = \Request::get()->toArray();
						
		foreach ($params as $key => $value) {
			
			\Session::set("param_field_".$key,$value);
			
		}	
		//2024.11.25웹앤모바일 추가종료		
					
		if( !Session::get('member.memNo') && (!empty($goodsCheckResult['startGoods']) || !empty($goodsCheckResult['otherStartGoods']) || !empty($goodsCheckResult['directGoods']))){
			
			\Session::set("direct_goodsView",$goodsNo);
			

			
		}
		//2024.10.21웹앤모바일 상품상세 보기후 로그인시 다시 상품상세로 이동 추가종료
		
			
		\Session::del('event_goodsNo');//2024.10.21웹앤모바일 추가
		
		$cartIdx = Session::get("cartIdx");
		$memNo = Session::get("member.memNo");
		$db = \App::load(\DB::class);
		
		if(!empty($memNo) && !empty($cartIdx)){
			
			Session::del('cartIdx');
			
			$siteKey = Session::get('siteKey');
			
			
			
			$db->query("update wm_subscription_cart set memNo='{$memNo}' where siteKey='{$siteKey}'");

			$subObj= new \Component\Subscription\Subscription();
			$subCfg = $subObj->getCfg();
			$period = end($subCfg['period']);
			$deliveryEa = end($subCfg['deliveryEa']);

			?>
				<form name="tform" method="post" action="../subscription/order.php">
					<input type="hidden" name="cartSno[]" value="<?=$cartIdx?>"/>
					<input type="hidden" name="period" value="<?=$period?>"/>
					<input type="hidden" name="deliveryEa" value="<?=$deliveryEa?>"/>

				</form>
				<script type="text/javascript">document.tform.submit();</script>
			<?php

			exit;

		}
		
		

		
		parent::index();
		$goodsView = $this->getData("goodsView");
		$cossia = new \Component\Cossia\Cossia;
		
		///2023.03.10웹앤모바일 추가시작
		if(empty($memNo)===false){
		    //$db= \App::load(\DB::class);
		    
		    $goodsNo = $goodsView['goodsNo'];
		    $strSQL = "select o.orderNo from ".DB_ORDER." o INNER JOIN ".DB_ORDER_GOODS." og ON og.orderNo=o.orderNo where o.memNo=? and og.goodsNo=? and substr(og.orderStatus,1,1) IN ('o','p','g','d','s') group by o.orderNo";
		    $row    = $db->query_fetch($strSQL,['ii',$memNo,$goodsNo]);
		    
		    $orderNo = $row[0]['orderNo'];
		    
		}
		///2023.03.10웹앤모바일 추가종료
		
		$codata = $cossia->getEduAuth($orderNo);
		$this->setData('codata', $codata);

		// 2022-07-23 이벤트 상품 구매 확인 여부 Cossia
		if( $goodsView['isEvent'] == 'y' ){
			if( !Session::get("member.memNo") ){
				throw new AlertRedirectException(__('로그인하셔야 본 서비스를 이용하실 수 있습니다.'), 401, null, '../member/login.php?returnUrl=' . urlencode("/goods/goods_view.php?goodsNo=" .Request::get()->get('goodsNo') ), 'top');
			}
			$this->setData('coEvent', $cossia->isBuyEventGoods( $goodsView['goodsNo'] ) );
		}
		
		
		//2024.11.06웹앤모바일 추가시작-구매가능여부
		//if(\Request::getRemoteAddress() =="182.216.219.157" || \Request::getRemoteAddress() =="211.49.123.117"){	
			$buyObj = new \Component\Wm\BuyCheck();
			$buyResult = $buyObj->goodsBuyChk($goodsView['goodsNo']);
			
			$this->setData("buyResult",$buyResult);
						
		//}
		//2024.11.06웹앤모바일 추가종료-구매가능여부
		
		///2024.02.01웹앤모바일 스타트 상품 시작
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			if(!empty($memNo)){
				$startSQL  = "select * from wm_start_goods where goodsNo=?";
				$startROW = $db->query_fetch($startSQL,['i',$goodsView['goodsNo']]);
				
				
				if(!empty($startROW[0]['idx'])){
					$memSQL = "select * from ".DB_MEMBER." where memNo=?";
					$memRow = $db->query_fetch($memSQL,['i',$memNo]);
					
					$this->setData("startGoods",1);
					$this->setData("move_goodsNo",$startROW[0]['move_goodsNo']);
					$this->setData("cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));
				}
				
			}
		//}
		///2024.02.01웹앤모바일 스타트 상품 종료
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			//2024.10.18 웹앤모바일 추가시작
			if(!empty($memNo)){
				$otherStartSQL  = "select * from wm_start_goods where other_goodsNo=?";
				$otherStartROW = $db->query_fetch($otherStartSQL,['i',$goodsNo]);
				
				
				if(!empty($otherStartROW[0]['idx'])){
					$memSQL = "select * from ".DB_MEMBER." where memNo=?";
					$memRow = $db->query_fetch($memSQL,['i',$memNo]);
					
					$this->setData("otherStartGoods",1);
					$this->setData("other_move_goodsNo",$otherStartROW[0]['other_move_goodsNo']);
					$this->setData("other_cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));
					$this->getView()->setPageName("goods/goods_view_new_start");
				}
				$eventStartSQL  = "select * from wm_event_goods where goodsNo=?";
				$evntStartROW = $db->query_fetch($eventStartSQL,['i',$goodsNo]);
				
				if(!empty($evntStartROW[0]['idx'])){
					
					$this->js("document.location.href='/goods/event_view.php?goodsNo={$goodsNo}';");
					exit;
				}	
			}			
			//2024.10.18 웹앤모바일 추가종료
		//}		
		
		
		
			
		if (\Request::isRefresh()) {
			$this->setData("page_reload",1);
		}	
		
		
		//20210.09.09 민트웹 추가시작
		$memId = \Session::get("member.memId");
		//if ($memId=="dev@mintweb.co.kr") {
		//2023.01.11민트웹 정기결제스킨경로 변경함 - 기존goods_view_subscription스킨은 레이어가 망가져있음으며 이유는 알수없음
		if ($goodsView['isSubscription']){
		        $this->getView()->setPageName("goods/goods_view_subscription2");
				//2024.01.31웹앤모바일 추가시작
				$now_date=date("Y-m-d");
				if($now_date <= "2024-04-30"){
					
					$fixedPrice = number_format(($goodsView['fixedPrice']+ $goodsView['option']['optionPrice'])*$goodsView['minOrderCnt']);
					$this->setData("fixedPrice",$fixedPrice);
					
				
					$this->getView()->setPageName('goods/goods_view_subscription_one');
				}		
		}
		//}else{
		//	if ($goodsView['isSubscription'])
		//		$this->getView()->setPageName("goods/goods_view_subscription");
		//}
		//20210.09.09 민트웹 추가종료
		
		
	
		

	}
}