<?php
namespace Controller\Mobile\Verification;
use Component\Cossia\Cossia;
class MemberVerificationController extends \Controller\Mobile\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = \Request::post()->all();
		$cossia = new Cossia;
		$sql = 'SELECT * FROM `co_abbottMember` WHERE `memNm` = \''.$post['memNm'].'\' AND `email` = \''.$post['email'].'\' AND `cellPhone` = \''.$cossia->getCellPhone( $post['cellPhone'] ).'\'';
		$data = $this->db->query_fetch($sql, true)[0];
		if( $data['sno'] ) $result = true;
		else $result = false;
		$this->json( $result );
	}
}



