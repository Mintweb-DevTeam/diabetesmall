<?php
namespace Controller\Mobile\Verification;
class SetpAController extends \Controller\Mobile\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = \Request::post()->all();
		if( !$post['cellPhone'] ){
			$this->alert('올바른 접근방법이 아닙니다.',null,'/verification/');
		}
		$this->setData('cellPhone', $post['cellPhone']);
	}
}
