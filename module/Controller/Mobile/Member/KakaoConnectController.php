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

class KakaoConnectController extends \Controller\Mobile\Controller
{
    public function index(){
        $in = \Request::request()->all();


        $session = \App::getInstance('session');
        $kakaoApi = new GodoKakaoServerApi();
        $memberSnsService = new MemberSnsService();

        //사용자 정보
        $userInfo = $kakaoApi->getUserInfo($in['accessToken']);

        $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
        $kakaoApi->appLink($kakaoToken['access_token']);

        $cossia = new \Component\Cossia\Cossia;
        $param['cellPhone'] = $cossia->getCellPhone(str_replace("+82 ","0", $userInfo['kakao_account']['phone_number']));

        if ($param['cellPhone'] === false) {
            echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
            exit;
        }

        $memberSnsService->connectSns($in['memNo'], $userInfo['id'], $in['accessToken'], 'kakao');
        $memberSnsService->saveToken($userInfo['id'], $in['accessToken'], $in['refresh_token']);
        $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', 'kakao');
        $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', $in['access_token']);
        $session->set(Member::SESSION_MEMBER_LOGIN . '.snsJoinFl', 'n');
        $session->set(Member::SESSION_MEMBER_LOGIN . '.connectFl', 'y');


        // 25-03-31 리브레 멤버쉽 회원가입
        $member_url = "https://kapi.kakao.com/v2/user/service_terms"; // 24-01-22 수신여부판단 v2 바뀌면서 수정
        $accessToken = $kakaoToken['access_token'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $member_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        $end = curl_exec($ch);
        curl_close($ch);
        $end = json_decode($end, true);


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

        $cossia->updateCoAbbottMemberKakao($param);

        // 25-03-31 리브레 멤버쉽 회원가입 끝


        $js = "alert('" . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . "');location.href='../member/login.php';";
        $this->js($js);

    }
}