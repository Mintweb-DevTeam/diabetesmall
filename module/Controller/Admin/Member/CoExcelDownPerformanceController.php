<?php
namespace Controller\Admin\Member;
use Request;
class CoExcelDownPerformanceController extends \Controller\Admin\Controller {
	public function index(){
		ini_set('memory_limit', '-1');
		set_time_limit(RUN_TIME_LIMIT);
		$excel = \App::load('Component\\Excel\\ExcelGoodsConvert');
		$this->streamedDownload('Pharmacy_Performance.xls');
		$excel->coExcel_2(Request::get()->all());
		exit();
	}
}