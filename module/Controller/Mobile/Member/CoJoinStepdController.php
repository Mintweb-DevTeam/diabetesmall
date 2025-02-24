<?php
namespace Controller\Mobile\Member;
use Request;
use Encryptor;
class CoJoinStepdController extends \Controller\Mobile\Controller
{
	
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::request()->all();
		if( !$post['cellPhone'] && !$post['memNm'] ){
			$this->alert('잘못된 접근 입니다.', null, '/member/co_join_stepa.php');
		}
		$cossia = new \Component\Cossia\Cossia;
		$post['cellPhone'] = $cossia->getCellPhone($post['cellPhone']);
		$this->setData('text', ($post['type'] == 'uze') ? '확인' : '완료' );
		if( $post['cellPhone'] === false ){
			echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
			exit;
		}
		if(!$post['type']){
			$post['sno'] = $cossia->insertCoAbbottMember($post);
			$this->setData('now', 'NOW ');
		}
		
		$this->setData('post', $post);
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			$memNm = urlencode(Encryptor::encrypt($post['memNm']));
			$url = '/member/co_join_stepe.php?sno='.$post['sno'].'&name='.$memNm;
		//}else{		
		//	$url = '/member/co_join_stepe.php?sno='.$post['sno'].'&name='.$post['memNm'];
		
		//}
		$this->setData('url', $url );
		
 		$co_Mail = new \Component\Cossia\Mail;
		$co_Mail->libre_send(['sno'=>$post['sno'], 'email'=>$post['memId'], 'memNm'=>$post['memNm'], 'cellPhone'=>$post['cellPhone'], 'type'=>$post['type'], 'isQr'=>null]);
		/*
		$param = array('receiver'=>$post['memNm'],'receiverMail'=>$post['memId'],'sno'=>$post['sno'],'cellPhone'=>$post['cellPhone']);
		$co_Mail->send($param);
		*/
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			if( \Session::has('claim') )
				$this->redirect('../member/co_join_stepe.php?sno='.$post['sno'].'&name='.$memNm);
		//}else{
		//	if( \Session::has('claim') )
		//		$this->redirect('../member/co_join_stepe.php?sno='.$post['sno'].'&name='.$post['memNm']);
		//}
		//2024.02.05웹앤모바일 해당 페이지에서 쇼핑몰 회원가입페이지로 바로이동
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			$this->redirect($url);
			exit();
		//}		
   }
}