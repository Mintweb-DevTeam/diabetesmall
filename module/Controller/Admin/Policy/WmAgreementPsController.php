<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Controller\Admin\Policy;

use Session;
use Request;
use App;

class WmAgreementPsController extends \Controller\Admin\Controller
{
    public $db = null;

    public function index()
    {
        $postValue = Request::post()->toArray();

        $wm = new \Component\Wm\Wm();
        $result = $wm->saveAgreementInfo($postValue);

        if ($result) {
            $this->layer('저장되었습니다.');
        } else {
            $this->layer('저장에 실패하였습니다.');
        }

        exit;
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END