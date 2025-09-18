<?php

namespace Component\Database;

class MemberTableField extends \Bundle\Component\Database\MemberTableField
{
    public function tableMember()
    {
        $arrField = parent::tableMember();
        $arrField[] = ['val' => 'pharmacy_code', 'typ' => 's', 'def' => null]; // 약국 코드
        $arrField[] = ['val' => 'coKakaoChannel', 'typ' => 'i', 'def' => 0]; // 카카오 채널 입력

        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
        $arrField[] = ['val' => 'agreementSf', 'typ' => 's', 'def' => '']; // 제3자 정보 제공 동의
        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END

        return $arrField;
    }
}