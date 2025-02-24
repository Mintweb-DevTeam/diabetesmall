<?php
namespace Controller\Mobile\Member;
use Request;
class OldMemAgreePsController extends \Controller\Mobile\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		if( !$post['cellPhone'] ){
			$this->alert('잘못된 접근 입니다.', null, '/member/co_join_stepa.php');
		}
		$cossia = new \Component\Cossia\Cossia;
		$post['cellPhone'] = $cossia->getCellPhone($post['cellPhone']);
		if( $post['cellPhone'] === false ){
			echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
			exit;
		}
		$data = $cossia->abbottCheck($post['cellPhone']);
		if($data['code'] == 'isMem'){
			echo '<script>parent.alert("이미 가입된 회원 입니다.");parent.window.location.replace("/");</script>';
		}else if ( $data['code'] == 'isOk' ){
			echo '<script>parent.window.location.replace("./co_join_stepd.php?type=success&sno='.$data['sno'].'&memNm='.$data['memNm'].'&memId='.$data['email'].'&cellPhone='.$data['cellPhone'].'&type=uze");</script>';
		}else if(  $data['code'] == 'isNot' ){
			echo '<script>parent.alert("등록된 정보가 없습니다.");parent.window.location.replace("./co_join_stepb.php");</script>';
		}
		exit;
    }
}