<?php

namespace Component\Member;

use App;
use Component\Database\DBTableField;
use Component\Mail\MailUtil;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Util\MemberUtil;
use Component\Mileage\Mileage;
use Component\Mileage\MileageDAO;
use Component\Sms\Code;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Exception;
use Framework\Security\Digester;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\GodoUtils;
use Globals;
use Logger;
use Request;
use Session;

class MemberAdmin extends \Bundle\Component\Member\MemberAdmin
{
    /**
     * 관리자 회원정보 수정
     *
     * @param $arrData
     * @param $from
     *
     * @return bool
     * @throws Exception
     */
    public function modifyMemberData($arrData, $from = null)
    {
        $result = parent::modifyMemberData($arrData, $from);

        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {
            $wm->setAgreementSp($arrData, $arrData['memNo']);
        }
        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END

        return $result;
    }
}