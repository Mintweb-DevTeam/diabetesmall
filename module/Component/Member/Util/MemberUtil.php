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
namespace Component\Member\Util;
use App;
use Bundle\Component\Payment\Payco\Payco;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Bundle\Component\Policy\PaycoLoginPolicy;
use Bundle\Component\Policy\SnsLoginPolicy;
use Bundle\Component\Policy\NaverLoginPolicy;
use Bundle\Component\Policy\WonderLoginPolicy;
use Component\Database\DBTableField;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoKakaoServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\Manager;
use Component\Member\Member;
use Component\Member\MemberVO;
use Component\Member\MyPage;
use Component\Validator\Validator;
use Component\Mall\Mall;
use Cookie;
use Encryptor;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Object\SimpleStorage;
use Framework\Object\SingletonTrait;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\DateTimeUtils;
use Logger;
use Message;
use Request;
use Session;
/**
 * Class MemberUtil
 * @package Bundle\Component\Member\Util
 * @author  yjwee
 * @method static MemberUtil getInstance
 */
class MemberUtil extends \Bundle\Component\Member\Util\MemberUtil
{
    public static function logoutKakao()
    {
        parent::logoutKakao();

        /* 웹앤모바일 카카오싱크 튜닝 */
        if(\Cookie::has('no')){
            \Cookie::del('no');
        }
        /* 웹앤모바일 튜닝 끝 */
    }

}