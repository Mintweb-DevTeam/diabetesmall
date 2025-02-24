<?php
namespace Controller\Mobile\Qrcode;
use Request;
class CoJoinStepdController extends \Controller\Mobile\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::request()->all();
		if( !$post['cellPhone'] && !$post['memNm'] ){
			$this->alert('잘못된 접근 입니다.', null, './co_join_stepa.php');
		}
		$cossia = new \Component\Cossia\Cossia;
		$post['cellPhone'] = $cossia->getCellPhone($post['cellPhone']);
		if( $post['cellPhone'] === false ){
			echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
			exit;
		}
		$post['sno'] = $cossia->insertCoAbbottMember($post);
		$this->redirect('./co_join_stepe.php?sno='.$post['sno'].'&name='.$post['memNm'], null, 'self');
		$this->setData('now', 'NOW ');
    }
}