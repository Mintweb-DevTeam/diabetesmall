<?php
namespace Controller\Admin\Goods;

class EventSmsSetController extends \Controller\Admin\Controller
{

	public function index()
	{
        $this->callMenu('goods', 'event_goods', 'event_sms_set');
	
		$db =\App::load(\DB::class);
		
		$sql="select * from wm_event_sms";
		$row = $db->fetch($sql);
		
		$this->setData($row);
		
	}

}