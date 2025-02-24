<?php
namespace Controller\Front\Mypage;

use Request;
use Session;

class HackOutPsController extends \Bundle\Controller\Front\Mypage\HackOutPsController
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		$session = Session::all();
		
		$sql = 'SELECT `abbott_sno` FROM `es_member` WHERE `memNo` = '.$session['member']['memNo'];
		$sno = $this->db->query_fetch($sql, true);
		if($sno[0]['abbott_sno']){
			$sql = 'UPDATE `co_abbottMember` SET `isJoin` = \'n\' WHERE `sno` = '.$sno[0]['abbott_sno'];
			$this->db->query($sql);
		}
		parent::index();
	}
}