<?php

namespace Controller\Front;

use Session;

class IndexController extends \Bundle\Controller\Front\IndexController
{
    public function index()
    {
        parent::index();

        if (Session::get('member.memNo')) {

            $cossia = new \Component\Cossia\Cossia;
            $bool = $cossia->getAgreementItem(Session::get('member.memNo'));
            if ($bool) {    // 필수항목이 n 으로 되어 있음
                echo '<script>alert("필수항목 동의가 필요 합니다. 마이페이지로 이동합니다.");window.location.replace("/mypage/my_page_password.php");</script>';
                exit;
            }

            $wm = new \Component\Wm\Wm();
            if ($wm->agreementFl) {
                $agreementSp = $wm->getAgreementSp(Session::get('member.memNo'));
                if (empty($agreementSp)) {
                    echo '<script>alert("제3자 정보 제공 동의 여부 확인이 필요합니다. 마이페이지로 이동합니다.");window.location.replace("/mypage/agreement_chk.php");</script>';
                    exit;
                }
            }

        }
    }
}