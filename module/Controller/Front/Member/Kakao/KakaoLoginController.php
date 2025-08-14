<?php

namespace Controller\Front\Member\Kakao;

use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Godo\GodoKakaoServerApi;
use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\Member\Member;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Component\Member\MemberSnsDAO;
use Request;
use Framework\Debug\Exception\AlertRedirectCloseException;


/**
 * Class KakaoLoginController
 * @package Bundle\Controller\Mobile\Member\Kakao
 */
class KakaoLoginController extends \Bundle\Controller\Front\Member\Kakao\KakaoLoginController
{
    protected $client_id = 'e0ef5095dc965dbe3817dc446688d58e'; // REST KEY 쇼핑몰 마다 수정 바람

    public function index()
    {

        $request = \App::getInstance('request');
        $code = $request->get()->get('code');
        $memId = $request->get()->get('memId');
        $autologin = $request->get()->get('saveAutoLogin');
        $session = \App::getInstance('session');
        $returnUrl1 = $request->get()->get('returnUrl');
        $no = \Cookie::get('no');
        $response = '';
        $state1 = $request->get()->get('state');
        $db = \App::load('DB');
        $state1 = explode('^|^' , $state1);

        if($no && !$code && $autologin == 'y'){
            $snsno = \App::load("\\Component\\Wm\\Wm");
            $end = $snsno->getToken($no);
            $memId = $end;
        }

        if ((!$returnUrl1 && ($state1[sizeof($state1) - 1] == "n" || $state1[sizeof($state1) - 1] == "y")) && !$memId) { // 자동로그인 여부 검사
            // 카카오에서 받은 토큰을 REST KEY 와 redirect_uti와 함께 보냄
            // 웹앤모바일 수정 21-09-23 - $redirect_uri의 http, https 유동적으로 설정하도록 수정
            $redirect_uri = $request->getScheme().'://'.'diabetesmall.co.kr/member/kakao/kakao_login.php'; // redirect_uri 쇼핑몰 마다 수정 바람
            $token_request = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id={$this->client_id}&redirect_uri={$redirect_uri}&code={$code}";

            $isPost = false;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $token_request);
            curl_setopt($ch, CURLOPT_POST, $isPost);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $loginResponse = curl_exec($ch);
            curl_close($ch);
            $loginToken = json_decode($loginResponse, true);

            $member_url = "https://kapi.kakao.com/v2/user/me";
            $accessToken = json_decode($loginResponse)->access_token; //Access Token만 따로 뺌
            // 웹앤모바일 추가 21-10-21 - 동일한 이메일 회원이 있는 경우 예외 처리
            $refresh_token = json_decode($loginResponse)->refresh_token;
            // 회원정보 받아오기
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $member_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, $isPost);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            $response1 = curl_exec($ch);
            curl_close($ch);
            // 회원 정보
            $response = json_decode($response1, true);
        }
        if ($memId) $response['id'] = $request->get()->get('uuid');

        $logger = \App::getInstance('logger');
        $logger->info(sprintf('start controller: %s', __METHOD__));

        $in = \Request::Request()->all();

        $kakaoApi = new GodoKakaoServerApi();
        $memberSnsService = new MemberSnsService();

        $functionName = 'popup';
        if(gd_is_skin_division()) {
            $functionName = 'gd_popup';
        }


        // 마이페이지 회원 정보 수정 검증
        if($in['mode'] == 'mypage') {

            $kakaoType = $in['KakaoType'];
            $returnURLFromAuth = $in['returnURLFromAuth'];
            //사용자 정보
            $userInfo = $kakaoApi->getUserInfo($in['wm_access_token']['access_token']);
            // 세션에 사용자 정보 저장
            $session->set(GodoKakaoServerApi::SESSION_ACCESS_TOKEN, $in['wm_access_token']);
            $memberSns = $memberSnsService->getMemberSnsByUUID($userInfo['id']);
            // kakao 아이디로 회원가입한 회원인지 검증
            if ($memberSnsService->validateMemberSns($memberSns)) {
                $logger->channel('kakaoLogin')->info('pass validationMemberSns');

                $session->set(GodoKakaoServerApi::SESSION_KAKAO_HACK, true);
                if ($session->has(Member::SESSION_MEMBER_LOGIN)) {
                    // 마이페이지 회원정보 수정시 인증
                    if ($kakaoType == 'my_page_password') {
                        $memberSnsService->saveToken($userInfo['id'], $in['wm_access_token']['access_token'], $in['wm_access_token']['refresh_token']);
                        $logger->channel('kakaoLogin')->info('move my page');
                        $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                        $js = "
                                 if (typeof(window.top.layerSearchArea) == \"object\") {
                                        parent.location.href='../../mypage/my_page.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($returnURLFromAuth, '../../mypage/my_page.php') . "';
                                    } else {
                                        opener.location.href='../../mypage/my_page.php';
                                        self.close();
                                    }
                            ";
                        $this->js($js);
                    }
                }
                $saveAutoLogin = 'y';
                // 카카오 로그인
                $memberSnsService->saveToken($userInfo['id'], $in['wm_access_token']['access_token'], $in['wm_access_token']['refresh_token']);
                $memberSnsService->loginBySns($userInfo['id']);
                if ($saveAutoLogin == 'y') {
                    $session->set(Member::SESSION_MYAPP_SNS_AUTO_LOGIN, 'y');
                    \Cookie::set('no', \Encryptor::encrypt($memberSns['memNo']), (3600 * 24 * 10), '/', true, true);
                }
                $logger->channel('kakaoLogin')->info('success login by kakao');
            }

            //마이페이지 회원 인증 다를경우
            if($kakaoType == 'my_page_password'){
                //현재 받은 세션값으로 로그아웃 시키기
                \Logger::channel('kakaoLogin')->info('different inform', $session->get(GodoKakaoServerApi::SESSION_USER_PROFILE));
                $js = "alert('" . __('로그인 시 인증한 정보와 다릅니다 .') . "');
                    if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                        location.href='../../mypage/my_page_password.php';
                    } else {
                        opener.location.href='../../mypage/my_page_password.php';
                        self.close();
                    }
                ";
                $this->js($js);
            }
        }


        if ($response) {
            //탈퇴를 확인하기 위한 모듈 로드
            $hackOut = \App::load('\\Component\\Member\\HackOut\\HackOutServiceSec');

            if($memId){
                $result = $hackOut->checkRejoinByMemberId($memId);
            } else {
                $result = $hackOut->checkRejoinByMemberId($response['kakao_account']['email']);
            }

            if ($result['isFl'] == 'n') { //탈퇴한 경우
                // 웹앤모바일 수정 21-10-06
                $this->js("alert('".$result['message']."'); location.href = '../../member/login.php'");
            }
            $kakaoType = null;
            try {
                $functionName = 'popup';
                if (gd_is_skin_division()) {
                    $functionName = 'gd_popup';
                }

                $kakaoApi = new GodoKakaoServerApi();
                $memberSnsService = new MemberSnsService();
                $state = $request->get()->get('state');
                //state 값을 이용해 분기처리
                $kakaoType = $state['kakaoType'];
                // saveAutologin
                $saveAutoLogin = $state['saveAutoLogin'];

                //카카오계정 로그인 팝업창에서 동의안함 클릭시 팝업창 닫힘 처리
                if ($request->get()->get('error') == 'access_denied') {
                    $logger->channel('kakaoLogin')->info($request->get()->get('error_description'));
                    $js = "
                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                    if('" . $kakaoType . "' == 'join_method'){
                        location.href='../../member/join_method.php';
                    }else{
                        location.href='../../mypage/my_page.php';
                    }
                } else {
                    opener.location.reload();
                    self.close();
                }";
                    $this->js($js);
                }

                if ($response) {


                    $memberSns = $memberSnsService->getMemberSnsByUUID($response['id']);


//                    $userInfo = $kakaoApi->getUserInfo($accessToken);
                    // 웹앤모바일 21-10-21
                    if(!empty($loginToken))
                        $session->set(GodoKakaoServerApi::SESSION_ACCESS_TOKEN, $loginToken);
                    $logger->channel('kakaoLogin')->info('pass validationMemberSns');

                    $wm_access_token = $accessToken;

                    if ($memberSnsService->validateMemberSns($memberSns)) {

                        // 카카오 로그인 부분

                        // 카카오 아이디 로그인 wm_access_token
                        $memberSnsService->saveToken($response['id'], $wm_access_token, $loginToken['refresh_token']);
                        $memberSnsService->loginBySns($response['id']);


                        if ($saveAutoLogin == 'y') {
                            $session->set(Member::SESSION_MYAPP_SNS_AUTO_LOGIN, 'y');
                            \Cookie::set('no', \Encryptor::encrypt($memberSns['memNo']), (3600 * 24 * 10), '/', true, true);
                        }

                        $logger->channel('kakaoLogin')->info('success login by kakao');

                        $db = \App::getInstance('DB');
                        try {
                            $db->begin_tran();
                            $check = new AttendanceCheckLogin();
                            
                            //24-08-29 출석체크 이벤트가 있을때 RuntimeException 오류로 화면이 멈추는 현상 수정
                            try {
                                $message = $check->attendanceLogin();
                            } catch (\RuntimeException $e) {
                                $logger->warning('Attendance login failed: ' . $e->getMessage());
                                // 예외가 발생해도 흐름이 중단되지 않도록 추가 로직 처리
                                // 예를 들어 특정 변수에 상태를 기록하거나 사용자에게 피드백을 제공
                                $message = 'Attendance check failed but continuing.';
                            }
                            //24-08-29 출석체크 이벤트가 있을때 RuntimeException 오류로 화면이 멈추는 현상 수정 끝
                            
                            $db->commit();

                            // 에이스 카운터 로그인 스크립트
                            $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                            $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                            if ($acecounterUse) {
                                echo $acecounterScript->getLoginScript();
                            }

                            $logger->info('commit attendance login');
                            if ($message) {
                                $logger->info(sprintf('has attendance message: %s', $message));

                            }

                            // 웹앤모바일 수정 21-10-21 - 회원가입시 returnUrl 추가 21-07-19
                            $url = ($returnUrl1) ? $returnUrl1 : urldecode($state1[0]);

                            if (strpos($url, 'member/kakao_connect.php')) {
                                $url = "../../main/index.php";
                            }

                            if(strpos($url, '../') === 0){
                                $url = "../" . $url;
                            }
                            if ($url == 'n' || $url == 'y' || $url == '')
                                $url = "../../main/index.php";
                            $this->redirect($url, null, parent);
                        } catch (Exception $e) {
                            $db->rollback();
                            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                        }
                    } else {
                        /* 웹앤모바일 추가 21-10-21 - 동일한 이메일 회원이 있는 경우 예외 처리 */
                        /**
                         * 해당 부분은 필요시에만 사용해주세요.
                         * 작업 내용에 "카카오싱크 가입시 동일한 이메일 회원이 있는 경우 예외 처리" 같은 내용이
                         * 포함되어 있는 경우에 사용하고, 그 외에는 해당 부분 소스를 지워주세요.
                         *
                         * 웹앤모바일 2021-10-21
                         */

                        $uuid = $response['id'];

                            if(empty(\Request::get()->get("memId"))){


                                \Session::set("accessToken",$accessToken);
                                \Session::set("refresh_token",$refresh_token);



                                $email = $response['kakao_account']['email'];
                                $cellPhone = $response['kakao_account']['phone_number'] ? str_replace("+82 ","0", $response['kakao_account']['phone_number']) : "";
                                $phone = str_replace('-','',$cellPhone);

                                if ($response['kakao_account']['gender'] == 'male'){
                                    $sexFl = 'm';
                                }else
                                    $sexFl = 'w';

                                $birthYear = $response['kakao_account']['birthyear'];
                                $birthMonth = substr($response['kakao_account']['birthday'], 0, 2);
                                $birthDay = substr($response['kakao_account']['birthday'], 2, 2);



                                if(!empty($phone)){

                                    $strSQL = "select * from co_abbottMember where cellPhone = '$phone' OR cellPhone = '$cellPhone'";
                                    $meberShip = $db->fetch($strSQL);

                                    $strSQL = "select memId,email,cellPhone from " . DB_MEMBER . " where cellPhone = '$phone' OR cellPhone = '$cellPhone'";
                                    $member = $db->fetch($strSQL);

                                    $memId = $response['kakao_account']['email'];
                                    $memNm = $response['kakao_account']['name'] ? $response['kakao_account']['name'] : 'user'.$uuid;

                                    // 카카오 계정이 없고 리브레 멤버쉽이 있는 경우
                                    if ( !empty($meberShip) && !$member['memId']) {

                                        ?>
                                        <form name="snsForm" method="post" action="../../member/join_kakao.php">
                                            <input type="hidden" name="wm_access_token" value="<?= $accessToken ?>">
                                            <input type="hidden" name="accessToken" value="<?= $accessToken ?>">
                                            <input type="hidden" name="refresh_token"
                                                   value="<?= $loginToken['refresh_token'] ?>">
                                            <input type="hidden" name="directKakao" value="1">
                                            <input type="hidden" name="rncheck" value="none">
                                            <input type="hidden" name="mode" value="join">
                                            <input type="hidden" name="memId" value="<?= $response['kakao_account']['email'] ?>">
                                            <input type="hidden" name="email" value="<?= $email ?>">
                                            <input type="hidden" name="cellPhone" value="<?= $cellPhone ?>">
                                            <input type="hidden" name="sexFl" value="<?= $sexFl ?>">
                                            <input type="hidden" name="birthYear" value="<?= $birthYear ?>">
                                            <input type="hidden" name="birthMonth" value="<?= $birthMonth ?>">
                                            <input type="hidden" name="birthDay" value="<?= $birthDay ?>">
                                            <input type="hidden" name="uuid" value="<?= $uuid ?>">
                                            <input type="hidden" name="memNm"
                                                   value="<?=$memNm?>">
                                            <input type="hidden" name="returnTo"
                                                   value="<?= !empty($returnUrl1) ? $returnUrl1 : urldecode($state1[0]) ?>">
                                        </form>
                                        <script>
                                            document.snsForm.submit();
                                        </script>


                                        <?php
                                        exit();
                                    }


                                    if (!empty($phone)){
                                        $strSQL = "select memId,email,cellPhone,memNo from " . DB_MEMBER . " where cellPhone = '$phone' OR cellPhone = '$cellPhone'";
                                        $mrow = $db->fetch($strSQL);

                                        $strSQL = "select * from co_abbottMember where cellPhone = '$phone' OR cellPhone = '$cellPhone'";
                                        $meberShip = $db->fetch($strSQL);
                                    }


                                    $memId = $response['kakao_account']['email'];
                                    $memNm = $response['kakao_account']['name'] ? $response['kakao_account']['name'] : 'user'.$uuid;

                                    if ( !empty($mrow) && !empty($meberShip) ) {

                                        ?>
                                        <form name="snsForm" method="post" action="../../member/sns_member.php">
                                            <input type="hidden" name="wm_access_token" value="<?= $accessToken ?>">
                                            <input type="hidden" name="accessToken" value="<?= $accessToken ?>">
                                            <input type="hidden" name="refresh_token"
                                                   value="<?= $loginToken['refresh_token'] ?>">
                                            <input type="hidden" name="directKakao" value="1">
                                            <input type="hidden" name="rncheck" value="none">
                                            <input type="hidden" name="mode" value="join">
                                            <input type="hidden" name="memId" value="<?= $response['kakao_account']['email'] ?>">
                                            <input type="hidden" name="email" value="<?= $email ?>">
                                            <input type="hidden" name="cellPhone" value="<?= $cellPhone ?>">
                                            <input type="hidden" name="sexFl" value="<?= $sexFl ?>">
                                            <input type="hidden" name="birthYear" value="<?= $birthYear ?>">
                                            <input type="hidden" name="birthMonth" value="<?= $birthMonth ?>">
                                            <input type="hidden" name="birthDay" value="<?= $birthDay ?>">
                                            <input type="hidden" name="uuid" value="<?= $uuid ?>">
                                            <input type="hidden" name="memNm"
                                                   value="<?=$memNm?>">
                                            <input type="hidden" name="memNo"
                                                   value="<?=$mrow['memNo']?>">
                                            <input type="hidden" name="returnTo"
                                                   value="<?= !empty($returnUrl1) ? $returnUrl1 : urldecode($state1[0]) ?>">
                                        </form>
                                        <script>
                                            document.snsForm.submit();
                                        </script>


                                        <?php
                                        exit();
                                    }
                                }
                            }


                        /* 웹앤모바일 추가 끝 */

                        /*웹앤모바일 20200311 튜닝 카카오 로그인시 바로 회원가입으로 보내버리기*/
                        // 회원가입해야할 경우 member_ps 쪽으로 회원정보 전송

                        // 25-03-21 리브레 멤버쉽 회원가입
                        $member_url = "https://kapi.kakao.com/v2/user/service_terms"; // 24-01-22 수신여부판단 v2 바뀌면서 수정
                        $accessToken = $wm_access_token;
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
                        $param['cellPhone'] = $response['kakao_account']['phone_number'] ? str_replace("+82 ","0", $response['kakao_account']['phone_number']) : "010-0000-0000";
                        $param['memId'] = $response['kakao_account']['email'] ? $response['kakao_account']['email'] : 'test'.$uuid."@kakao.com";
                        $param['memNm'] = $response['kakao_account']['name'] ? $response['kakao_account']['name'] : 'user'.$uuid;
                        if (!\Request::isMobileDevice()) {
                            $param['device'] = 'pc';
                        }

                        $param['cellPhone'] = $cossia->getCellPhone($param['cellPhone']);
                        if ($param['cellPhone'] === false) {
                            echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
                            exit;
                        }
                        $sno = $cossia->insertCoAbbottMemberKakao($param);
                        // 25-03-21 리브레 멤버쉽 회원가입 끝


                        $memId = $response['kakao_account']['email'] ? $response['kakao_account']['email'] : 'test'.$uuid."@kakao.com";
                        $memNm = $response['kakao_account']['name'] ? $response['kakao_account']['name'] : 'user'.$uuid;
//                        $directKakao = 1;
//                        $rncheck = 'none';
//                        $mode = 'join';
                        $email = $response['kakao_account']['email'] ? $response['kakao_account']['email'] : 'test'.$uuid."@kakao.com";
                        $cellPhone = $response['kakao_account']['phone_number'] ? str_replace("+82 ","0", $response['kakao_account']['phone_number']) : "010-0000-0000";
                        if ($response['kakao_account']['gender'] == 'male'){
                            $sexFl = 'm';
                        } else $sexFl = 'w';
                        $birthYear = $response['kakao_account']['birthyear'];
                        $birthMonth = substr($response['kakao_account']['birthday'], 0, 2);
                        $birthDay = substr($response['kakao_account']['birthday'], 2, 2);
                        // 웹앤모바일 21-10-21 - 회원 가입시 필요한 returnUrl 추가

                        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
                        $wm = new \Component\Wm\Wm();
                        $agreementSp = '';
                        if ($wm->agreementFl) {
                            $agreementSp = $request->get()->get('agreementSp');
                            if (empty($agreementSp)) {
                                $agreementSp = 'n'; // 회원가입 시 기본값 n
                            }
                        }
                        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END
                        ?>
                        <form name="snsForm" method="post" action="../../member/join_membership.php">
                            <input type="hidden" name="wm_access_token" value="<?= $accessToken ?>">
                            <input type="hidden" name="accessToken" value="<?= $accessToken ?>">
                            <input type="hidden" name="refresh_token"
                                   value="<?= $loginToken['refresh_token'] ?>">
                            <input type="hidden" name="directKakao" value="1">
                            <input type="hidden" name="rncheck" value="none">
                            <input type="hidden" name="mode" value="join">
                            <input type="hidden" name="memId" value="<?= $response['kakao_account']['email'] ?>">
                            <input type="hidden" name="email" value="<?= $email ?>">
                            <input type="hidden" name="cellPhone" value="<?= $cellPhone ?>">
                            <input type="hidden" name="sexFl" value="<?= $sexFl ?>">
                            <input type="hidden" name="birthYear" value="<?= $birthYear ?>">
                            <input type="hidden" name="birthMonth" value="<?= $birthMonth ?>">
                            <input type="hidden" name="birthDay" value="<?= $birthDay ?>">
                            <input type="hidden" name="uuid" value="<?= $uuid ?>">
                            <input type="hidden" name="memNm"
                                   value="<?=$memNm?>">
                            <input type="hidden" name="sno"
                                   value="<?=$sno?>">
                            <input type="hidden" name="name"
                                   value="<?=urlencode(\Encryptor::encrypt($memNm))?>">
                            <input type="hidden" name="returnTo"
                                   value="<?= !empty($returnUrl1) ? $returnUrl1 : urldecode($state1[0]) ?>">
                            <input type="hidden" name="agreementSp" value="<?= $agreementSp ?>">
                        </form>
                        <script>
                            document.snsForm.submit();
                        </script>


                        <?php
                        exit();

//                        $this->redirect("../member_ps.php?wm_access_token=".$accessToken."&directKakao=".$directKakao."&rncheck=".$rncheck."&mode=".$mode."&memId=".$memId."&memNm=".$memNm."&email=".$email."&cellPhone=".$cellPhone."&sexFl=".$sexFl."&birthYear=".$birthYear."&birthMonth=".$birthMonth."&birthDay=".$birthDay."&returnTo=".$state1[0]."&uuid=".$uuid, null, parent);
                    }
                    exit;
                }
                // 카카오 로그인 팝업을 띄우는 케이스
                $callbackUri = $request->getRequestUri();
                $state = array();
                if ($startLen = strpos($request->getRequestUri(), "?")) {
                    $requestUriArray = explode('&', substr($request->getRequestUri(), ($startLen + 1)));
                    //\Logger::channel('kakaoLogin')->info('requestUriArray 354 %s', json_encode($requestUriArray));

                    $kakaoTypeInRequestUri = $requestUriArray[0];
                    $kakaoTypeToState = explode('=', $kakaoTypeInRequestUri);
                    $state['kakaoType'] = $kakaoTypeToState[1];
                    //returnUrl이 여러 개 있을 경우
                    foreach ($requestUriArray as $key => $val) {
                        $isReturnUrl = strstr($val, 'returnUrl');
                        if ($isReturnUrl) {

                            // 기존에 explode 를 = 을 기준으로 작업되어 returnUrl에 있는 파라미터 들의 = 까지 구분되어 주소가 수정되는 이슈로 str_replace 로 변경
                            $returnUrlToState = str_replace('returnUrl=','',$val);
                            $state['returnUrl'] = $returnUrlToState;
                            //// 정상적인 returnUrl 인지 확인용 로그
                            //\Logger::channel('kakaoLogin')->info('returnUrlToState 359 %s', json_encode($returnUrlToState));
                            /* 웹앤모바일 수정 끝 */
                        }
                    }
                    $state['referer'] = $request->getReferer();
                    if ($request->get()->get('saveAutoLogin') == 'y') $state['saveAutoLogin'] = 'y';
                    $callbackUri = substr($request->getRequestUri(), 0, $startLen);
                }
                $redirectUri = $request->getDomainUrl() . $callbackUri;
                \Logger::channel('kakaoLogin')->info('Redirect URI is %s', $redirectUri);

                $getCodeURL = $kakaoApi->getCodeURL($redirectUri, $state);
                \Logger::channel('kakaoLogin')->info('Code URI is %s', $getCodeURL);
                $this->redirect($getCodeURL);
            } catch (AlertRedirectException $e) {
                $logger->error($e->getTraceAsString());
                MemberUtil::logout();
                throw $e;
            } catch (AlertRedirectCloseException $e) {
                $logger->error($e->getTraceAsString());
                throw $e;
            } catch (Exception $e) {
                $logger->error($e->getTraceAsString());
                if ($request->isMobile()) {
                    MemberUtil::logout();
                    throw new AlertRedirectException($e->getMessage(), $e->getCode(), $e, '../../member/login.php', 'parent');
                } else {
                    MemberUtil::logout();
                    throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                }
            }
        } else {


            $request = \App::getInstance('request');
            $session = \App::getInstance('session');
            $logger = \App::getInstance('logger');
            $logger->info(sprintf('start controller: %s', __METHOD__));

            $kakaoType=null;

            try {
                $functionName = 'popup';
                if(gd_is_skin_division()) {
                    $functionName = 'gd_popup';
                }

                $kakaoApi = new GodoKakaoServerApi();
                $memberSnsService = new MemberSnsService();

                //state 값 decode
                $state = json_decode($request->get()->get('state'), true);
                $logger->info(sprintf('state : %s', $request->get()->get('state')));
                $logger->info(sprintf('state code : %s', $request->get()->get('code')));

                //state 값을 이용해 분기처리
                $kakaoType= $state['kakaoType'];
                //returnUrl 추출
                $returnURLFromAuth = gd_isset(rawurldecode($state['returnUrl']), $request->get()->get('returnUrl'));
                // saveAutologin
                $saveAutoLogin = $state['saveAutoLogin'];

                //카카오계정 로그인 팝업창에서 동의안함 클릭시 팝업창 닫힘 처리
                if($request->get()->get('error') == 'access_denied'){
                    $logger->channel('kakaoLogin')->info($request->get()->get('error_description'));
                    $js=" 
               if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                    if('".$kakaoType."' == 'join_method'){
                        location.href='../../member/join_method.php';
                    }else{     
                        location.href='../../mypage/my_page.php';
                    }
                } else {
                    opener.location.reload();
                    self.close();
                }";
                    $this->js($js);
                }

                if($code = $request->get()->get('code')){
                    if($endlen = (strpos($request->getRequestUri(), '?'))){
                        $returnURL = $request->getDomainUrl() . substr($request->getRequestUri(), 0, $endlen);
                    }
                    // 토큰 정보
                    $properties = $kakaoApi->getToken($code, $returnURL);

                    //사용자 정보
                    $userInfo = $kakaoApi->getUserInfo($properties['access_token']);

                    //세션에 사용자 정보 저장
                    $session->set(GodoKakaoServerApi::SESSION_ACCESS_TOKEN, $properties);
                    //$session->set(GodoKakaoServerApi::SESSION_USER_PROFILE, $userInfo);

                    $memberSns = $memberSnsService->getMemberSnsByUUID($userInfo['id']);

                    // kakao 아이디로 회원가입한 회원인지 검증
                    if($memberSnsService->validateMemberSns($memberSns)) {
                        $logger->channel('kakaoLogin')->info('pass validationMemberSns');

                        if($session->has(Member::SESSION_MEMBER_LOGIN)){
                            //일반회원 카카오 아이디 연동 거부 처리
                            if($kakaoType == 'connect'){
                                $logger->channel('kakaoLogin')->info('Deny app link');
                                $js = "
                               alert('" . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . "');
                               if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                  location.href='../../mypage/my_page.php';
                               } else {
                                  self.close();
                               }
                             ";
                                $this->js($js);
                            }
                            //마이페이지 회원정보 수정시 인증 정보 다를때 처리
                            if ($memberSns['memNo'] != $session->get(Member::SESSION_MEMBER_LOGIN . '.memNo', 0)) {
                                $logger->info($session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN));
                                $logger->channel('kakaoLogin')->info('not eq memNo');
                                $js = "
                                            alert('" . __('로그인 시 인증한 정보와 다릅니다 .') . "');
                                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                location.href='../../mypage/my_page_password.php';
                                            } else {
                                                opener.location.href='../../mypage/my_page_password.php';
                                                self.close();
                                            }
                                        ";
                                $this->js($js);
                            }

                            //마이페이지 회원정보 수정시 인증
                            if($kakaoType == 'my_page_password'){
                                $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                                $logger->channel('kakaoLogin')->info('move my page');
                                $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                                $js="
                                 if (typeof(window.top.layerSearchArea) == \"object\") {
                                        parent.location.href='../../mypage/my_page.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($returnURLFromAuth, '../../mypage/my_page.php') . "';
                                    } else {
                                        opener.location.href='../../mypage/my_page.php';
                                        self.close();
                                    }
                            ";
                                $this->js($js);
                            }

                            //회원탈퇴
                            if($kakaoType == 'hack_out') {
                                $logger->channel('kakaoLogin')->info('hack out kakao id');
                                $session->set(GodoKakaoServerApi::SESSION_KAKAO_HACK, true);
                                $js = "
                                   if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                       location.href='../../mypage/hack_out.php';
                                   } else {
                                       opener.location.href='../../mypage/hack_out.php';
                                       self.close();
                                   }
                                   ";
                                $this->js($js);
                            }

                            //일반회원 마이페이지 카카오 아이디 연동 해제
                            if($kakaoType == 'disconnect') {
                                if($memberSns['snsJoinFl'] == 'y'){
                                    $logger->channel('kakaoLogin')->info('Impossible disconnect member joined by kakao');
                                    $js=" alert('" . __('카카오로 가입한 회원님은 연결을 해제 할 수 없습니다.') . "');";
                                    $this->js($js);
                                }
                                if ($session->has(GodoKakaoServerApi::SESSION_ACCESS_TOKEN)) {
                                    $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                                    $kakaoApi->unlink($kakaoToken['access_token']);
                                    $session->del(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                                    $memberSnsService = new MemberSnsService();
                                    $memberSnsService->disconnectSns($memberSns['memNo']);
                                    $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', '');
                                    $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', '');
                                    $session->set(Member::SESSION_MEMBER_LOGIN . '.snsJoinFl', '');
                                    $session->set(Member::SESSION_MEMBER_LOGIN . '.connectFl', '');
                                    $js = "
                                alert('" . __('카카오 연결이 해제되었습니다.') . "');
                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                    location.href='../../mypage/my_page.php';
                                } else {
                                    opener.location.href='../../mypage/my_page.php';
                                    self.close();
                                }
                            ";
                                    $this->js($js);
                                }
                            }
                        }
                        if (isset($memberSns['accessToken'])) {
                            $logger->info('isset accessToken');
                            $kakaoApi->logout($memberSns['accessToken']);
                            $logger->info('success logout');
                        }
                        // 카카오 아이디 로그인
                        $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                        $memberSnsService->loginBySns($userInfo['id']);
                        if ($saveAutoLogin == 'y') $session->set(Member::SESSION_MYAPP_SNS_AUTO_LOGIN, 'y');
                        $logger->channel('kakaoLogin')->info('success login by kakao');

                        $db = \App::getInstance('DB');
                        try {
                            $db->begin_tran();
                            $check = new AttendanceCheckLogin();
                            $message = $check->attendanceLogin();
                            $db->commit();

                            // 에이스 카운터 로그인 스크립트
                            $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                            $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                            if ($acecounterUse) {
                                echo $acecounterScript->getLoginScript();
                            }

                            $logger->info('commit attendance login');
                            if ($message) {
                                $logger->info(sprintf('has attendance message: %s', $message));
                                $js = "
                                    alert('" . $message . "');
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='" . $returnURLFromAuth . "';
                                    } else if (window.opener === null) {
                                        location.href='" . $returnURLFromAuth . "';
                                    } else {
                                        opener.location.href='" . $returnURLFromAuth . "';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                        } catch (Exception $e) {
                            $db->rollback();
                            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                        }

                        if ($kakaoType == 'join_method') {
                            $logger->channel('kakaoLogin')->info('already join member');
                            $js = "
                                alert('" . __('이미 가입한 회원입니다.') . "');
                                if (typeof(window.top.layerSearchArea) == 'object') {
                                    parent.location.href='" . $returnURLFromAuth . "';
                                } else if (window.opener === null) {
                                    location.href='" . $returnURLFromAuth . "';
                                } else {
                                    opener.location.href='" . $returnURLFromAuth . "';
                                    self.close();
                                }
                            ";
                            $this->js($js);
                        }
                        $logger->channel('kakaoLogin')->info('move return url');
                        $loginReturnUrl = $returnURLFromAuth;
                        if ($request->isMyapp() && $request->get()->get('saveAutoLogin') == 'y') {
                            $loginReturnUrl .= '?saveAutoLogin=' . $request->get()->get('saveAutoLogin');
                        }
                        $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.href='" . $loginReturnUrl . "';
                            } else if (window.opener === null) {
                                location.href='" . $loginReturnUrl . "';
                            } else {
                                opener.location.href='" . $loginReturnUrl . "';
                                self.close();
                            }
                        ";
                        $this->js($js);
                    }

                    // 일반회원 카카오 아이디 연동 처리
                    if($kakaoType == 'connect') {
                        $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                        $kakaoApi->appLink($kakaoToken['access_token']);

                        $cossia = new \Component\Cossia\Cossia;
                        $param['cellPhone'] = $cossia->getCellPhone(str_replace("+82 ","0", $userInfo['kakao_account']['phone_number']));

                        if ($param['cellPhone'] === false) {
                            echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
                            exit;
                        }

                        $memberSnsService->connectSns($session->get(Member::SESSION_MEMBER_LOGIN . '.memNo'), $userInfo['id'], $properties['access_token'], 'kakao');
                        $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                        $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', 'kakao');
                        $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', $properties['access_token']);
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

                        $js = "
                                alert('" . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . "');
                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                    location.href='../../mypage/my_page.php';
                                } else {
                                    opener.location.href='../../mypage/my_page.php';
                                    self.close();
                                }
                            ";
                        $this->js($js);
                    }

                    if ($kakaoType == 'join_method') {
                        $logger->channel('kakaoLogin')->info('kakao id applink success');
                        $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.href='../join_agreement.php';
                            } else if (window.opener === null) {
                                location.href='../join_agreement.php';
                            } else {
                                opener.location.href='../join_agreement.php';self.close();
                            }
                        ";
                        $this->js($js);
                    }
                    //마이페이지 회원 인증 다를경우
                    if($kakaoType == 'my_page_password'){
                        //현재 받은 세션값으로 로그아웃 시키기
                        \Logger::channel('kakaoLogin')->info('different inform', $session->get(GodoKakaoServerApi::SESSION_USER_PROFILE));
                        $js = "
                                            alert('" . __('로그인 시 인증한 정보와 다릅니다 .') . "');
                                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                location.href='../../mypage/my_page_password.php';
                                            } else {
                                                opener.location.href='../../mypage/my_page_password.php';
                                                self.close();
                                            }
                                        ";
                        $this->js($js);
                    }
                    $js = "
                    if (typeof(window.top.layerSearchArea) == 'object') {
                            if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                parent.location.href = '../join_agreement.php';
                            } else {
                                parent.location.reload();
                            }
                        } else if (window.opener === null) {
                            if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                location.href = '../join_agreement.php';
                            } else {
                                location.href='" . gd_isset($returnURLFromAuth, '../../main/index.php') . "';
                            }
                        } else {
                           if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                opener.location.href = '../join_agreement.php';
                                self.close();
                           }else {
                                opener.location.href = '" . gd_isset($returnURLFromAuth, '../../main/index.php') . "';
                                self.close();
                           }
                        }
                        ;";
                    $this->js($js);

                }
                // 카카오 로그인 팝업을 띄우는 케이스
                $callbackUri = $request->getRequestUri();
                \Logger::channel('kakaoLogin')->info('callbackUri 350 %s', $callbackUri);

                $state = array();
                if ($startLen = strpos($request->getRequestUri(), "?")) {
                    $requestUriArray = explode('&', substr($request->getRequestUri(), ($startLen + 1)));
                    \Logger::channel('kakaoLogin')->info('requestUriArray 354 %s', json_encode($requestUriArray));

                    $kakaoTypeInRequestUri = $requestUriArray[0];
                    $kakaoTypeToState= explode('=', $kakaoTypeInRequestUri);
                    $state['kakaoType'] = $kakaoTypeToState[1];
                    //returnUrl이 여러 개 있을 경우
                    foreach ($requestUriArray as $key => $val) {
                        $isReturnUrl = strstr($val, 'returnUrl');
                        if ($isReturnUrl) {
                            // 기존에 explode 를 = 을 기준으로 작업되어 returnUrl에 있는 파라미터 들의 = 까지 구분되어 주소가 수정되는 이슈로 str_replace 로 변경
                            $returnUrlToState = str_replace('returnUrl=','',$val);
                            $state['returnUrl'] = $returnUrlToState;
                            // 정상적인 returnUrl 인지 확인용 로그
                            \Logger::channel('kakaoLogin')->info('returnUrlToState 359 %s', json_encode($returnUrlToState));
                        }
                    }
                    $state['referer'] = $request->getReferer();
                    if ($request->get()->get('saveAutoLogin') == 'y') $state['saveAutoLogin'] = 'y';
                    $callbackUri = substr($request->getRequestUri(), 0, $startLen);
                }
                $redirectUri = $request->getDomainUrl() . $callbackUri;
                \Logger::channel('kakaoLogin')->info('Redirect URI is %s', $redirectUri);

                $getCodeURL = $kakaoApi->getCodeURL($redirectUri, $state);
                \Logger::channel('kakaoLogin')->info('Code URI is %s', $getCodeURL);
                $this->redirect($getCodeURL);
            } catch (AlertRedirectException $e) {
                $logger->error($e->getTraceAsString());
                MemberUtil::logout();
                throw $e;
            } catch (AlertRedirectCloseException $e) {
                $logger->error($e->getTraceAsString());
                throw $e;
            } catch (Exception $e) {
                $logger->error($e->getTraceAsString());
                if ($request->isMobile()) {
                    MemberUtil::logout();
                    throw new AlertRedirectException($e->getMessage(), $e->getCode(), $e, '../../member/login.php', 'parent');
                } else {
                    MemberUtil::logout();
                    throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                }
            }


        }
    }
}