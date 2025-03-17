<?php

namespace Controller\Front\Member;

use App;
use Request;

class KakaoChkController extends \Controller\Front\Controller
{
    /** 웹앤모바일 카카오싱크 유효성 검사 */
    private $db="";

    public function pre()
    {
        if(!is_object($this->db)){
            $this->db = App::load("DB");
        }
    }

    public function index()
    {
        $post = Request::post()->toArray();
        $get = Request::get()->toArray();
        $req = array_merge($post,$get);

        $data = array();
        switch ($req['mode']) {
            case 'email_chk':
                if(Request::isAjax()){
                    $email=$req['email'];
                    $phone=$req['phoneNumber'];
                    $phone2 = str_replace('-','',$phone);
                    $data['result']="success";

                    if(!empty($email)){
                        $strSQL="select count(*) as cnt from ".DB_MEMBER." where email=? OR cellPhone = ? OR cellPhone = ?";
                        $row = $this->db->query_fetch($strSQL,['sss',$email,$phone,$phone2],false);
                        if($row['cnt']>0) $data['result']="fail";

                    }else{
                        $data['result']="fail";
                    }
                }else{
                    $data['result']="fail";
                }

                echo json_encode($data);
                break;
        }
        exit();

    }
}