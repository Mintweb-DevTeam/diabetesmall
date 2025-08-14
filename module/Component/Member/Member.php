<?php
namespace Component\Member;

use App;
use Bundle\Component\Godo\GodoKakaoServerApi;
use Component\Database\DBTableField;
use Component\Facebook\Facebook;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\GoodsStatistics\GoodsStatistics;
use Component\Mail\MailMimeAuto;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Util\MemberUtil;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Sms\SmsAutoObserver;
use Component\Validator\Validator;
use Encryptor;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\ImageUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Framework\Security\Digester;
use Framework\Utility\GodoUtils;
use Globals;
use Logger;
use Request;
use Session;
use UserFilePath;
use Component\Member\MemberDAO;
use Component\Sms\SmsAuto;

class Member extends \Bundle\Component\Member\Member
{

    /** @var  \Bundle\Component\Member\MemberDAO */
    private $memberDAO;
    /** @var  \Bundle\Component\Sms\SmsAuto */
    private $smsAuto;

    /**
     * Member constructor.
     *
     * @param array $config 클래스 생성자에 필요한 각종 설정을 받는 인자. 객체 주입이 주 목적이다.
     */
    public function __construct($config = [])
    {
        parent::__construct();
        $this->tableFunctionName = 'tableMember';
        $this->fieldTypes = DBTableField::getFieldTypes($this->tableFunctionName);
        if (isset($config['memberDao']) && \is_object($config['memberDao'])) {
            $this->memberDAO = $config['memberDao'];
        } else {
            $this->memberDAO = \App::load(MemberDAO::class);
        }

        if (isset($config['smsAuto']) && \is_object($config['smsAuto'])) {
            $this->smsAuto = $config['smsAuto'];
        } else {
            $this->smsAuto = \App::load(SmsAuto::class);
        }
    }

