<?php
namespace Controller\Api\Load;
use Component\Wm\MemberApi;

class CampaignFlController extends \Controller\Api\Controller
{
    public function index()
    {

        try{

            $wm = new MemberApi;

        }catch( \Exception $e ){
            echo json_encode(['code'=>$e->getCode(), 'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode( $wm->campaignFl(), JSON_UNESCAPED_UNICODE ) ;
        exit;

    }
}
