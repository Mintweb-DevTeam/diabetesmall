<?php
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== START
namespace Controller\Mobile;

use Request;
use Session;
use App;

class SugartreePsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        if(!empty($postValue['phone'])) {
            $type = 'sha512';
            $encryptKey = 'Vo4YzlGm12';
            $data = $encryptKey . $postValue['phone'];
            $hashed = hash($type, $data);

            $this->json(strtoupper($hashed));
        }

        exit;
    }
}
// 웹앤모바일 특정페이지 개발 및 참여이력 관리 기능 추가(모바일만 작업) ================================================== END