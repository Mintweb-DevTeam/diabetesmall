<?php
namespace Controller\Admin\Member;
use Request;
class ExcelReadController extends \Controller\Admin\Controller {
	public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$result = null;
		$filde = ['sno','code','name','phone','post','address1','address2'];
		$snos = [];
		$request = \App::getInstance('request');
		$files = $request->files()->toArray();
		$xls = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
		$chk = $xls->canRead($files['excel']['tmp_name']);
		if($chk){
			$this->sheet = $xls->setReadEmptyCells(false)->load($files['excel']['tmp_name'])->getActiveSheet();
			
			$max_ = $this->sheet->getHighestRow();
			$sheetData = $this->sheet->toArray();
			foreach($sheetData[0] as $k => $v){
				if( in_array($v, $filde) ) $finaFild[$v] = $k;
			}
			for($i=1; $i<$max_; $i++){
				$data[($i-1)]['sno'] = $sheetData[$i][ $finaFild['sno'] ];
				if( isset($sheetData[$i][ $finaFild['sno'] ]) ){
					$data[($i-1)]['snoover'] = in_array($sheetData[$i][ $finaFild['sno'] ], $snos);
					$snos[] = $sheetData[$i][ $finaFild['sno'] ];
				}
				$data[($i-1)]['code'] = $sheetData[$i][ $finaFild['code'] ];
				$data[($i-1)]['codeover'] = $this->code_overlap( $sheetData[$i][ $finaFild['code'] ] );
				$data[($i-1)]['name'] = $sheetData[$i][ $finaFild['name'] ];
				$data[($i-1)]['phone'] = $sheetData[$i][ $finaFild['phone'] ];
				$data[($i-1)]['post'] = $sheetData[$i][ $finaFild['post'] ];
				$data[($i-1)]['address1'] = $sheetData[$i][ $finaFild['address1'] ];
				$data[($i-1)]['address2'] = $sheetData[$i][ $finaFild['address2'] ];
			}
			$result = array('code'=>200, 'data'=>$data);
		}else {
			$result = array('code'=>400, 'msg'=>'읽을 수 없는 파일 입니다.');
		}
		echo json_encode($result);
		exit;
	}
	public function code_overlap($code){
		$sql = 'SELECT COUNT(*) AS `cnt` FROM `co_pharmacy` WHERE `code` = \''.$code.'\'';
		$cnt = $this->db->query_fetch($sql, true);
		if($cnt[0]['cnt'] != 0) return 'f';
		else return 't';
	}
	
	
}