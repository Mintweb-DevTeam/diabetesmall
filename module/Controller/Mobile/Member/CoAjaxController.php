<?php
namespace Controller\Mobile\Member;

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
			case 'agreeCk' :
				$data = $cossia->doubleCheck2($post);
			break;
		}
		$this->json($data);
	}
}
