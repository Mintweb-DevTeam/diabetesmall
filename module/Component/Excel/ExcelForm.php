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
 * @link      http://www.godo.co.kr
 */
namespace Component\Excel;
use Component\Database\DBTableField;
use Component\Member\Manager;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\GodoUtils;
use Session;
/**
 * Class ExcelForm
 * @package Bundle\Component\Excel
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 *          atomyang
 */
class ExcelForm extends \Bundle\Component\Excel\ExcelForm
{
    public function setExcelFormMember($location)
    {
		
		$tmp = parent::setExcelFormMember($location);

		//2021.09.16민트웹 정기결제회원추가 시작
		if($location =="member_list"){
			$subscription=['name'=>__('정기결제회원')];
			$tmp['subscription']=$subscription;
		}
		//2021.09.16민트웹 정기결제회원추가 종료
		
		return $tmp;

	}

   public function setExcelFormOrder($location)
    {
        //@formatter:off
        $setBaseData = [
            'orderNo' =>['name'=>__('주문 번호'),'orderFl'=>'y'],
            'orderTypeFl' =>['name'=>__('주문 유형'),'orderFl'=>'n'],
            'apiOrderNo' =>['name'=>__('외부채널주문번호'),'orderFl'=>'y'],
            'memNm' =>['name'=>__('회원명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'memNo' =>['name'=>__('회원 아이디'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'groupNm' =>['name'=>__('회원등급명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'orderGoodsNm' =>['name'=>__('주문 상품명'),'orderFl'=>'y'],
            'orderGoodsCnt' =>['name'=>__('주문 품목 개수'),'orderFl'=>'y'],
            'orderChannelFl' =>['name'=>__('주문 채널'),'orderFl'=>'y'],
            'totalSettlePrice'=>['name'=>__('총 결제 금액'),'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'y'],
            'totalGoodsPrice'=>['name'=>__('총 품목 금액'),'type'=>'price','orderFl'=>'y'],
            'totalDeliveryCharge'=>['name'=>__('총 배송 금액'),'type'=>'price','orderFl'=>'y'],
            'totalGift'=>['name'=>__('총 사은품 정보'),'orderFl'=>'y'],
            'totalUseDeposit'=>['name'=>__('사용된 총 예치금'),'type'=>'deposit','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalUseMileage'=>['name'=>__('사용된 총 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMemberDcPrice'=>['name'=>__('총 회원 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalGoodsDcPrice'=>['name'=>__('총 상품 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalCouponDcPrice'=>['name'=>__('총 쿠폰 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMileage'=>['name'=>__('적립될 총 마일리지') ,'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalGoodsMileage'=>['name'=>__('적립될 총 상품 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMemberMileage'=>['name'=>__('적립될 총 회원 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalCouponMileage'=>['name'=>__('적립될 총 쿠폰 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'settleKind'=>['name'=>__('결제방법'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'bankAccount'=>['name'=>__('입금계좌'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'bankSender' =>['name'=>__('입금자'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'receiptFl' =>['name'=>__('영수증 신청 여부'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgResultCode' =>['name'=>__('PG 결과 코드'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgTid' =>['name'=>__('PG 거래 번호'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgAppNo' =>['name'=>__('PG 승인 번호'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'paymentDt' =>['name'=>__('입금일자'),'scmDisplayFl'=>'n'],
            'orderDt' =>['name'=>__('주문일자'),'orderFl'=>'y'],
            'orderName' =>['name'=>__('주문자 이름'),'orderFl'=>'y'],
            'orderEmail' =>['name'=>__('주문자 e-mail'),'orderFl'=>'y'],
            'orderPhone' =>['name'=>__('주문자 전화번호'),'orderFl'=>'y'],
            'orderCellPhone' =>['name'=>__('주문자 핸드폰 번호'),'orderFl'=>'y'],
            'receiverName' =>['name'=>__('수취인 이름'),'orderFl'=>'y'],
            'receiverPhone' =>['name'=>__('수취인 전화번호'),'orderFl'=>'y'],
            'receiverCellPhone' =>['name'=>__('수취인 핸드폰 번호'),'orderFl'=>'y'],
            'receiverZipcode' =>['name'=>__('수취인 구 우편번호 (6자리)'),'orderFl'=>'y'],
            'receiverZonecode' =>['name'=>__('수취인 우편번호'),'orderFl'=>'y'],
            'receiverAddress' =>['name'=>__('수취인 주소'),'orderFl'=>'y'],
            'receiverAddressSub' =>['name'=>__('수취인 나머지 주소'),'orderFl'=>'y'],
            'receiverAddressTotal' =>['name'=>__('수취인 전체주소'),'orderFl'=>'y'],
            'orderMemo' =>['name'=>__('주문시 남기는 글'),'orderFl'=>'y'],
            'orderDeliverySno' =>['name'=>__('배송번호'),'scmDisplayFl'=>'n'],
            'goodsDeliveryCollectFl' =>['name'=>__('배송비 구분'),'scmDisplayFl'=>'n'],
            'scmNo' =>['name'=>__('공급사코드'),'scmDisplayFl'=>'n'],
            'scmNm' =>['name'=>__('공급사명'),'scmDisplayFl'=>'n'],
            'orderGoodsSno' =>['name'=>__('상품주문번호')],
            'apiOrderGoodsNo' =>['name'=>__('외부채널품목번호')],
            'orderCd' =>['name'=>__('주문코드(순서)')],
            'orderStatus' =>['name'=>__('주문상태')],
            'goodsNo' =>['name'=>__('상품코드')],
            'goodsCd' =>['name'=>__('자체상품코드')],
            'goodsModelNo' =>['name'=>__('모델명')],
            'goodsType' =>['name'=>__('상품종류')],
            'goodsNm' =>['name'=>__('상품명')],
            'optionInfo' =>['name'=>__('옵션정보')],
            'optionCode' =>['name'=>__('자체옵션코드')],
            'goodsCnt' =>['name'=>__('상품수량')],
            'goodsWeightVolume' =>['name'=>__('상품 무게/용량')],
            'goodsTotalWeightVolume' =>['name'=>__('상품 배송 총 무게/용량')],
            'cateCd' =>['name'=>__('카테고리명')],
            'brandCd' =>['name'=>__('브랜드명')],
            'makerNm' =>['name'=>__('제조사')],
            'originNm' =>['name'=>__('원산지')],
            'optionTextInfo' =>['name'=>__('텍스트옵션정보')],
            'divisionUseDeposit' =>['name'=>__('사용된 예치금'),'type'=>'deposit','scmDisplayFl'=>'n'],
            'divisionUseMileage' =>['name'=>__('사용된 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'memberDcPrice' =>['name'=>__('회원 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'goodsDcPrice' =>['name'=>__('상품 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'couponGoodsDcPrice' =>['name'=>__('쿠폰 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'memberMileage' =>['name'=>__('적립 회원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'goodsMileage' =>['name'=>__('적립 상품 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'couponGoodsMileage' =>['name'=>__('적립 쿠폰 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'goodsTaxInfo' =>['name'=>__('상품부가세정보')],
            'goodsPrice' =>['name'=>__('판매가'),'type'=>'price'],
            'fixedPrice' =>['name'=>__('정가'),'type'=>'price'],
            'costPrice' =>['name'=>__('매입가'),'type'=>'price'],
            'commission' =>['name'=>__('수수료율')],
            'optionPrice' =>['name'=>__('옵션 금액'),'type'=>'price'],
            'optionCostPrice' =>['name'=>__('옵션 매입가'),'type'=>'price'],
            'optionTextPrice' =>['name'=>__('텍스트옵션 금액'),'type'=>'price'],
            'ogi.presentSno' =>['name'=>__('사은품 지급 제목')],
            'ogi.giftNo' =>['name'=>__('사은품 정보')],
            'invoiceCompanySno' =>['name'=>__('배송 업체 번호')],
            'invoiceNo' =>['name'=>__('송장 번호')],
            'addField' =>['name'=>__('추가 정보')],
            'hscode' => ['name'=>__('HS코드')],
            'multiShippingOrder' => ['name'=>__('배송지')],
            'multiShippingPrice' => ['name'=>__('배송지별 배송비'),'type'=>'price'],
            'totalEnuriDcPrice' => ['name'=>__('총 운영자추가할인 금액'),'type'=>'price','orderFl'=>'y'],
            'enuri' => ['name'=>__('운영자추가할인 금액'),'type'=>'price'],
            'sinerg_code' => ['name'=>__('시너지코드')],
            /*'period' => ['name'=>__('배송주기')],
            'schedule' => ['name'=>__('배송회차')],
			*/
			
			// 주문시 병원정보 추가 Cossia 2022-03-16
			'hospitalNm' => ['name'=>__('병원명')],
			'hospitalAdd' => ['name'=>__('병원주소')],

			//2022.06.10민트웹 추가
			'schedule_tcount' => ['name'=>__('배송횟수')],
			'period' => ['name'=>__('배송주기')],
			'schedule' => ['name'=>__('배송회차')],
			'autoExtend' => ['name'=>__('자동연장여부')],
			'autoExtendCount' => ['name'=>__('자동연장횟수')],
			'gift' => ['name'=>__('센서추가증정')],

        ];
        $setTaxData = [
            'sno' => ['name'=>__('번호')],
            'regDt' => ['name'=>__('발행요청일')],
            'orderNo' => ['name'=>__('주문번호')],
            'applicantNm' => ['name'=>__('주문자')],
            'orderStatusStr' => ['name'=>__('주문상태')],
            'requestNm' => ['name'=>__('요청인')],
            'taxBusiNo' => ['name'=>__('사업자번호')],
            'taxCompany' => ['name'=>__('회사명')],
            'taxCeoNm' => ['name'=>__('대표자명')],
            'taxService' => ['name'=>__('업태')],
            'taxItem' => ['name'=>__('종목')],
            'taxAddress' => ['name'=>__('사업장 주소')],
            'taxEmail' => ['name'=>__('발행 이메일')],
            'totalPrice' => ['name'=>__('결제금액')],
            'tax' => ['name'=>__('세금등급')],
            'reTotalPrice' => ['name'=>__('발행액')],
            'price' => ['name'=>__('공급가액')],
            'vat' => ['name'=>__('세액')],
            'issueDt' => ['name'=>__('발행일')],
        ];
        if ($location =='order_list_refund' || $location =='order_list_all') {
            $setGlobalData = [
                'mallSno' => ['name'=>__('상점구분'),'orderFl'=>'y' ],
                'orderGoodsNmStandard' => ['name'=>__('주문 상품명(해외상점)'),'orderFl'=>'y'],
                'global_totalSettlePrice' => ['name'=>__('총 결제금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsPrice' => ['name'=>__('총 품목금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalDeliveryCharge' => ['name'=>__('총 배송금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'overseasSettlePrice' => ['name'=>__('PG승인 금액'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsDcPrice' => ['name'=>__('총 상품할인 금액(해외상점)'),'type'=>'price',],
                'global_refundPrice' => ['name'=>__('총 환불금액(해외상점)'),'type'=>'price',],
                'global_completePgPrice' => ['name'=>__('카드환불 금액(해외상점)'),'type'=>'price',],
                'global_refundDeliveryCharge' => ['name'=>__('총 환불 배송비(해외상점)'),'type'=>'price',],
                'goodsNmStandard' => ['name'=>__('상품명(해외상점)')],
            ];
        } else {
            $setGlobalData = [
                'mallSno' => ['name'=>__('상점구분'),'orderFl'=>'y' ],
                'orderGoodsNmStandard' => ['name'=>__('주문 상품명(해외상점)'),'orderFl'=>'y'],
                'global_totalSettlePrice' => ['name'=>__('총 결제금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsPrice' => ['name'=>__('총 품목금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalDeliveryCharge' => ['name'=>__('총 배송금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'overseasSettlePrice' => ['name'=>__('PG승인 금액'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsDcPrice' => ['name'=>__('총 상품할인 금액(해외상점)'),'type'=>'price',],
                'goodsNmStandard' => ['name'=>__('상품명(해외상점)')],
            ];
        }
        if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE)) {
            $orderBasic = gd_policy('order.basic');
            if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
                unset($orderBasic['userHandleScmFl']);
            }

            if (((!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) && in_array($location, ['order_list_all', 'order_list_order', 'order_list_goods', 'order_list_delivery', 'order_list_delivery_ok'])) {
                $setBaseData['userHandleInfo'] = [
                    'name' => __('고객 클레임 신청정보'),
                ];
            }
        }
        //@formatter:on

        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'order_list_fail':
                $setData = $setBaseData;
                break;
            case 'order_list_delivery_ok':
            case 'order_list_delivery':
            case 'order_list_goods':
                $addSetData =  [
                    'packetCodeFl' =>['name'=>__('묶음배송 여부'),'orderFl'=>'y'],
                    'packetCode' =>['name'=>__('묶음배송 그룹번호'),'orderFl'=>'y']
                ];

                $setData = array_merge($setBaseData, $addSetData);
                break;
            case 'order_list_settle':
                $addSetData =  [
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'packetCodeFl' =>['name'=>__('묶음배송 여부'),'orderFl'=>'y'],
                    'packetCode' =>['name'=>__('묶음배송 그룹번호'),'orderFl'=>'y']
                ];

                $setData = array_merge($setBaseData, $addSetData);
                break;
            case 'order_list_order':
            case 'order_list_pay':

                $addSetData =  [
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')]
                ];

                $setData = array_merge($setBaseData, $addSetData);
                break;

            case 'order_list_user_exchange':
            case 'order_list_user_return':
            case 'order_list_user_refund':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수'),'scmDisplayFl'=>'n'],
                    'userHandleReason' =>['name'=>__('사유')],
                    'userHandleDetailReason' =>['name'=>__('고객클레임 신청 메모')],
                    'userRefundAccountNumber' =>['name'=>__('환불계좌')],
                    'userHandleRegDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'adminHandleReason' =>['name'=>__('클레임 운영자 클레임 관리 메모')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_exchange' :
            case 'order_list_exchange_cancel' :
            case 'order_list_exchange_add' :

                $addSetData =  ['handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'handleRegDt' =>['name'=>__('교환 접수일자')],
                    'handleDt' =>['name'=>__('교환 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_back':

                $addSetData =  ['handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundMethod' =>['name'=>__('환불수단')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'handleRegDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')]];

                $setData = array_merge($setBaseData, $addSetData);
                break;
            case 'order_list_all':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수'),'scmDisplayFl'=>'n'],
                    'refundPrice'=>['name'=>__('총 환불금액'),'type'=>'price'],
                    'completeCashPrice'=>['name'=>__('현금 환불금액'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'completePgPrice'=>['name'=>__('카드 환불금액'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'completeDepositPrice'=>['name'=>__('예치금 환불금액'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'completeMileagePrice'=>['name'=>__('마일리지 환불금액'),'type'=>'mileage' ,'scmDisplayFl'=>'n'],
                    'refundCharge'=>['name'=>__('환불수수료'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'refundDeliveryCharge'=>['name'=>__('총 환불 배송비'),'type'=>'price'],
                    'refundUseDeposit'=>['name'=>__('총 환원 예치금'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'refundUseMileage'=>['name'=>__('총 환원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundUseDepositCommission'=>['name'=>__('총 환원 예치금 수수료'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'refundUseMileageCommission'=>['name'=>__('총 환원 마일리지 수수료'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'regDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'cancelGoodsCnt' =>['name'=>__('취소 상품 수량'),'scmDisplayFl'=>'n'],
                    'exchangeGoodsCnt' =>['name'=>__('교환 상품 수량')],
                    'backGoodsCnt' =>['name'=>__('반품 상품 수량')],
                    'refundGoodsCnt' =>['name'=>__('환불 상품 수량')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'packetCodeFl' =>['name'=>__('묶음배송 여부'),'orderFl'=>'y'],
                    'packetCode' =>['name'=>__('묶음배송 그룹번호'),'orderFl'=>'y']];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_cancel':

                $addSetData =  ['handleReason' =>['name'=>__('사유')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_refund':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수') ,'scmDisplayFl'=>'n'],
                    'refundPrice'=>['name'=>__('총 환불금액'),'type'=>'price'],
                    'completeCashPrice'=>['name'=>__('현금 환불금액'),'type'=>'price','scmDisplayFl'=>'n'],
                    'completePgPrice'=>['name'=>__('카드 환불금액'),'type'=>'price','scmDisplayFl'=>'n'],
                    'completeDepositPrice'=>['name'=>__('예치금 환불금액'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'completeMileagePrice'=>['name'=>__('마일리지 환불금액'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundCharge'=>['name'=>__('환불수수료'),'type'=>'price','scmDisplayFl'=>'n'],
                    'refundDeliveryCharge'=>['name'=>__('총 환불 배송비'),'type'=>'price'],
                    'refundUseDeposit'=>['name'=>__('총 환원 예치금'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'refundUseMileage'=>['name'=>__('총 환원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundUseDepositCommission'=>['name'=>__('총 환원 예치금 수수료'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'refundUseMileageCommission'=>['name'=>__('총 환원 마일리지 수수료'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundMethod' =>['name'=>__('환불수단')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'regDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'tax_invoice_request':
                $addSetData = [
                    'adminMemo' => ['name'=>__('메모')],
                ];
                $setData = array_merge($setTaxData, $addSetData);
                break;
            case 'tax_invoice_list':
                $addSetData = [
                    'issueFl' => ['name'=>__('종류')],
                    'printFl' => ['name'=>__('발행상태')],
                    'processDt' => ['name'=>__('처리일')],
                    'adminMemo' => ['name'=>__('메모')],
                ];
                $setData = array_merge($setTaxData, $addSetData);
                break;
        }
        //@formatter:on
        if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $setData = array_merge($setData, ['purchaseNm' => ['name' => __('매입처')]]);
        }

        $orderBasic = gd_policy('order.basic');
        if (gd_isset($orderBasic['safeNumberFl'])) {
            $setData = array_merge($setData, ['receiverSafeNumber' =>['name'=>__('수취인 안심번호'), 'orderFl'=>'y']]);
        }

        $setData = array_merge($setData, $setGlobalData);

        return $setData;

    }

}