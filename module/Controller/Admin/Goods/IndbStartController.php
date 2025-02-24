<?php
namespace Controller\Admin\Goods;

class IndbStartController extends \Controller\Admin\Controller
{

	public function index()
	{
		$in = \Request::request()->all();
		
		$db = \App::load(\DB::class);
		
		switch($in['mode']){
			case "update_goods_config_list":
				
				$db->query("delete from wm_start_goods where 1=1");
				
				foreach($in['goodsNo'] as $key => $val){
					
					
					if(!empty($in['chk'][$val])){
						
						$db->query("insert into wm_start_goods set goodsNo='{$val}',move_goodsNo='{$in['move_goodsNo'][$val]}',regDt=sysdate()");
					
					}
				}
				
				//2024.10.18웹앤모바일 추가시작
				foreach($in['goodsNo'] as $key => $val){
					
					if(!empty($in['other_chk'][$val])){
						
						$row = $db->fetch("select idx from wm_start_goods ");
						
						if(empty($row['idx'])){
						
							$db->query("insert into wm_start_goods set other_goodsNo='{$val}',other_move_goodsNo='{$in['other_move_goodsNo'][$val]}',regDt=sysdate()");
						}else{
							$db->query("update wm_start_goods set other_goodsNo='{$val}',other_move_goodsNo='{$in['other_move_goodsNo'][$val]}' where idx='{$row['idx']}'");
						}
					}
				}
				//2024.10.18웹앤모바일 추가종료
				
				$this->layer("설정되었습니다");
				break;
			case "update_config":
				
				$db->query("delete from wm_start_sms where 1=1");
				//$db->query("insert into wm_start_sms set smsPass='{$in['smsPass']}',smsTemplate='{$in['smsTemplate']}',movie_link='{$in['movie_link']}',regDt=sysdate()");
				
				$db->query("insert into wm_start_sms set smsPass='{$in['smsPass']}',smsTemplate='{$in['smsTemplate']}',movie_link='{$in['movie_link']}',other_smsTemplate='{$in['other_smsTemplate']}',other_movie_link='{$in['other_movie_link']}',regDt=sysdate()");

				$this->layer("설정되었습니다");
				break;
		}
	
	}
}