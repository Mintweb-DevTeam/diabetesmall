<?php
namespace Controller\Mobile\Goods;

class LayerOptionController extends \Bundle\Controller\Mobile\Goods\LayerOptionController
{
    public function index()
    {
		parent::index();
		
		
		$goods = \App::load('\\Component\\Goods\\Goods');
		
		
		///2024.02.01웹앤모바일 스타트 상품 시작
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			
			$memNo = \Session::get("member.memNo");
			$db = \App::load(\DB::class);
			
			$postValue = \Request::post()->toArray();
			if(\Request::getRemoteAddress() =="211.49.123.117"){
				$this->setData("ex",1);
			}
			
			$goodsView = $goods->getGoodsView($postValue['goodsNo']);
			
			if ($goodsView['isSubscription']){
			}else{
			
				if(!empty($memNo)){
					$startSQL  = "select * from wm_start_goods where goodsNo=?";
					$startROW = $db->query_fetch($startSQL,['i',$postValue['goodsNo']]);
					
					
					if(!empty($startROW[0]['idx'])){
						$memSQL = "select * from ".DB_MEMBER." where memNo=?";
						$memRow = $db->query_fetch($memSQL,['i',$memNo]);
						
						$this->setData("startGoods",1);
						$this->setData("move_goodsNo",$startROW[0]['move_goodsNo']);
						$this->setData("cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));
					}
					
					//if(\Request::getRemoteAddress()=="182.216.219.157"){
					
						$OtherStartSQL  = "select * from wm_start_goods where other_goodsNo=?";
						$OtherStartROW = $db->query_fetch($OtherStartSQL,['i',$postValue['goodsNo']]);
						
						
						if(!empty($OtherStartROW[0]['idx'])){
							$memSQL = "select * from ".DB_MEMBER." where memNo=?";
							$memRow = $db->query_fetch($memSQL,['i',$memNo]);
							
							$this->setData("other_startGoods",1);
							$this->setData("other_move_goodsNo",$startROW[0]['move_goodsNo']);
							$this->setData("cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));
						}	
						
						
						$eventSQL  = "select * from wm_event_goods where goodsNo=?";
						$eventROW = $db->query_fetch($eventSQL,['i',$postValue['goodsNo']]);
						
						if(!empty($eventROW[0]['idx'])){
							$memSQL = "select * from ".DB_MEMBER." where memNo=?";
							$memRow = $db->query_fetch($memSQL,['i',$memNo]);
							
							$this->setData("event_startGoods",1);
							$this->setData("cellPhone",str_replace("-",'',$memRow[0]['cellPhone']));	
							
						}
						
						
					//}
					
				}
			}
		//}
		///2024.02.01웹앤모바일 스타트 상품 종료
		
    }
}