<?php
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== START
namespace Controller\Front;

use Request;
use Session;
use App;

class SugartreeController extends \Controller\Front\Controller
{
    public function index()
    {
        $server = Request::server()->toArray();
        $domain = str_replace("www.", "", $server['SERVER_NAME']);
        $this->js("location.replace('https://m.".$domain."/sugartree');");
        exit;
    }
}
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== END