<?php
// 웹앤모바일 세일즈포스 데이터 연동 ================================================== START
namespace Component\Wm;

use Request;
use Session;
use App;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;

class WmSalesforce
{
    protected $db = null;

    public $rootDir = null;
    public $writerType = null;
    public $writerNo = null;
    public $writerIp = null;
    public $applyFl = false;

    // common function start ----------------------------------------------------------------------------------------------------

    // 생성자
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }

        $controller = App::getController();
        if (!empty($controller)) {
            $this->rootDir = $controller->getRootDirecotory();
        }

        switch ($this->rootDir) {
            case 'admin':
                $this->writerType = 'admin';
                $this->writerNo = Session::get('manager.sno');
                break;
            case 'front':
            case 'mobile':
                $this->writerType = 'member';
                $this->writerNo = Session::get('member.memNo');
                break;
            case 'api':
                $this->writerType = 'api';
                break;
        }

        $this->writerIp = Request::getRemoteAddress();

        $arrIp = array('182.216.219.157');
        if (in_array(Request::getRemoteAddress(), $arrIp)) {
            $this->applyFl = true;
        }
    }

    // 로그 저장
    public function saveLog($linkValue, $title = null, $contents = null)
    {
        $arrValue = null;
        $arrValue[] = "linkCode='{$linkValue['code']}'";
        $arrValue[] = "linkType='{$linkValue['type']}'";
        $arrValue[] = "linkSno='{$linkValue['sno']}'";
        if (!empty($linkValue['data'])) {
            $arrValue[] = "linkData='" . json_encode($linkValue['data'], JSON_UNESCAPED_UNICODE) . "'";
        }
        if (!empty($linkValue['response'])) {
            $arrValue[] = "linkResponse='" . json_encode($linkValue['response'], JSON_UNESCAPED_UNICODE) . "'";
        }
        $arrValue[] = "title='{$title}'";
        $arrValue[] = "contents='{$contents}'";

        return $this->db->query("insert into wm_salesforceLog set " . implode(',', $arrValue));
    }

    // api 전송
    public function setApi($linkValue, $cronFl = false)
    {
        if (empty($linkValue)) {
            return null;
        }

        $data = $linkValue['data'];
        $domain = 'https://adc-apac--dev23.sandbox.my.salesforce.com';
        $url = null;
        $mode = $linkValue['code'] . '_' . $linkValue['type'];

        switch ($mode) {
            case 'register_libre' :
                $url = $domain . '/services/data/v58.0/sobjects/LibreCare_KR_Consumer__c';
                break;
            case 'register_member' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Consumer__c';
                break;
            case 'modify_member' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Consumer__c/Adela_ID__c/' . $data['Adela_ID__c'];
                break;
            case 'register_order' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Order__c';
                break;
            case 'modify_order' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Order__c/Order_ID__c/' . $data['Order_ID__c'];
                break;
            case 'register_orderGoods' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Order_Line_Item__c';
                break;
            case 'modify_orderGoods' :
                $url = $domain . '/services/data/v58.0/sobjects/Webshop_KR_Order_Line_Item__c/Order_Line_Item_ID__c/' . $data['Order_Line_Item_ID__c'];
                break;
        }

        if (empty($url)) {
            return null;
        }

        $linkValue['url'] = $url;

        // cURL 초기화
        $ch = curl_init($url);

        // 요청 헤더 설정
        $headers = [
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json'
        ];

        $jsonData = json_encode($linkValue['data'], JSON_UNESCAPED_UNICODE);

        // cURL 옵션 설정
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // cURL 실행 및 결과 저장
        $response = curl_exec($ch);

        // cURL 세션 종료
        curl_close($ch);

        $result = false;
        $response = json_decode($response, true);
        $linkValue['response'] = $response;

        if ($response['success'] === true) {
            $result = true;

            // 성공건 연동 처리
            $this->saveLink($linkValue, 'y');

            // 크론일 경우 실패건 삭제
            if ($cronFl) {
                $this->deleteFail($linkValue);
            }

            // 로그 저장
            $this->saveLog($linkValue, 'log', 'success');
        } else {
            // 실패건 처리 (크론 아닌 경우에만 등록)
            if (!$cronFl) {
                $this->saveFail($linkValue);
            }

            // 로그 저장
            $this->saveLog($linkValue, 'log', 'fail');
        }

        return $result;
    }

    // 리브레 조회
    public function getLibreInfo($sno)
    {
        if (empty($sno)) {
            return null;
        }

        return $this->db->fetch("select * from co_abbottMember where sno='{$sno}'");
    }

    // 회원 조회
    public function getMemberInfo($memNo)
    {
        if (empty($memNo)) {
            return null;
        }

        return $this->db->fetch("select * from es_member where memNo='{$memNo}'");
    }

    // 주문 조회
    public function getOrderData($orderNo)
    {
        if (empty($orderNo)) {
            return null;
        }

        return $this->db->fetch("select * from es_order where orderNo='{$orderNo}'");
    }

    // 주문 배송정보 조회
    public function getOrderInfo($orderNo)
    {
        if (empty($orderNo)) {
            return null;
        }

        return $this->db->fetch("select * from es_orderInfo where orderNo='{$orderNo}'");
    }

    // 주문상품 조회
    public function getOrderGoodsData($orderNo)
    {
        if (empty($orderNo)) {
            return null;
        }

        return $this->db->query_fetch("select * from es_orderGoods where orderNo='{$orderNo}' order by orderCd asc");
    }

    // 메디컬샵 조회
    public function getPharmacyInfo($code)
    {
        if (empty($code) || $code <= 0) {
            return null;
        }

        return $this->db->fetch("select * from co_pharmacy where code='{$code}'");
    }

    // 첫 구매 조회
    public function getFirstOrderDate($memNo)
    {
        $data = $this->db->fetch("select * from es_order where memNo='{$memNo}' and firstSaleFl='y' order by regDt asc limit 1");
        if (empty($data)) {
            return null;
        }

        return $this->getDateConvert($data['regDt'], 'short');
    }

    // 최근 구매 조회
    public function getRecentOrderDate($memNo)
    {
        $data = $this->db->fetch("select * from es_order where memNo='{$memNo}' order by regDt desc limit 1");
        if (empty($data)) {
            return null;
        }

        return $this->getDateConvert($data['regDt'], 'short');
    }

    // 날짜 변수 변경
    public function getDateConvert($date, $mode = 'tz')
    {
        if (empty($date)) {
            return null;
        }

        $result = null;

        if ($mode == 'tz') {
            $arrDate = explode(' ', $date);
            $result = $arrDate[0] . 'T' . $arrDate[1] . 'Z';
        } else if ($mode == 'short') {
            $result = substr($date, 0, 10);
        }

        return $result;
    }

    // 주문 상태 리스트
    public function getOrderStatusList()
    {
        return [
            'o1' => 'Quote',
            'p1' => 'New',
            'g1' => 'Pick',
            'd1' => 'Uncommitted',
            'd2' => 'Dispatched',
            'r3' => 'cancelled',
            's1' => 'On Hold',
        ];
    }

    // 검색 조건 추가
    public function getSearchData($getValue, $type)
    {
        if (empty($type)) {
            return null;
        }

        $getData = null;

        if (in_array($getValue['linkFl'], ['success', 'fail', 'null'])) {

            $left = null;
            $where = null;
            $field = " , w.linkFl ";

            if ($getValue['linkFl'] == 'success') {
                $where = " w.linkFl='y' ";
            } else if ($getValue['linkFl'] == 'fail') {
                $where = " w.linkFl='n' ";
            } else if ($getValue['linkFl'] == 'null') {
                $where = " w.sno is null ";
            }

            if ($type == 'libre') {
                $left = " left join wm_salesforce as w on a.sno=w.linkSno and w.linkType='libre' ";
            } else if ($type == 'member') {
                $left = " left join wm_salesforce as w on m.memNo=w.linkSno and w.linkType='member' ";
            } else if ($type == 'order') {

            }

            $getData['field'] = $field;
            $getData['left'] = $left;
            $getData['where'] = $where;
        }

        return $getData;
    }

    // common function end ----------------------------------------------------------------------------------------------------

    // after function start ----------------------------------------------------------------------------------------------------

    // 리브레케어 api 생성
    public function saveLibreAfter($sno, $mode = 'register')
    {
        if (empty($sno)) {
            return false;
        }

        // 리브레 정보 조회
        $libreInfo = $this->getLibreInfo($sno);
        if (empty($libreInfo)) {
            return false;
        }

        // 연동 기본값
        $linkValue = [
            'code' => $mode,
            'type' => 'libre',
            'sno' => $sno
        ];

        // 연동 데이터 확인 및 저장
        $linkData = $this->getLink($linkValue);
        if (empty($linkData)) {
            $result = $this->saveLink($linkValue);
            if (!$result) {
                return false;
            }
        }

        // 리브레 정보 api 연동
        $privateConsignFl = json_decode($libreInfo['privateConsignFl'], true);
        $privateOfferFl = json_decode($libreInfo['privateOfferFl'], true);
        $info_collection = ($privateConsignFl[20] == 'y') ? true : false;
        $marketing_reception = ($privateConsignFl[23] == 'y') ? true : false;
        $third_party_privacy = ($privateOfferFl[25] == 'y') ? true : false;
        $third_party_sensitive = ($privateOfferFl[26] == 'y') ? true : false;

        $apiValue = null;
        $apiValue['LibreCare_Consumer_ID__c'] = $libreInfo['sno'];
        $apiValue['Full_Name__c'] = $libreInfo['memNm'];
        $apiValue['Email_Address__c'] = $libreInfo['email'];
        $apiValue['Mobile_Number__c'] = $libreInfo['cellPhone'];
        $apiValue['LC_Capture_Info_Consent__c'] = $info_collection;
        $apiValue['LC_Consent_to_Contact__c'] = $marketing_reception;
        $apiValue['LC_Share_Info_Consent__c'] = $third_party_privacy;
        $apiValue['LC_Share_Sensitive_Info_Consent__c'] = $third_party_sensitive;
        $apiValue['Access_Device__c'] = $libreInfo['device'];
        $apiValue['LibreCare_Registration_Date__c'] = $this->getDateConvert($libreInfo['regDt'], 'tz');

        $pharmacy = $this->getPharmacyInfo($libreInfo['pharmacy']);
        if (!empty($pharmacy)) {
            $apiValue['Pharmacy_Code__c'] = $pharmacy['code'];
            $apiValue['Pharmacy_Name__c'] = $pharmacy['name'];
        }

        $linkValue['data'] = $apiValue;

        return $this->setApi($linkValue);
    }

    // 회원 api 생성
    public function saveMemberAfter($memNo, $mode = 'register')
    {
        if (empty($memNo)) {
            return false;
        }

        // 회원 정보 조회
        $memberInfo = $this->getMemberInfo($memNo);
        if (empty($memberInfo) || empty($memberInfo['abbott_sno'])) {
            return false;
        }

        // 리브레 정보 조회
        $libreInfo = $this->getLibreInfo($memberInfo['abbott_sno']);
        if (empty($libreInfo)) {
            return false;
        }

        // 연동 기본값
        $linkValue = [
            'code' => $mode,
            'type' => 'member',
            'sno' => $memNo
        ];

        // 연동 데이터 확인 및 저장
        $linkData = $this->getLink($linkValue);
        if (empty($linkData)) {
            $result = $this->saveLink($linkValue);
            if (!$result) {
                return false;
            }
        }

        // 리브레 정보 api 연동
        $firstOrderDate = $this->getFirstOrderDate($memNo); // 첫 구매
        $recentOrderDate = $this->getRecentOrderDate($memNo); // 최근 구매
        $pharmacy = $this->getPharmacyInfo($libreInfo['pharmacy']);
        $privateConsignFl = json_decode($libreInfo['privateConsignFl'], true);
        $privateOfferFl = json_decode($libreInfo['privateOfferFl'], true);
        $info_collection = ($privateConsignFl[20] == 'y') ? true : false;
        $marketing_reception = ($privateConsignFl[23] == 'y') ? true : false;
        $third_party_privacy = ($privateOfferFl[25] == 'y') ? true : false;
        $third_party_sensitive = ($privateOfferFl[26] == 'y') ? true : false;

        $apiValue = null;
        $apiValue['Adela_Consumer_ID__c'] = $memberInfo['memNo'];
        $apiValue['Full_Name__c'] = $memberInfo['memNm'];
        $apiValue['Email_Address__c'] = $memberInfo['email'];
        $apiValue['Mobile_Number__c'] = $memberInfo['cellPhone'];
        $apiValue['Postal_Code__c'] = $memberInfo['zonecode'];
        $apiValue['Ship_Address_Line_1__c'] = $memberInfo['address'];
        $apiValue['Ship_Address_Line_2__c'] = $memberInfo['addressSub'];
        $apiValue['First_Webshop_Order_Date__c'] = $firstOrderDate;
        $apiValue['Recent_Webshop_Order_Date__c'] = $recentOrderDate;
        $apiValue['Access_Device__c'] = $libreInfo['device'];
        $apiValue['LibreCare_Consumer_ID__c'] = $libreInfo['sno'];
        $apiValue['Adela_Membership_Registration_Date__c'] = $this->getDateConvert($libreInfo['regDt'], 'tz');
        $apiValue['Adela_Share_Info_Consent__c'] = $third_party_privacy;
        $apiValue['Adela_Share_Sensitive_Info_Consent__c'] = $third_party_sensitive;
        $apiValue['Adela_Capture_Info_Consent__c'] = $info_collection;
        $apiValue['Adela_Consent_to_Contact__c'] = $marketing_reception;
        $apiValue['Adela_Member_Created_Date__c'] = $this->getDateConvert($memberInfo['regDt'], 'tz');
        $apiValue['Adela_Member_Modified_Date__c'] = $this->getDateConvert($memberInfo['modDt'], 'tz');

        if ($mode == 'register') {
            $apiValue['Adela_ID__c'] = $libreInfo['email'];
            $apiValue['LibreCare_Registration_Date__c'] = $this->getDateConvert($libreInfo['regDt']);
        }

        if (!empty($pharmacy)) {
            $apiValue['Pharmacy_Code__c'] = $pharmacy['code'];
            $apiValue['Pharmacy_Name__c'] = $pharmacy['name'];
        }

        $linkValue['data'] = $apiValue;

        return $this->setApi($linkValue);
    }

    // 주문 api 생성
    public function saveOrderAfter($orderNo, $mode = 'register')
    {
        if (empty($orderNo)) {
            return false;
        }

        // 주문 정보 조회
        $orderData = $this->getOrderData($orderNo);
        if (empty($orderData) || empty($orderData['memNo'])) {
            return false;
        }

        // 회원 정보 조회
        $memberInfo = $this->getMemberInfo($orderData['memNo']);
        if (empty($memberInfo) || empty($memberInfo['abbott_sno'])) {
            return false;
        }

        // 리브레 정보 조회
        $libreInfo = $this->getLibreInfo($memberInfo['abbott_sno']);
        if (empty($libreInfo)) {
            return false;
        }

        // 연동 기본값
        $linkValue = [
            'code' => $mode,
            'type' => 'order',
            'sno' => $orderNo
        ];

        // 연동 데이터 확인 및 저장
        $linkData = $this->getLink($linkValue);
        if (empty($linkData)) {
            $result = $this->saveLink($linkValue);
            if (!$result) {
                return false;
            }
        }

        // 주문 상품 리스트
        $orderGoodsData = $this->getOrderGoodsData($orderNo);

        // 주문 상태 리스트
        $orderStatusList = $this->getOrderStatusList();

        // 주문 배송정보
        $orderInfo = $this->getOrderInfo($orderNo);

        // 리브레 정보 api 연동
        $firstOrder = ($orderData['firstSaleFl'] == 'y') ? true : false;
        $multiOrder = ($orderData['multiShippingFl'] == 'y') ? true : false;

        $apiValue = null;

        $apiValue['Order_Serial_Number__c'] = $orderData['orderNo'];
        $apiValue['Adela_Consumer_ID__c'] = $memberInfo['memNo'];
        $apiValue['Webshop_KR_Consumer__r']['Adela_Consumer_ID__c'] = $memberInfo['memNo'];
        $apiValue['Email_Address__c'] = $memberInfo['email'];
        $apiValue['Order_Status__c'] = $orderStatusList[$orderData['orderStatus']];
        $apiValue['Order_Email_Address__c'] = $orderData['orderEmail'];
        $apiValue['Product_Name__c'] = $orderData['orderGoodsNm'];
        $apiValue['Product_Quantity__c'] = $orderGoodsData[0]['goodsCnt'];
        $apiValue['Product_Cost__c'] = $orderGoodsData[0]['goodsPrice'];
        $apiValue['Total_Order_Cost__c'] = $orderData['settlePrice'];
        $apiValue['First_Order_Flag__c'] = $firstOrder;
        $apiValue['Multi_Order_Flag__c'] = $multiOrder;
        $apiValue['Order_Date__c'] = $this->getDateConvert($orderData['regDt'], 'short');
        $apiValue['Ship_Name__c'] = $orderInfo['receiverName'];
        $apiValue['Ship_Phone__c'] = $orderInfo['receiverCellPhone'];
        $apiValue['Ship_Mobile__c'] = $orderInfo['receiverCellPhone'];
        $apiValue['Ship_Zipcode__c'] = $orderInfo['receiverZipcode'];
        $apiValue['Ship_Zone_Code__c'] = $orderInfo['receiverZonecode'];
        $apiValue['Ship_Address_Line_1__c'] = $memberInfo['receiverAddress'];
        $apiValue['Ship_Address_Line_2__c'] = $memberInfo['receiverAddressSub'];
        $apiValue['Order_Created_Date__c'] = $this->getDateConvert($orderData['regDt'], 'tz');
        $apiValue['Order_Modified_Date__c'] = $this->getDateConvert($orderData['modDt'], 'tz');
        $apiValue['CurrencyISOCode'] = 'KRW';

        if ($mode == 'register') {
            $apiValue['Order_ID__c'] = $orderData['orderNo'];
        }

        if (count($orderGoodsData) > 1) {
            $apiValue['Product_Quantity__c'] = $orderData['orderGoodsCnt']; // 복수 구매 시 총 개수로 설정
            $apiValue['Product_Cost__c'] = $orderData['settlePrice']; // 복수 구매 시 총 주문 가격으로 설정
        }

        $linkValue['data'] = $apiValue;

        $result1 = $this->setApi($linkValue);

        $result2 = false;
        if (count($orderGoodsData) > 0) {

            $cnt = 0;
            foreach ($orderGoodsData as $val) {
                $apiValue = null;
                $apiValue['Webshop_KR_Order__r']['Order_ID__c'] = $val['orderNo'];
                $apiValue['Order_Line_Item_ID__c'] = $val['sno'];
                $apiValue['Order_ID__c'] = $val['orderNo'];
                $apiValue['Order_Line_Serial_Number__c'] = $val['orderCd'];
                $apiValue['Order_Line_SKU__c'] = $val['goodsNo'];
                $apiValue['Order_Line_Product__c'] = $val['goodsNm'];
                $apiValue['Order_Line_Quantity__c'] = $val['goodsCnt'];
                $apiValue['Order_Line_Unit_Price__c'] = $val['goodsPrice'];
                $apiValue['Order_Status__c'] = $orderStatusList[$val['orderStatus']];
                $apiValue['Order_Line_Created_Date__c'] = $this->getDateConvert($val['regDt'], 'tz');
                $apiValue['Order_Line_Modified_Date__c'] = $this->getDateConvert($val['modDt'], 'tz');
                $apiValue['CurrencyISOCode'] = 'KRW';

                $linkValue = [
                    'code' => $mode,
                    'type' => 'orderGoods',
                    'sno' => $orderNo,
                    'data' => $apiValue
                ];

                $res = $this->setApi($linkValue);
                if ($res) {
                    $cnt++;
                }
            }

            if ($cnt == count($orderGoodsData)) {
                $result2 = true;
            }

        } else {
            $result2 = true;
        }

        if ($result1 && $result2) {
            return true;
        }

        return false;
    }

    // after function end ----------------------------------------------------------------------------------------------------

    // link function start ----------------------------------------------------------------------------------------------------

    // 연동 데이터 조회
    public function getLink($linkValue)
    {
        if (empty($linkValue)) {
            return null;
        }

        return $this->db->fetch("select * from wm_salesforce where linkType='{$linkValue['type']}' and linkSno='{$linkValue['sno']}'");
    }

    // 연동 데이터 저장
    public function saveLink($linkValue, $linkFl = 'n')
    {
        if (empty($linkValue)) {
            return false;
        }

        $arrValue = null;

        if ($linkValue['code'] == 'register') {
            $arrValue[] = "linkType='{$linkValue['type']}'";
            $arrValue[] = "linkSno='{$linkValue['sno']}'";
            $arrValue[] = "linkFl='{$linkFl}'";
            $arrValue[] = "writerType='{$this->writerType}'";
            $arrValue[] = "writerNo='{$this->writerNo}'";
            $arrValue[] = "writerIp='{$this->writerIp}'";
            $sql = "insert into";
        } else {
            $arrValue[] = "linkFl='{$linkFl}'";
            $sql = "update";
        }

        $sql .= " wm_salesforce set " . implode(',', $arrValue);

        if ($linkValue['code'] == 'update') {
            $sql .= " where linkType='{$linkValue['type']}' and linkSno='{$linkValue['sno']}'";
        }

        return $this->db->query($sql);
    }

    // link function end ----------------------------------------------------------------------------------------------------

    // fail function start ----------------------------------------------------------------------------------------------------

    // 실패 데이터 조회
    public function getFail($linkValue)
    {

        if (empty($linkValue)) {
            return null;
        }

        $data = $this->db->query_fetch("select * from wm_salesforceFail where linkCode='{$linkValue['code']}' and linkType='{$linkValue['type']}' order by sno asc");
        if (empty($data)) {
            return null;
        }

        foreach ($data as $key => $val) {
            $data[$key]['apiValue'] = json_decode($val['apiValue'], true);
        }

        return $data;
    }

    // 실패 데이터 저장
    public function saveFail($linkValue)
    {
        if (empty($linkValue)) {
            return null;
        }

        $arrValue = null;
        $arrValue[] = "linkCode='{$linkValue['code']}'";
        $arrValue[] = "linkType='{$linkValue['type']}'";
        $arrValue[] = "linkSno='{$linkValue['sno']}'";
        $arrValue[] = "apiValue='" . json_encode($linkValue['data'], JSON_UNESCAPED_UNICODE) . "'";

        if (!empty($linkValue['response'])) {
            $arrValue[] = "linkResponse='" . json_encode($linkValue['response'], JSON_UNESCAPED_UNICODE) . "'";
        }

        return $this->db->query("insert into wm_salesforceFail set " . implode(',', $arrValue));
    }

    // 실패 데이터 삭제
    public function deleteFail($linkValue)
    {
        if (empty($linkValue)) {
            return false;
        }

        $arrWhere = null;
        $arrWhere[] = "linkCode='{$linkValue['code']}'";
        $arrWhere[] = "linkType='{$linkValue['type']}'";
        $arrWhere[] = "linkSno='{$linkValue['sno']}'";

        return $this->db->query("delete from wm_salesforceFail where " . implode(' and ', $arrWhere));
    }

    // fail function end ----------------------------------------------------------------------------------------------------

    // cron function start ----------------------------------------------------------------------------------------------------

    // cron 호출
    public function cron()
    {
        // 크론 기록
        $this->saveLog(null, 'cron', 'start');
        sleep(2);

        // 리브레
        $this->resend(['code' => 'register', 'type' => 'libre']);
        sleep(2);

        // 등록
        $this->resend(['code' => 'register', 'type' => 'member']);
        sleep(2);
        $this->resend(['code' => 'register', 'type' => 'order']);
        sleep(2);
        $this->resend(['code' => 'register', 'type' => 'orderGoods']);
        sleep(2);

        // 갱신
        $this->resend(['code' => 'modify', 'type' => 'member']);
        sleep(2);
        $this->resend(['code' => 'modify', 'type' => 'order']);
        sleep(2);
        $this->resend(['code' => 'modify', 'type' => 'orderGoods']);
        sleep(2);

        // 크론 기록
        $this->saveLog(null, 'cron', 'end');
    }

    // 실패건 재전송
    public function resend($linkValue)
    {
        if (empty($linkValue)) {
            return false;
        }

        $data = $this->getFail($linkValue);

        if (empty($data)) {
            return false;
        }

        $arrData = null;
        foreach ($data as $val) {

            $params = [
                'code' => $val['linkCode'],
                'type' => $val['linkType'],
                'sno' => $val['linkSno'],
                'data' => $val['apiData']
            ];

            try {
                $result = $this->setApi($params, true);
                if ($result) {
                    $arrData[] = $val['linkSno'];
                } else {
                    // 재전송 실패건 기록(개별)
                    $this->saveLog($params, 'resend', 'fail');
                }
            } catch (\Exception $e) {
                //
            }
        }

        // 재전송 성공건 기록(종합)
        if (!empty($arrData)) {
            $this->saveLog($linkValue, 'resend', 'success : ' . implode(',', $arrData));
        }
    }

    // cron function end ----------------------------------------------------------------------------------------------------
}
// 웹앤모바일 세일즈포스 데이터 연동 ================================================== END