    /**
     * 회원가입
     *
     * @param $params
     *
     * @return \Component\Member\MemberVO
     * @throws Exception
     */
    public function join2($params)
    {
        $session = \App::getInstance('session');
        $globals = \App::getInstance('globals');
        $logger = \App::getInstance('logger');

        if (isset($params['birthYear']) === true && isset($params['birthMonth']) === true && isset($params['birthDay']) === true) {
            $params['birthDt'] = $params['birthYear'].'-'.$params['birthMonth'].'-'.$params['birthDay'];
        }
        if (isset($params['marriYear']) === true && isset($params['marriMonth']) === true && isset($params['marriDay']) === true) {
            $params['marriDate'] = $params['marriYear'].'-'.$params['marriMonth'].'-'.$params['marriDay'];
        }

        $vo = $params;
        if (is_array($params)) {
            DBTableField::setDefaultData($this->tableFunctionName, $params);
            $vo = new \Component\Member\MemberVO($params);
        }

        $vo->databaseFormat();
        $vo->setEntryDt(date('Y-m-d H:i:s'));
        $vo->setGroupSno(GroupUtil::getDefaultGroupSno());

        $v = new Validator();
        $v->init();
        $v->add('agreementInfoFl', 'yn', true, '{' . __('이용약관') . '}'); // 이용약관
        $v->add('privateApprovalFl', 'yn', true, '{' . __('개인정보 수집.이용 동의 필수사항') . '}'); // 개인정보동의 이용자 동의사항
        $v->add('privateApprovalOptionFl', '', false, '{' . __('개인정보 수집.이용 동의 선택사항') . '}'); // 개인정보동의 이용자 동의사항
        $v->add('privateOfferFl', '', false, '{' . __('개인정보동의 제3자 제공') . '}'); // 개인정보동의 제3자 제공
        $v->add('privateConsignFl', '', false, '{' . __('개인정보동의 취급업무 위탁') . '}'); // 개인정보동의 취급업무 위탁
        $v->add('foreigner', '', false, '{' . __('내외국인구분') . '}'); // 내외국인구분
        $v->add('dupeinfo', '', false, '{' . __('본인확인 중복가입확인정보') . '}'); // 본인확인 중복가입확인정보
        $v->add('pakey', '', false, '{' . __('본인확인 번호') . '}'); // 본인확인 번호
        $v->add('rncheck', '', false, '{' . __('본인확인방법') . '}'); // 본인확인방법
        $v->add('under14ConsentFl', 'yn', true, '{' . __('만 14세 이상 동의') . '}'); // 만 14세 이상 동의

        $joinSession = new SimpleStorage($session->get(\Bundle\Component\Member\Member::SESSION_JOIN_INFO));
        $session->del(Member::SESSION_JOIN_INFO);
        $vo->setPrivateApprovalFl($joinSession->get('privateApprovalFl'));
        $vo->setPrivateApprovalOptionFl(json_encode($joinSession->get('privateApprovalOptionFl'), JSON_UNESCAPED_SLASHES));
        $vo->setPrivateOfferFl(json_encode($joinSession->get('privateOfferFl'), JSON_UNESCAPED_SLASHES));
        $vo->setPrivateConsignFl(json_encode($joinSession->get('privateConsignFl'), JSON_UNESCAPED_SLASHES));
        $vo->setForeigner($joinSession->get('foreigner'));
        $vo->setDupeinfo($joinSession->get('dupeinfo'));
        $vo->setPakey($joinSession->get('pakey'));
        $vo->setRncheck($joinSession->get('rncheck'));
        $vo->setUnder14ConsentFl($joinSession->get('under14ConsentFl'));
        $toArray = $vo->toArray();
        if ($v->act($toArray) === false) {
            $logger->error(implode("\n", $v->errors));
            throw new Exception(implode("\n", $v->errors));
        }

        $hasPaycoUserProfile = $session->has(GodoPaycoServerApi::SESSION_USER_PROFILE);
        $hasNaverUserProfile = $session->has(GodoNaverServerApi::SESSION_USER_PROFILE);
        $hasThirdPartyProfile = $session->has(Facebook::SESSION_USER_PROFILE);
        $hasKakaoUserProfile = $session->has(GodoKakaoServerApi::SESSION_USER_PROFILE);
        $hasWonderUserProfile = $session->has(GodoWonderServerApi::SESSION_USER_PROFILE);
        $passValidation = $hasPaycoUserProfile || $hasNaverUserProfile || $hasThirdPartyProfile || $hasKakaoUserProfile || $hasWonderUserProfile;
        \Component\Member\MemberValidation::validateMemberByInsert($vo, null, $passValidation);

        $authCellPhonePolicy = new SimpleStorage(gd_get_auth_cellphone_info());
        $ipinPolicy = new SimpleStorage(ComponentUtils::getPolicy('member.ipin'));

        //SNS 회원 가입을 진행중이고
        //본인 인증을 노출하지 않을 경우 아이핀/휴대폰 본인인증의 상태값을 미사용(n)으로 변경함.
        if ($passValidation === true && \Component\Member\MemberValidation::checkSNSMemberAuth() === 'n') {
            $ipinPolicy->set('useFl', 'n');
            $authCellPhonePolicy->set('useFl', 'n');
        }




        // 휴대폰인증시 저장된 세션정보와 실제 넘어온 파라미터 검증 (생년월일) - XSS 취약점 개선요청
        if ($authCellPhonePolicy->get('useFl', 'n') === 'y' && $session->has(Member::SESSION_DREAM_SECURITY)) {
            $dreamSession = new SimpleStorage($session->get(Member::SESSION_DREAM_SECURITY));

            $joinItem = gd_policy('member.joinitem');
            if ($joinItem['birthDt']['use'] === 'y' && $dreamSession->get('ibirth') != str_replace('-','', $vo->getBirthDt())) {
                throw new Exception(__("휴대폰 인증시 입력한 생년월일과 동일하지 않습니다."));
            }



            if ($joinItem['cellPhone']['use'] === 'y' && $dreamSession->get('phone') != str_replace('-','', $vo->getCellPhone())) {
                throw new Exception(__("휴대폰 인증시 입력한 번호와 동일하지 않습니다."));
            }

            if ($dreamSession->get('name') != $vo->getMemNm()) {
                throw new Exception(__("휴대폰 인증시 입력한 이름과 동일하지 않습니다."));
            }
        }



        /*if ($hasWonderUserProfile === false && $authCellPhonePolicy->get('useFl', 'n') === 'y' && $ipinPolicy->get('useFl', 'n') === 'n'&& !$session->has('simpleJoin')) {

            if (!$session->has(Member::SESSION_DREAM_SECURITY)) {
                $logger->error('Cellphone need identity verification.');
                throw new Exception(__('휴대폰 본인인증이 필요합니다..'));
            }
            $dreamSession = new SimpleStorage($session->get(Member::SESSION_DREAM_SECURITY));
            $session->del(Member::SESSION_DREAM_SECURITY);
            if (!Validator::required($dreamSession->get('DI'))) {
                $logger->error('Duplicate identification entry information does not exist.');
                throw new Exception(__('본인확인 중복가입정보가 없습니다.'));
            }
            if (!$vo->isset($vo->getDupeinfo())) {
                $vo->setDupeinfo($dreamSession->get('DI'));
            }
            if (!$vo->isset($vo->getBirthDt())) {
                $vo->setBirthDt($dreamSession->get('ibirth'));
            }
        }*/



        $member = $vo->toArray();
        if (empty($member['dupeinfo']) === false && MemberUtil::overlapDupeinfo($member['memId'], $member['dupeinfo'])) {
            $logger->error('Already members registered customers.');
            throw new Exception(__('이미 회원등록한 고객입니다.'));
        }
        if ($member['appFl'] == 'y') {
            $member['approvalDt'] = date('Y-m-d H:i:s');
        }

        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
        $wm = new \Component\Wm\Wm();
        if($wm->agreementFl) {
            $member['agreementSp'] = $params['agreementSp'];
            if(empty($member['agreementSp'])) {
                $member['agreementSp'] = 'n'; // 회원가입 시 기본값 n
            }
        }
        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END

        $hasSessionGlobalMall = $session->has(SESSION_GLOBAL_MALL);
        $isUseGlobal = $globals->get('gGlobal.isUse', false);
        $logger->info(sprintf('has session global mall[%s], global use[%s]', $hasSessionGlobalMall, $isUseGlobal));




        if ($hasSessionGlobalMall && $isUseGlobal) {
            $mallSnoBySession = \Component\Mall\Mall::getSession('sno');
            $logger->info('has global mall session and has globals isUse. join member mallSno=' . $mallSnoBySession);
            $member['mallSno'] = $mallSnoBySession;

        } else {


            $logger->info('join member default mallSno');
            $member['mallSno'] = DEFAULT_MALL_NUMBER;
        }
        if ($hasPaycoUserProfile || $hasNaverUserProfile || $hasThirdPartyProfile || $hasKakaoUserProfile || $hasWonderUserProfile) {
            $member['rncheck'] = 'aithCellphone';
            $memNo = $this->memberDAO->insertMemberByThirdParty($member);

            $member['memNo'] = $memNo;
        } else {
            $memNo = $this->memberDAO->insertMember($member);
            $member['memNo'] = $memNo;
        }
        if ($member['mallSno'] == DEFAULT_MALL_NUMBER) {
            $this->benefitJoin(new \Component\Member\MemberVO($member));
        } else {
            $logger->info(sprintf('can\'t benefit. your mall number is %d', $member['mallSno']));
        }

        $session->set(Member::SESSION_NEW_MEMBER, $member['memNo']);

        if ($vo->isset($member['cellPhone'])) {
            /** @var \Bundle\Component\Sms\SmsAuto $smsAuto */
            $aBasicInfo = gd_policy('basic.info');
            $aMemInfo = $this->getMemberId($memNo);
            $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
            $observer = new SmsAutoObserver();
            $observer->setSmsType(SmsAutoCode::MEMBER);
            $observer->setSmsAutoCodeType(Code::JOIN);
            $observer->setReceiver($member);
            $observer->setReplaceArguments(
                [
                    'name'      => $member['memNm'],
                    'memNm'     => $member['memNm'],
                    'memId'     => $member['memId'],
                    'appFl'     => $member['appFl'],
                    'groupNm'   => $aMemInfo['groupNm'],
                    'mileage'   => 0,
                    'deposit'   => 0,
                    'rc_mallNm' => Globals::get('gMall.mallNm'),
                    'shopUrl'   => $aBasicInfo['mallDomain'],
                ]
            );
            $smsAuto->attach($observer);
        }

        return new \Component\Member\MemberVO($member);
    }
}