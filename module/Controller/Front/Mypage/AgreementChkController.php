<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Controller\Front\Mypage;

use Request;
use Session;
use App;
use Framework\Debug\Exception\AlertBackException;

class AgreementChkController extends \Controller\Front\Controller
{
    public function index()
    {
        $memNo = Session::get('member.memNo');
        if (empty($memNo)) {
            throw new AlertBackException(__('잘못된 접근입니다.'));
        }

        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {
            $agreementSp = $wm->getAgreementSp($memNo);
            if (!empty($agreementSp)) {
                throw new AlertBackException(__('잘못된 접근입니다.'));
            }

            $buyerInformService = new \Component\Agreement\BuyerInform();

            $agreementInfo = $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::AGREEMENT);
            $this->setData('agreementInfo', $agreementInfo);

            $privateInfo = $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::BASE_PRIVATE);
            $this->setData('privateInfo', $privateInfo);

            $this->setData('spInfo', $wm->getAgreementInfo());
        } else {
            throw new AlertBackException(__('잘못된 접근입니다.'));
        }
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END