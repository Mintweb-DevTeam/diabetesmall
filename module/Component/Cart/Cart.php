<?php
namespace Component\Cart;

class Cart extends \Bundle\Component\Cart\Cart
{

	public function getCartGoodsData($cartIdx = null, $address = null, $tmpOrderNo = null, $isAddGoodsDivision = false, $isCouponCheck = false, $postValue = [], $setGoodsCnt = [], $setAddGoodsCnt = [], $setDeliveryMethodFl = [], $setDeliveryCollectFl = [], $deliveryBasicInfoFl = false)
	{
		$tmp = parent::getCartGoodsData($cartIdx, $address, $tmpOrderNo, $isAddGoodsDivision, $isCouponCheck, $postValue, $setGoodsCnt, $setAddGoodsCnt, $setDeliveryMethodFl, $setDeliveryCollectFl, $deliveryBasicInfoFl);
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			//2024.06.21웹앤모바일 장바구상품의 카테고리 및 브랜드 정보시작
			$goods = new \Component\Goods\Goods();
			
			foreach($tmp  as $key => $val){
			
				foreach($val as $key2 => $val2){
				
					foreach($val2 as $key3 => $val3){
						$goodsView = $goods->getGoodsView($val3['goodsNo']);
						
						$tmp[$key][$key2][$key3]['cateNm']=$goodsView['cateNm'];
						
						if(empty($goodsView['brandNm']))
							$goodsView['brandNm']="아델라";
						
						$tmp[$key][$key2][$key3]['brandNm']=$goodsView['brandNm'];
						
						if(empty($val3['optionNm']))
							$variant=$val3['goodsNm'];
						else
							$variant = $val3['optionNm'];
						
						$tmp[$key][$key2][$key3]['variant']=strip_tags($variant);						
						
					}
				}
			}
			//2024.06.21웹앤모바일 장바구상품의 카테고리 및 브랜드 정보종료
		//}
		return $tmp;

	}
}