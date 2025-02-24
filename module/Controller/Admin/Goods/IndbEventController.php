<?php
namespace Controller\Admin\Goods;

class IndbEventController extends \Controller\Admin\Controller
{

	public function index()
	{
		$in = \Request::request()->all();
		
		$db = \App::load(\DB::class);
		
		switch($in['mode']){
			case "update_goods_config_list":
				
				$db->query("delete from wm_event_goods where 1=1");
				
				foreach($in['goodsNo'] as $key => $val){
					
					
					if(!empty($in['chk'][$val])){
						
						$db->query("insert into wm_event_goods set goodsNo='{$val}',regDt=sysdate()");
					
					}
				}
				
				$this->layer("설정되었습니다");
				break;
			case "update_config":
				
				$db->query("delete from wm_event_sms where 1=1");				
				$db->query("insert into wm_event_sms set smsPass='{$in['smsPass']}',smsTemplate='{$in['smsTemplate']}',movie_link='{$in['movie_link']}',regDt=sysdate()");

				$this->layer("설정되었습니다");
				break;
		}
	
	}
}