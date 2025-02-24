<?php

namespace Controller\Front\Goods;

use Request;
use Session;
use App;
//2024.02.01웹앤모바일 추가
class SugartreePsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
		
		\Session::del('event_goodsNo');//2024.10.21웹앤모바일 추가
		
        if(!empty($postValue['start_hp'])) {
			
			//2024.10.21웹앤모바일 추가시작
			if(!empty($postValue['event_goods'])){
				Session::set('event_goodsNo',$postValue['event_goods']);
			}
			//2024.10.21웹앤모바일 추가종료
			
            $type = 'sha512';
            $encryptKey = 'Vo4YzlGm12';
            $data = $encryptKey . str_replace("-","",$postValue['start_hp']);
            $hashed = hash($type, $data);

           // $this->json(strtoupper($hashed));
		   
		   $secret_phone = strtoupper($hashed);
		   
		   echo"<script>top.history_return('{$secret_phone}');</script>";
        }

        exit;
    }
}