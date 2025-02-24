<?php
namespace Controller\Mobile\Qrcode;

use Request;

class CoAjaxController extends \Controller\Mobile\Controller
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