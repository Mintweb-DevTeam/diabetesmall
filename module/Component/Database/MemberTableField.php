<?php
namespace Component\Database;
class MemberTableField extends \Bundle\Component\Database\MemberTableField {
	public function tableMember(){
        $arrField = parent::tableMember();
        $arrField[] = ['val' => 'pharmacy_code', 'typ' => 's', 'def' => null]; // 약국 코드
		$arrField[] = ['val' => 'coKakaoChannel', 'typ' => 'i', 'def' => 0]; // 카카오 채널 입력
        return $arrField;
    }
}