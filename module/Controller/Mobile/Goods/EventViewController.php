<?php
namespace Controller\Mobile\Goods;
use Session;
use Request;
use Framework\Debug\Exception\AlertRedirectException;
class EventViewController extends \Bundle\Controller\Mobile\Goods\GoodsViewController
{
	public function index()
	{
		//if( !Session::get('member.memNo') ){
		//	echo '<script>alert("로그인이 필요합니다.");window.location.replace("/member/login.php?returnUrl=/goods/goods_view.php?goodsNo='.Request::get()->get('goodsNo').'");</script>';
		//	exit;
		//}


		$goodsNo=Request::get()->get('goodsNo');
			if(\Request::getRemoteAddress() =="182.216.219.157"){
								
				$this->setData("ex",1);
			}
		
		//2024.10.21웹앤모바일 상품상세 보기후 로그인시 다시 상품상세로 이동 추가시작
		
		$goodsCheck = new \Component\Wm\StartEventGoodsCheck();
		$goodsCheckResult = $goodsCheck->GoodsTypeCheck($goodsNo);
			
		if( !Session::get('member.memNo') && !empty($goodsCheckResult['eventGoods'])){
			
			\Session::set("direct_event_goodsView",$goodsNo);
			
		}
		

		\Session::del('event_goodsNo');//2024.10.21웹앤모바일 추가
		$cartIdx = Session::get("cartIdx");
		$memNo = Session::get("member.memNo");
		$db = \App::load(\DB::class);
			
		parent::index();
		$goodsView = $this->getData("goodsView");
		//$cossia = new \Component\Cossia\Cossia;
				
		
        $memId = \Session::get("member.memId");
		//$goodsNo=Request::get()->get('goodsNo');

		//if(!empty($memNo)){
		$startSQL  = "select * from wm_event_goods where goodsNo=?";
		$startROW = $db->query_fetch($startSQL,['i',$goodsNo]);
		
		
		if(!empty($startROW[0]['idx'])){
			$memSQL = "select * from ".DB_MEMBER." where memNo=?";
			$memRow = $db->query_fetch($memSQL,['i',$memNo]);
			
			$this->setData("startGoods",1);
			$this->setData("move_goodsNo",$startROW[0]['move_goodsNo']);
			$this->setData("cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));
		}else{
			$this->js("alert('이벤트 상품이 아닙니다.');document.location.href='/';");
			exit;
		}
			
		$this->setData('goodsNo', $goodsNo);
		$this->setData("goodsPrice", $goodsPrice);
		
		
		//2024.11.06웹앤모바일 추가시작-구매가능여부
		
		//if(\Request::getRemoteAddress() =="182.216.219.157" || \Request::getRemoteAddress() =="211.49.123.117"){	
			$buyObj = new \Component\Wm\BuyCheck();
			$buyResult = $buyObj->goodsBuyChk($goodsNo);
			
			$this->setData("buyResult",$buyResult);
						
		//}
		//2024.11.06웹앤모바일 추가종료-구매가능여부	
		

	}
}