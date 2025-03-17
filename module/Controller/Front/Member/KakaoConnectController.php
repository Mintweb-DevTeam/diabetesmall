<?php

namespace Controller\Front\Member;

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

class KakaoConnectController extends \Controller\Front\Controller
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
        $memberSnsService->connectSns($in['memNo'], $userInfo['id'], $in['accessToken'], 'kakao');
        $memberSnsService->saveToken($userInfo['id'], $in['accessToken'], $in['refresh_token']);
        $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', 'kakao');
        $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', $in['access_token']);
        $session->set(Member::SESSION_MEMBER_LOGIN . '.snsJoinFl', 'n');
        $session->set(Member::SESSION_MEMBER_LOGIN . '.connectFl', 'y');
        $js = "alert('" . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . "');location.href='../member/login.php';";
        $this->js($js);

    }
}