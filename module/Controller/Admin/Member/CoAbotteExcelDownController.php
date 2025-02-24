<?php
namespace Controller\Admin\Member;
use Request;
class CoAbotteExcelDownController extends \Controller\Admin\Controller {
	public function index(){
		ini_set('memory_limit', '-1');
		set_time_limit(RUN_TIME_LIMIT);
		$get = Request::get()->all();
		$excel = \App::load('Component\\Excel\\ExcelGoodsConvert');
		$name = $get['type'] ?? 'member';
		$this->streamedDownload($name.'_list.xls');
		switch($get['type']){
			case 'order' :
				$excel->abbotOrder($get);
			break;
			default :
				$excel->abbotMember2($get);
		}
		exit();
	}
}