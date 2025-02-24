<?php
/*
# 2023-05-18 Cossia
# 닥다몰 회원 정보를 입력하기 위한 API
*/
namespace Controller\Front\Api;
use Component\Cossia\Drdiary;
class DrdiaryController extends \Controller\Front\Controller
{
	public function index()
	{
		try{
			$cossia = new Drdiary;
		}catch( \Exception $e ){
			echo json_encode(['code'=>$e->getCode(), 'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
			exit;
		}
		echo json_encode( $cossia->saveData(), JSON_UNESCAPED_UNICODE ) ;
		exit;
	}
}