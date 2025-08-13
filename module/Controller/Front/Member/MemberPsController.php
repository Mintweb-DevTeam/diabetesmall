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
namespace Controller\Front\Member;

use Bundle\Component\Godo\GodoKakaoServerApi;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\MemberSnsService;
use Component\Member\MemberValidation;
use Component\Member\Util\MemberUtil;
use Component\Policy\SnsLoginPolicy;
use Component\SiteLink\SiteLink;
use Component\Storage\Storage;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\PlusShop\PlusShopWrapper;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

use App;
use Component\Coupon\Coupon;
use Framework\Debug\Exception\DatabaseException;
use Framework\Object\SimpleStorage;
use Request;
use Session;
use Validation;
use Component\Member\Member;

/**
 * Class MemberPsController
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class MemberPsController extends \Bundle\Controller\Front\Member\MemberPsController
{
    public function index()
    {
        $in = \Request::request()->all();

        if($in['directKakao']) {
            $session = \App::getInstance('session');
            $request = \App::getInstance('request');
            $logger = \App::getInstance('logger');

            try {
                /** @var  \Bundle\Component\Member\Member $member */
                $member = \App::load('\\Component\\Member\\Member');

                $memberVO = null;
                try {
                    if ($session->has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
                        \Component\Member\Util\MemberUtil::saveJoinInfoBySession($request->post()->all());
                    }
                    $memberSnsService = \App::load('Component\\Member\\MemberSnsService');
                    \DB::begin_tran();
                    $in = \Request::request()->all();
                    if($in['directKakao']) {
                        $kakaoToken = \Session::get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                        $kakaoApi = new GodoKakaoServerApi();
                        $kakaoApi->appLink($kakaoToken['access_token']);
//                        $userInfo = $kakaoApi->getUserInfo($in['wm_access_token']['access_token']);
                        $userInfo = $kakaoApi->getUserInfo($in['wm_access_token']);
                        \Session::set(GodoKakaoServerApi::SESSION_USER_PROFILE, $userInfo);
                        $in['appFl'] ='y';
                        $in['rncheck'] = 'authCellphone';
                        // 웹앤모바일 추가 튜닝 카카오 디벨로퍼에서 email, sms 수신 여부 판단 후 회원가입에 반영
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


                        // 24-01-22 수신여부판단 v2 바뀌면서 수정
                        foreach($end['service_terms'] as $k => $v){
                            if($v['tag'] == 'maillingFl'){
                                if($v['agreed']){
                                    $in['maillingFl'] = 'y';
                                }
                            }
                            if($v['tag'] == 'smsFl'){
                                if($v['agreed']){
                                    $in['smsFl'] = 'y';
                                }
                            }

                        }
                        // end
                        

                        // 이메일 중복 여부 판단
                        $email = \App::load("\Component\Wm\KakaoJoin");
                        $result = $email->getEmail($in['email']);
                        if($result){
                            $in['email'] = $in['memId'] . '_@email.com';
                        }

                        if(empty($in['email'])) {
                            $in['email'] = $in['memId'] . '_@email.com';
                        }

                        if(empty($in['cellPhone'])) {
                            $in['cellPhone'] = '010-0000-0000';
                        }

                        if(empty($in['sexFl'])) {
                            $in['sexFl'] = 'm';
                        }

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
                        \Session::set(Member::SESSION_JOIN_INFO, $param);


                        $in['cellPhone'] = $cossia->getCellPhone($in['cellPhone']);
                        if ($in['cellPhone'] === false) {
                            echo '<script>parent.alert("전화번호를 다시 확인해주세요.");</script>';
                            exit;
                        }
                        $result = $cossia->abbottCheck($in['cellPhone']);
                        $sno = $result['sno'];
                        
                        
                        // 웹앤모바일 수정 21-09-23 - 배송지 정보를 받아서 회원정보의 주소로 입력
                        $shipping_url = 'https://kapi.kakao.com/v1/user/shipping_address';
                        $ch2 = curl_init();
                        curl_setopt($ch2, CURLOPT_URL, $shipping_url);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch2, CURLOPT_POST, false);
                        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
                        $shipAddr = curl_exec($ch2);
                        curl_close($ch2);
                        $shipAddr = json_decode($shipAddr, true);

                        if ($shipAddr['shipping_addresses'][0]) {
                            $in['zonecode'] = $shipAddr['shipping_addresses'][0]['zone_number'];
                            $in['address'] = $shipAddr['shipping_addresses'][0]['base_address'];
                            $in['addressSub'] = $shipAddr['shipping_addresses'][0]['detail_address'];
                        }



                        $memberVO = $member->join2($in);
                        $cossia->isJoinData($sno,$memberVO->getMemNo());
                
                    } else {
                        $memberVO = $member->join($request->post()->xss()->all());
                    }
                    $session->del('isFront');
                    if ($session->has(GodoPaycoServerApi::SESSION_USER_PROFILE)) {
                        $paycoToken = $session->get(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
                        $userProfile = $session->get(GodoPaycoServerApi::SESSION_USER_PROFILE);
                        $session->del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
                        $session->del(GodoPaycoServerApi::SESSION_USER_PROFILE);
                        $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['idNo'], $paycoToken['access_token'], 'payco');
                        $paycoApi = new GodoPaycoServerApi();
                        $paycoApi->logByJoin();
                    } elseif ($session->has(Facebook::SESSION_USER_PROFILE)) {
                        $userProfile = $session->get(Facebook::SESSION_USER_PROFILE);
                        $accessToken = $session->get(Facebook::SESSION_ACCESS_TOKEN);
                        $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['id'], $accessToken, SnsLoginPolicy::FACEBOOK);
                    } elseif ($session->has(GodoNaverServerApi::SESSION_USER_PROFILE)) {
                        $naverToken = $session->get(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
                        $userProfile = $session->get(GodoNaverServerApi::SESSION_USER_PROFILE);
                        $session->del(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
                        $session->del(GodoNaverServerApi::SESSION_USER_PROFILE);
                        $memberSnsService = new MemberSnsService();
                        $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['id'], $naverToken['access_token'], 'naver');
                        $naverApi = new GodoNaverServerApi();
                        $naverApi->logByJoin();
                    } elseif($session->has(GodoKakaoServerApi::SESSION_USER_PROFILE)) {
                        $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                        $kakaoProfile = $session->get(GodoKakaoServerApi::SESSION_USER_PROFILE);
                        $session->del(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                        $session->del(GodoKakaoServerApi::SESSION_USER_PROFILE);
                        $memberSnsService = new MemberSnsService();
                        $memberSnsService->joinBySns($memberVO->getMemNo(), $kakaoProfile['id'], $kakaoToken['access_token'], 'kakao');

                    } elseif ($session->has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
                        $wonderToken = $session->get(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
                        $userProfile = $session->get(GodoWonderServerApi::SESSION_USER_PROFILE);
                        $session->del(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
                        $session->del(GodoWonderServerApi::SESSION_USER_PROFILE);
                        $memberSnsService = new MemberSnsService();
                        $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['mid'], $wonderToken['access_token'], 'wonder');
                        $wonderApi = new GodoWonderServerApi();
                        $wonderApi->logByJoin();
                    }

                    \DB::commit();

                } catch (\Throwable $e) {
                    \DB::rollback();
                    throw $e;
                }
                if($session->get('ps_event')) {
                    PlusShopWrapper::event($session->get('ps_event'),['memNo'=>$memberVO->getMemNo()]);
                }
                if ($memberVO != null) {
                    $smsAutoConfig = ComponentUtils::getPolicy('sms.smsAuto');
                    $kakaoAutoConfig = ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
                    if ($kakaoAutoConfig['useFlag'] == 'y' && $kakaoAutoConfig['memberUseFlag'] == 'y') {
                        $smsDisapproval = $kakaoAutoConfig['member']['JOIN']['smsDisapproval'];
                    } else {
                        $smsDisapproval = $smsAutoConfig['member']['JOIN']['smsDisapproval'];
                    }
                    StringUtils::strIsSet($smsDisapproval, 'n');
                    $sendSmsJoin = ($memberVO->getAppFl() == 'n' && $smsDisapproval == 'y') || $memberVO->getAppFl() == 'y';
                    $mailAutoConfig = ComponentUtils::getPolicy('mail.configAuto');
                    $mailDisapproval = $mailAutoConfig['join']['join']['mailDisapproval'];
                    StringUtils::strIsSet($smsDisapproval, 'n');
                    $sendMailJoin = ($memberVO->getAppFl() == 'n' && $mailDisapproval == 'y') || $memberVO->getAppFl() == 'y';
                    if ($sendSmsJoin) {
                        /** @var \Bundle\Component\Sms\SmsAuto $smsAuto */
                        $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
                        $smsAuto->notify();
                    }
                    if ($sendMailJoin) {
                        $member->sendEmailByJoin($memberVO);
                    }

                    if($in['directKakao']) {
                        // 이 부분 수정 해야함
                        // 회원 가입이 완료되면 자동 로그인을 위해 로그인쪽으로 데이터 전송
                        // 웹앤모바일 수정 21-10-21 - 회원가입 후 returnUrl로 이동하도록 수정
//                        $this->redirect("./kakao/kakao_login.php?memId=".$in['memId'], null, parent);
                        // 웹앤모바일 수정 21-10-22 - 이메일 중복으로 재설정한 경우와 아닌 경우 구분
                        if (\Request::request()->get('emailCheck') == 'y') {
                            $this->redirect("./kakao/kakao_login.php?uuid=".$in['uuid']."&memId=".$in['memId']."&returnUrl=".\Request::post()->get('returnTo'), null, 'top');
                        } else {
                            $this->redirect("./kakao/kakao_login.php?uuid=".$in['uuid']."&memId=" . $in['memId'] . "&returnUrl=" . urlencode(\Request::get()->get('returnTo')), null, 'top');
                        }
//                        $this->js("alert('회원가입이 완료되었습니다.');window.location.href = './login.php';");
//                        echo 'join_ok';
                        exit;
                    }
                }

                $sitelink = new SiteLink();
                $returnUrl = $sitelink->link('../member/join_ok.php');
                if ($wonderToken && $userProfile && $session->has(GodoWonderServerApi::SESSION_PARENT_URI)) {
                    $returnUrl = $session->get(GodoWonderServerApi::SESSION_PARENT_URI) . '../member/wonder_join_ok.php';
                    $this->js('location.href=\'' . $returnUrl . '\';');
                } else {
                    $this->js('parent.location.href=\'' . $returnUrl . '\'');
                }
            } catch (AlertRedirectException $e) {
                throw $e;
            } catch (\Throwable $e) {
                if ($request->isAjax() === true) {
                    $logger->error($e->getTraceAsString());
                    $this->json($this->exceptionToArray($e));
                } else {
                    throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
                }
            }
        } else {
            parent::index();
        }
    }
}