<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Controller\Front\Mypage;

use Request;
use Session;
use App;

class WmAgreementPsController extends \Controller\Front\Controller
{
    public function index()
    {
        $memNo = Session::get('member.memNo');
        if (empty($memNo)) {
            $this->json(['msg' => '로그인 해주세요.']);
        }

        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {
            $result = $wm->setAgreementSp(Request::post()->toArray(), $memNo);
            if ($result) {
                $this->json(['result' => 'success']);
            } else {
                $this->json(['result' => 'fail']);
            }
        }

        exit;
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END