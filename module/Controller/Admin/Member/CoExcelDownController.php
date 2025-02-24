<?php
namespace Controller\Admin\Member;
use Request;
class CoExcelDownController extends \Controller\Admin\Controller {
	public function index(){
		ini_set('memory_limit', '-1');
		set_time_limit(RUN_TIME_LIMIT);
		$dbname = Request::get()->get('dbname');
		$name = $dbname ? $dbname : 'pharmacy';
		$excel = \App::load('Component\\Excel\\ExcelGoodsConvert');
		$this->streamedDownload($name.'_list.xls');
		if ( $name === 'abbottMember' )
			$excel->abbotMember();
		else 
			$excel->coExcel($name);
		exit();
	}
}

