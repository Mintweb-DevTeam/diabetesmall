<?php
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== START
namespace Controller\Mobile;

use Request;
use Session;
use App;

class SugartreeFindController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $this->setData('isMobile', Request::isMobileDevice());
    }
}
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== END