<?php
namespace Controller\Mobile\Qrcode;
use Request;
use Exception;
use Cookie;
use Encryptor;
class CoJoinStepeController extends \Controller\Mobile\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$get = Request::get()->all();
		if( !$get['sno']){
			$this->alert('잘못된 접근 입니다.', null, './co_join_stepa.php');
		}
		$this->setData('text', ($get['type'] == 'uze') ? '확인' : '완료' );
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->abbottRealMemberCheck(null, $get['sno']);
		
		//2024.08.16웹앤모바일 추가 시작
		$memNm = urlencode(Encryptor::encrypt($data['memNm']));
		$this->setData("encMemNm",$memNm);
		//2024.08.16웹앤모바일 추가 종료
		if($data['code'] === true){
			$this->setData('data', $data);
			$co_Mail = new \Component\Cossia\Mail;
			$param = ['sno'=>$data['sno'], 'email'=>$data['email'], 'memNm'=>$data['memNm'], 'cellPhone'=>$data['cellPhone'], 'type'=>$get['type'], 'isQr'=>'y'];
			$co_Mail->libre_qr_send($param);
			// $co_Mail->qr_send( $data['cellPhone'], $data['memNm'] );
		}else if ($data['code'] === false){
			Cookie::del('memNm');
			Cookie::del('memId');
			Cookie::del('cellPhone');
			$this->redirect('./co_join_stepa.php', null, 'self');
		}else{
			Cookie::del('memNm');
			Cookie::del('memId');
			Cookie::del('cellPhone');
			$this->alert('동의하지 않은 회원 입니다.', null, './co_join_stepa.php');
		}
    }
}