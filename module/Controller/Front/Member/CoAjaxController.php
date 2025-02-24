<?php
namespace Controller\Front\Member;

use Request;

class CoAjaxController extends \Controller\Front\Controller
{
    public function index()
	{
		$cossia = new \Component\Cossia\Cossia;
		$post = Request::post()->all();
		switch($post['type']){
			case 'doubleCheck' :
				$data = $cossia->doubleCheck($post);
			break;
		}
		$this->json($data);
		exit;
	}
}