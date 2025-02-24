<?php

namespace Component\Order;

use Request;
use App;
use Component\Mail\MailAutoObserver;
use Component\Godo\NaverPayAPI;
use Component\Member\Member;
use Component\Naver\NaverPay;
use Component\Database\DBTableField;
use Component\Delivery\OverseasDelivery;
use Component\Deposit\Deposit;
use Component\ExchangeRate\ExchangeRate;
use Component\Mail\MailMimeAuto;
use Component\Mall\Mall;
use Component\Mall\MallDAO;
use Component\Member\Manager;
use Component\Member\Util\MemberUtil;
use Component\Mileage\Mileage;
use Component\Policy\Policy;
use Component\Sms\Code;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Component\Sms\SmsAutoObserver;
use Component\Validator\Validator;
use Component\Goods\SmsStock;
use Component\Goods\KakaoAlimStock;
use Component\Goods\MailStock;
use Encryptor;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Helper\MallHelper;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\UrlUtils;
use Globals;
use Logger;
use LogHandler;
use Session;
use Framework\Utility\DateTimeUtils;

class Order extends \Bundle\Component\Order\Order
{
    public function getOrderView2($orderNo)
    {
        // �ֹ� ��ȣ üũ
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('�ش� �ֹ���ȣ�� ��ȸ�� �� �����ϴ�.'));
        }

        // �ֹ� ����
        $getData = $this->getOrderDataInfo2($orderNo);

        // ��ȸ���̰� ���̹� �����϶� �ֹ���ȣ ġȯ
        if (MemberUtil::checkLogin() == 'guest' && $getData['orderChannelFl'] == 'naverpay') {
            //$orderNo = $getData['orderNo'];
        }

        // �ֹ� ����Ÿ üũ
        if (empty($getData) === true) {
            throw new Exception(self::TEXT_NOT_EXIST_ORDER_INFO);
        }

        $useMultiShippingKey = false;
        if ($getData['multiShippingFl'] == 'y') {
            $useMultiShippingKey = true;
        }

        // �ֹ� ��ǰ ����
        $getData['goods'] = $this->getOrderGoodsData($orderNo, null, null, null, 'user', false, false, null, null, false, $useMultiShippingKey);

        $getData['orderAddGoodsCnt'] = 0;
        foreach ($getData['goods'] as $aVal) {
            $getData['orderAddGoodsCnt'] += $aVal['addGoodsCnt'];
        }

        // �ֹ� ��� ����
        $getData['delivery'] = $this->getOrderDelivery($orderNo);

        // �ֹ� ��� ������ ���� �� ����
        $oneDelivery = reset(reset($getData['delivery']));
        $getData['deliveryWeightInfo'] = json_decode($oneDelivery['deliveryWeightInfo'], true);

        // �ֹ� ���ݿ����� ����
        if ($getData['receiptFl'] == 'r') {
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $getData['cash'] = $cashReceipt->getOrderCashReceipt($orderNo);
        }

        // �ֹ� ���ݰ�꼭 ����
        if ($getData['receiptFl'] == 't') {
            $tax = \App::load('\\Component\\Order\\Tax');
            $getData['tax'] = $tax->getOrderTaxInvoice($orderNo);
        }

        // �ֹ� ����ǰ ����
        $getData['gift'] = $this->getOrderGift($orderNo, null, 40);

        // ����� ���� ����
        $getData['receiverCorrectFl'] = 'n';
        foreach ($this->statusPolicy as $key => $val) {
            if ($key == 'autoCancel') {
                continue;
            }
            if (substr($key, 0, 1) == $getData['orderStatus']) {
                if (isset($val['correct']) === true) {
                    if ($val['correct'] == 'y') {
                        $getData['receiverCorrectFl'] = 'y';
                    }
                }
            }
        }

        // ����� ���� ������ ��� ���� ����
        if ($getData['receiverCorrectFl'] == 'y') {
            $getData['receiverPhoneArr'] = explode('-', $getData['receiverPhone']);
            $getData['receiverCellPhoneArr'] = explode('-', $getData['receiverCellPhone']);
        }

        return $getData;
    }
    
    
    public function getOrderDataInfo2($orderNo)
    {
        // �ֹ� �⺻ ����
        $arrExclude = [
            'orderIp',
            'orderPGLog',
            'orderDeliveryLog',
            'orderAdminLog',
        ];

        // getOrderData���� arrWhere�� arrBind�� ����ϴ� �����ؼ� Ȯ�� �ʿ�
        $getData = $this->getOrderData($orderNo, $arrExclude);

        // �ֹ� ������ ���� ���
        if (empty($getData)) {
            throw new Exception(__('�ֹ������� �����ϴ�.'));
        }

        // �ֹ� �߰� �ʵ� ����
        $getData['addField'] = $this->getOrderAddFieldView($getData['addField']);

        // ����� ����
        $getData['orderMemo'] = nl2br($getData['orderMemo']);

        // ������ �Ա� ���� ����
        $getData['bankAccount'] = explode(STR_DIVISION, $getData['bankAccount']);

        // PG ��� ó��
        $getData['pgSettleNm'] = explode(STR_DIVISION, $getData['pgSettleNm']);
        $getData['pgSettleCd'] = explode(STR_DIVISION, $getData['pgSettleCd']);

        // �ֹ� ���� ó��
        $getData['orderStatus'] = substr($getData['orderStatus'], 0, 1);

        // ���� ���
        $getData['settleName'] = $this->getSettleKind($getData['settleKind']);
        $getData['settleGateway'] = substr($getData['settleKind'], 0, 1);
        $getData['settleMethod'] = substr($getData['settleKind'], 1, 1);

        // ����ũ�ο���
        if ($getData['settleGateway'] === 'e') {
            $getData['settleName'] = __('����ũ�� ') . $getData['settleName'];
        }

        // ��Ƽ���� ȯ�� �⺻ ����
        $getData['currencyPolicy'] = json_decode($getData['currencyPolicy'], true);
        $getData['exchangeRatePolicy'] = json_decode($getData['exchangeRatePolicy'], true);
        $getData['currencyIsoCode'] = $getData['currencyPolicy']['isoCode'];
        $getData['exchangeRate'] = $getData['exchangeRatePolicy']['exchangeRate' . $getData['currencyPolicy']['isoCode']];

        // ������ ��� ���� ���� (PG �ŷ� ������ - ���ݿ����� ����)
        if ($getData['settleMethod'] == 'c') {
            $getData['settleReceipt'] = 'card';
        } elseif ($getData['settleMethod'] == 'b') {
            $getData['settleReceipt'] = 'bank';
        } elseif ($getData['settleMethod'] == 'v') {
            $getData['settleReceipt'] = 'vbank';
        } elseif ($getData['settleMethod'] == 'h') {
            $getData['settleReceipt'] = 'hphone';
        } else {
            $getData['settleReceipt'] = '';
        }
        $pgCodeConfig = App::getConfig('payment.pg');
        if (empty($getData['settleReceipt']) === false && isset($pgCodeConfig->getPgReceiptUrl()[$getData['pgName']][$getData['settleReceipt']]) === false) {
            $getData['settleReceipt'] = '';
        }

        $getData['absTotalEnuriDcPrice'] = abs($getData['totalEnuriDcPrice']);

        return $getData;
    }
    
    //정기결제 스케줄링시 주문번호 중복오류방지시작
    public function generateOrderNo() {
        
        $tmp = parent::generateOrderNo();
        
        $db=\App::load(\DB::class);
        $sql="select count(orderNo) as cnt from ".DB_ORDER." where orderNo=?";
        $row = $db->query_fetch($sql,['s',$tmp],false);
        
        if($row['cnt']<=0){
            return $tmp;
        }else{
            while(1){
                $result = $this->wgenerateOrderNo();
                
                $sql="select count(orderNo) as cnt from ".DB_ORDER." where orderNo=?";
                $row = $db->query_fetch($sql,['s',$result],false);
                
                if($row['cnt']<=0){
                    break;
                }
                
            }
            return $result;
        }
        
    }
    public function wgenerateOrderNo()
    {
        // 0 ~ 999 마이크로초 중 랜덤으로 sleep 처리 (동일 시간에 들어온 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 999));
        
        // 0 ~ 99 마이크로초 중 랜덤으로 sleep 처리 (첫번째 sleep 이 또 동일한 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 99));
        
        // microtime() 함수의 마이크로 초만 사용
        list($usec) = explode(' ', microtime());
        
        // 마이크로초을 4자리 정수로 만듬 (마이크로초 뒤 2자리는 거의 0이 나오므로 8자리가 아닌 4자리만 사용함 - 나머지 2자리도 짜름... 너무 길어서.)
        $tmpNo = sprintf('%04d', round($usec * 10000));
        
        // PREFIX_ORDER_NO (년월일시분초) 에 마이크로초 정수화 한 값을 붙여 주문번호로 사용함, 16자리 주문번호임
        return PREFIX_ORDER_NO . $tmpNo;
    }
    //정기결제 스케줄링시 주문번호 중복오류방지종료
}