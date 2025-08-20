<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Widget\Front\Wm;

use Request;
use Session;
use App;

class AgreementPopupWidget extends \Widget\Front\Widget
{
    public function index()
    {
        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {

            // 개인정보 처리방침
            $buyerInformService = new \Component\Agreement\BuyerInform();
            $this->setData('privateInfo', $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::BASE_PRIVATE));

            // 제3자 정보 제공
            $this->setData('spInfo', $wm->getAgreementInfo());
        }
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END