<?php
namespace Component\Database;
class DBTableField extends \Bundle\Component\Database\DBTableField
{
	public static function tableGoods($conf = null)
	{
		$arrField = parent::tableGoods($conf);
		$arrField[] = ['val' => 'coViewOrder', 'typ' => 's', 'def' => 'n']; // 교육신청 보여주기
        $arrField[] = ['val' => 'isSubscription', 'typ' => 'i', 'def' => 0, 'name' => '정기배송상품여부'];//정기배송필드 민트웹추가 2021.09.09
        $arrField[] = ['val' => 'listViewFl', 'typ' => 's', 'def' => 'n', 'name' => '정기배송상품리스트보임여부'];// 민트웹추가 2021.10.21
        $arrField[] = ['val' => 'subscriptionDetail', 'typ' => 's', 'def' => '', 'name' => '정기배송상품 설명'];// 민트웹추가 2021.10.21
        $arrField[] = ['val' => 'subscription_top_detail', 'typ' => 's', 'def' => '', 'name' => '정기배송상품 상단짧은설명'];// 민트웹추가 2021.10.21
        $arrField[] = ['val' => 'subscription_bottom_detail', 'typ' => 's', 'def' => '', 'name' => '정기배송상품 하단짧은설명'];// 민트웹추가 2021.10.21
        $arrField[] = ['val' => 'subscription_shortDescription', 'typ' => 's', 'def' => '', 'name' => '정기배송 짧은설명'];// 민트웹추가 2021.10.21
		$arrField[] = ['val' => 'autoExtend', 'typ' => 's', 'def' => 'n', 'name' => '배송횟수,자동연장사용여부'];// 민트웹추가 2023.01.09
		$arrField[] = ['val' => 'orderBtn', 'typ' => 's', 'def' => 'n', 'name' => '정기결제버튼보임여부'];// 민트웹추가 2023.01.09
		
		// 2022-07-23 Cossia
		$arrField[] = ['val' => 'isEvent', 'typ' => 's', 'def' => 'n', 'name' => '이벤트 상품 여부'];
		
		//2024.11.05웹앤모바일
		$arrField[] = ['val' => 'buyGoods', 'typ' => 's', 'def' => 'n', 'name' => '구매상품여부'];
		
		return $arrField;
	}
	
	
	//정기배송필드 민트웹추가 2021.09.09
    public static function tableOrderInfo()
    {
        $arrField = parent::tableOrderInfo();
        $arrField[] = ['val' => 'isSubscription', 'typ' => 'i', 'def' => 0, 'name' => '정기결제 주문여부 체크'];
		
		// 주문시 병원정보 추가 Cossia 2022-03-16
		$arrField[] = ['val' => 'hospitalNm', 'typ' => 's', 'def' => '', 'name' => '병원명'];
		$arrField[] = ['val' => 'hospitalAdd', 'typ' => 's', 'def' => '', 'name' => '병원주소'];
        return $arrField;
    }
	
}