<?php
namespace Controller\Api\Load;
use Component\Wm\MemberApi;

class MemberJoinFlController extends \Controller\Api\Controller
{
    public function index()
    {
       
        $header = getallheaders();
        gd_Debug($header);
        exit;

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
