<?php

namespace Controller\Mobile\Member;

use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Godo\GodoKakaoServerApi;
use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\Member\Member;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Component\Member\MemberSnsDAO;

class KakaoQrController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $in = \Request::post()->all();

        if (empty($in)) {
            throw new AlertBackException('잘못된 접근입니다.');
        }

        $this->setData($in);
    }
}