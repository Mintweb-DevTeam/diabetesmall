<?php
namespace Controller\Front\Api;

use Component\Wm\MemberApi;

class MemberJoinFlController extends \Controller\Front\Controller
{
    public function index()
    {
        try{
            $wm = new MemberApi;

        }catch( \Exception $e ){
            echo json_encode(['code'=>$e->getCode(), 'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode( $wm->joinFl(), JSON_UNESCAPED_UNICODE ) ;
        exit;

    }
}
