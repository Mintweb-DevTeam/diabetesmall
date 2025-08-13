<?php

namespace Controller\Mobile\Member;

use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Godo\GodoKakaoServerApi;
use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\Member\Member;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Component\Member\MemberSnsDAO;

class KakaoQrJoinController extends \Controller\Mobile\Controller
{
    public function index(){
        $in = \Request::request()->all();

        //사용자 정보
        // 25-03-21 리브레 멤버쉽 회원가입
        $member_url = "https://kapi.kakao.com/v2/user/service_terms"; // 24-01-22 수신여부판단 v2 바뀌면서 수정
        $accessToken = $in['wm_access_token'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $member_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        $end = curl_exec($ch);
        curl_close($ch);
        $end = json_decode($end, true);


        $cossia = new \Component\Cossia\Cossia;

        //25-03-13 웹앤모바일 튜닝
        $privateApprovalOptionFl = [
            "19" => "n",
            "21" => "n"
        ];

        $privateOfferFl = [
            "5" => "n",
            "22" => "n",
            "25" => "n",
            "26" => "n"
        ];

        $privateConsignFl = [
            "20" => "n",
            "23" => "n"
        ];

        $termMap = [
            'private_info' => ['category' => 'privateApprovalOptionFl', 'id' => '19'],
            'user_info' => ['category' => 'privateApprovalOptionFl', 'id' => '21'],
            'private_agreement' => ['category' => 'privateOfferFl', 'id' => '5'],
            'user_agreement' => ['category' => 'privateOfferFl', 'id' => '22'],
            'user_select' => ['category' => 'privateOfferFl', 'id' => '25'],
            'private_select' => ['category' => 'privateOfferFl', 'id' => '26'],
            'marketing_opt' => ['category' => 'privateConsignFl', 'id' => '20'],
            'marketing_agreement' => ['category' => 'privateConsignFl', 'id' => '23']
        ];

        foreach ($end['service_terms'] as $v) {
            $tag = $v['tag'];
            $agreed = isset($v['agreed']) && $v['agreed'] ? 'y' : 'n';

            if (isset($termMap[$tag])) {
                $category = $termMap[$tag]['category'];
                $id = $termMap[$tag]['id'];

                // 동적 변수에 저장
                if ($category === 'privateApprovalOptionFl') {
                    $privateApprovalOptionFl[$id] = $agreed;
                } elseif ($category === 'privateOfferFl') {
                    $privateOfferFl[$id] = $agreed;
                } elseif ($category === 'privateConsignFl') {
                    $privateConsignFl[$id] = $agreed;
                }
            }
        }
        

        // JSON 변환
        $param['privateApprovalOptionFl'] = $privateApprovalOptionFl;
        $param['privateOfferFl'] = $privateOfferFl;
        $param['privateConsignFl'] = $privateConsignFl;
        $param['cellPhone'] = $in['cellPhone'];
        $param['memId'] = $in['email'];
        $param['memNm'] = $in['memNm'];
        $param['device'] = 'qr';
        $param['pharmacy_code'] = $in['pharmacy_code'];
        $param['cellPhone'] = $cossia->getCellPhone($param['cellPhone']);
        if ($param['cellPhone'] === false) {
            echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
            exit;
        }
        $sno = $cossia->insertCoAbbottMemberKakao($param);
        // 25-03-21 리브레 멤버쉽 회원가입 끝

        $this->redirect("../../qrcode/co_join_stepe.php?sno=" . $sno . "&memNm=" .$in['memNm'] , null, 'top');


    }
}