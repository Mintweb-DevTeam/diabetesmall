<?php
namespace Controller\Admin\Member;
use Request;
class CoExcelDownPcodeController extends \Controller\Admin\Controller {
	public function index(){
		ini_set('memory_limit', '-1');
		set_time_limit(RUN_TIME_LIMIT);
		$excel = \App::load('Component\\Excel\\ExcelGoodsConvert');
		$this->streamedDownload('Pharmacy_Detail.xls');
		$excel->coExcel_3(Request::get()->all());
		exit();
	}
}