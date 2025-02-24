<?php
namespace Controller\Admin\Goods;

class StartSmsSetController extends \Controller\Admin\Controller
{

	public function index()
	{
        $this->callMenu('goods', 'start_goods', 'start_sms_set');
	
		$db =\App::load(\DB::class);
		
		$sql="select * from wm_start_sms";
		$row = $db->fetch($sql);
		
		$this->setData($row);
		
	}

}