<?php

namespace Component\Wm;

class Wm
{
    protected $db = null;

    public $agreementFl = false;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        if (\Request::getRemoteAddress() == "182.216.219.157") {
            $this->agreementFl = true; // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가
        }
    }

    public function getToken($memNo)
    {
        $this->db->strField = "uuid";
        $this->db->strWhere = "memNo = '" . \Encryptor::decrypt($memNo) . "'";
        $query = $this->db->query_complete();
        $sql = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER_SNS . implode(' ', $query);
        $data = $this->db->fetch($sql);

        if ($data) {
            $sql = "SELECT memId FROM es_member WHERE memNo = '" . \Encryptor::decrypt($memNo) . "'";
            $result = $this->db->fetch($sql);
        }

        return $result['memId'];
    }

    // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
    public function getAgreementSp($memNo)
    {
        if (empty($memNo)) {
            return null;
        }

        $row = $this->db->fetch("select * from es_member where memNo='{$memNo}'");
        return $row['agreementSp'];
    }

    public function setAgreementSp($postValue, $memNo)
    {
        if (empty($memNo)) {
            return false;
        }
        
        $agreementSp = $postValue['agreementSp'];
        if(empty($agreementSp)) {
            if($postValue['mode'] != 'modify') { // 관리자만 null 허용
                $agreementSp = 'n';
            }
        }

        return $this->db->query("update es_member set agreementSp='{$agreementSp}' where memNo='{$memNo}'");
    }

    public function getAgreementInfo()
    {
        $row = $this->db->fetch("select * from wm_agreement where sno=1");
        return stripslashes($row['contents']);
    }

    public function saveAgreementInfo($postValue)
    {
        $managerNo = Session::get('manager.sno');
        $contents = $postValue['contents'];
        if (!empty($contents)) {
            $contents = addslashes($postValue['contents']);
        }

        return $this->db->query("update wm_agreement set contents='{$contents}', managerNo='{$managerNo}' where sno=1");
    }
    // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END
}