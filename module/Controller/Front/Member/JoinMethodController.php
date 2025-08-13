<?php
namespace Controller\Front\Member;

use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\Util\MemberUtil;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

class JoinMethodController extends \Bundle\Controller\Front\Member\JoinMethodController
{
	public function index()
	{
		$request = \App::getInstance('request');
		$session = \App::getInstance('session');
		$session->del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
		$scripts = ['gd_payco.js'];
		$paycoPolicy = new PaycoLoginPolicy();
		$naverPolicy = new NaverLoginPolicy();
		$snsLoginPolicy = new SnsLoginPolicy();
		$kakaoLoginPolicy = new KakaoLoginPolicy();
		$wonderLoginPolicy = new WonderLoginPolicy();
		$usePaycoLogin = $paycoPolicy->usePaycoLogin();
		$useNaverLogin = $naverPolicy->useNaverLogin();
		$useFacebook = $snsLoginPolicy->useFacebook();
		$usekakaoLogin = $kakaoLoginPolicy->useKakaoLogin();
		$useWonderLogin = $wonderLoginPolicy->useWonderLogin();
		if ($usePaycoLogin === false && $useNaverLogin === false && $useFacebook === false  && $useWonderLogin === false) {
			$this->redirect('../member/co_join_stepa.php');
		}
		parent::index();
	}
}