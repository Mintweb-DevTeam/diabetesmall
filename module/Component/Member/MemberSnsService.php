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

namespace Component\Member;

use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Member\Util\MemberUtil;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Request;
use Component\Member\MemberSleep;

/**
 * Class MemberSnsService
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberSnsService extends \Bundle\Component\Member\MemberSnsService
{
    public function disconnectSns($memberNo)
    {
        parent::disconnectSns($memberNo); // TODO: Change the autogenerated stub

        $db = \App::load(\DB::class);

        $sql = "SELECT abbott_sno FROM es_member WHERE memNo = ".$memberNo;
        $data = $db->fetch($sql);

        if($data){
            $sql = 'UPDATE `co_abbottMember` SET `kakaoFl` = \'n\' WHERE `sno` = \''.$data['abbott_sno'].'\'';
            $db->query($sql);
        }
    }

    public function connectSns($memberNo, $uuid, $accessToken, $snsTypeFl = '')
    {
        if($snsTypeFl == 'kakao') {
            $db = \App::load(\DB::class);

            $sql = "SELECT abbott_sno FROM es_member WHERE memNo = ".$memberNo;
            $data = $db->fetch($sql);

            if($data){
                $sql = 'UPDATE `co_abbottMember` SET `kakaoFl` = \'y\' WHERE `sno` = \''.$data['abbott_sno'].'\'';
                $db->query($sql);

            }
        }


        parent::connectSns($memberNo, $uuid, $accessToken, $snsTypeFl); // TODO: Change the autogenerated stub
    }
}