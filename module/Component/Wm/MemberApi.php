<?php
/*
 * 웹앤모바일 23-09-19
*/
namespace Component\Wm;

use Exception;

class MemberApi
{
    private $db;
    private $table = 'co_abbottMember';
    private $header;
    const AUTHORIZATION = 'Bare MUI3MjM0RUExQTgyRDA1ODZGRDUyOEM4OTY2QTVCN0Y=';

    const RESULT_CODE_SUCCESS = 200;
    const RESULT_MSG_SUCCESS = '성공';

    const RESULT_CODE_BAD_REQUEST = 400;
    const RESULT_MSG_BAD_REQUEST = '잘못된 요청';

    const FILDS_CODE_BAD_REQUEST = 410;
    const FILDS_MSG_BAD_REQUEST = '필요한 데이터가 없습니다 : ';

    const RESULT_CODE_BAD_SERVER = 500;
    const RESULT_MSG_BAD_SERVER = '서버에러';

    public function __construct(){
        if (!is_object($this->db)) $this->db = \App::load('DB');
//        $this->header = getallheaders();
        $this->data = json_decode( file_get_contents("php://input"), true );

//        if( $this->header['Authorization'] != self::AUTHORIZATION )
//            throw new Exception(self::RESULT_MSG_BAD_REQUEST, self::RESULT_CODE_BAD_REQUEST);
        if( !is_array( $this->data) )
            throw new Exception(self::RESULT_MSG_BAD_REQUEST, self::RESULT_CODE_BAD_REQUEST);
        if( !$this->data["cellPhone"] )
            throw new Exception(self::FILDS_CODE_BAD_REQUEST, self::FILDS_MSG_BAD_REQUEST);

    }

    function formatPhone( $phone = '' )
    {

        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);

        $phone_ = '';
        switch($length){
            case 11 :
                $phone_ = preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
                break;
            case 10:
                $phone_ = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
                break;
        }
        return $phone_;
    }

    public function joinFl(){
        $cellPhone = $this->formatPhone($this->data["cellPhone"]);

        $sql = "SELECT count(*) as cnt FROM ".$this->table." WHERE cellPhone = '{$cellPhone}'";
        $cnt = $this->db->fetch($sql);
        return $cnt['cnt']
            ?
            ['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>'true']
            :
            ['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>'false'];
    }

//    public function campaignFl(){
//        $cellPhone = $this->formatPhone($this->data["cellPhone"]);
//
//        $sql = "SELECT campaignFl FROM ".$this->table." WHERE cellPhone = '{$cellPhone}'";
//        $campaignFl = $this->db->fetch($sql);
//
//        if($campaignFl['campaignFl'] == 'y'){
//            return ['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>'true'];
//        } else {
//            return  ['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>'false'];
//        }
//    }

    public function campaignInfoUpdate(){
        $cellPhone = $this->formatPhone($this->data["cellPhone"]);

        $sql = "SELECT count(*) as cnt FROM ".$this->table." WHERE cellPhone = '{$cellPhone}'";
        $cnt = $this->db->fetch($sql);

        if($cnt['cnt']) {
            return ['code'=>420, 'msg'=>'해당 번호로 가입된 회원이 있습니다'];
        } else {


            //25-03-13 웹앤모바일 튜닝
            $privateApprovalOptionFl = [
                "19" => "y",
                "21" => "y"
            ];

            $privateOfferFl = [
                "5" => "y",
                "22" => "y",
                "25" => "n",
                "26" => "n"
            ];

            $privateConsignFl = [
                "20" => "n",
                "23" => "n"
            ];

            $privateOfferFl['25'] = $this->data['user_select'];
            $privateOfferFl['26'] = $this->data['private_select'];
            $privateConsignFl['20'] = $this->data['marketing_opt'];
            $privateConsignFl['23'] = $this->data['marketing_agreement'];

            // JSON 변환
            $param['privateApprovalOptionFl'] = $privateApprovalOptionFl;
            $param['privateOfferFl'] = $privateOfferFl;
            $param['privateConsignFl'] = $privateConsignFl;
            $device = ', `device` = \'qr\' ';

            $sql = 'INSERT INTO `co_abbottMember` SET `pharmacy_code` = \'TRYNOWDWGH\', `memNm` = \''.$this->data['memNm'].'\', `email` = \''.$this->data['email'].'\', `cellPhone` = \''.$this->data['cellPhone'].'\', `privateApprovalOptionFl` = \''.json_encode($param['privateApprovalOptionFl']).'\', `privateOfferFl` = \''.json_encode($param['privateOfferFl']).'\', `privateConsignFl` = \''.json_encode($param['privateConsignFl']).'\''.$device;
            $this->db->query($sql);

            return ['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>'성공'];
        }
    }
}