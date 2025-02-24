<?php
namespace Controller\Admin\Member;
use Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BloodDataDownController extends \Controller\Admin\Controller {
    public function index(){
		
		ini_set('memory_limit', '-1');
		set_time_limit(RUN_TIME_LIMIT);
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$sql = 'SELECT * FROM `co_bloodInfo` WHERE `sno` = '.Request::get()->get('sno');
		$data = $this->db->query_fetch($sql, true)[0];
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$head = ['관리번호','성명','생년월일','성별','측정일자','측정시간','측정혈당값'];
		for( $i = 'A', $j = 0; $i < 'H'; $i++, $j++ ){
			$sheet->setCellValue($i.'1', $head[$j]);
		}
		
		$fileData = json_decode($data['fileData'], true);
		$gender = ($data['gender'] == 'm') ? '남' : '여';
		foreach($fileData as $key => $row){
			$sheet->setCellValueExplicit('A'.($key + 2), $data['controlNo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			$sheet->setCellValue('B'.($key + 2), $data['patientName']);
			$sheet->setCellValue('C'.($key + 2), $data['patientBirth']);
			$sheet->setCellValue('D'.($key + 2), $gender);
			$sheet->setCellValue('E'.($key + 2), $row['date']);
			$sheet->setCellValue('F'.($key + 2), $row['time']);
			$sheet->setCellValueExplicit('G'.($key + 2), $row['mg'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		}
		
		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($data['patientName'].'_'.date('Yms').'.xlsx').'"');
        $writer->save('php://output');
		exit;
	}
	
}