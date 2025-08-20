<?php
namespace Controller\Mobile\Member;

use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;

class JoinMembershipController extends \Controller\Mobile\Controller
{
    /** 웹앤모바일 카카오싱크 튜닝 25-03-14 */
    public function index()
    {
        $in = \Request::post()->all();

        if (empty($in)) {
            throw new AlertBackException('잘못된 접근입니다.');
        }

        $this->setData($in);

        //$this->setData('gPageName', '카카오 회원가입');

        $buyerInformService = new \Component\Agreement\BuyerInform();

        $agreementInfo = $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::AGREEMENT);
        $this->setData('agreementInfo', $agreementInfo);
    }
}