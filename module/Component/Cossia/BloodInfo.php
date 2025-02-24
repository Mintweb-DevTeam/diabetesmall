<?php
namespace Component\Cossia;
use Session;
use Request;
class BloodInfo {

	public function __construct(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
	}
	
	public function setInfo(array $data, array $fild = []) : int
	{
		foreach( $data as $key => $val ){
			$fild[] = ' `'.$key.'` = \''.$val.'\' ';
		}
		$fild[] = ' `controlNo` = \'AK'.sprintf('%010d', $data['memNo']).'\' ';
		$sql = 'INSERT INTO `co_bloodInfo` SET '.implode(', ', $fild);
		$this->db->query($sql);
		return $this->db->query_fetch('SELECT last_insert_id() as `sno`', true)[0]['sno'];
	}
	
	public function fileUploaded(int $sno, $file, bool $result = false)
	{
		$sql = 'SELECT `controlNo` FROM `co_bloodInfo` WHERE `sno` = '.$sno;
		$controlNo = $this->db->query_fetch($sql, true)[0]['memNo'].date('ymdHis');
		$target = '/data/blood_file/'.$controlNo.'.csv';
		
		if( move_uploaded_file( $file['tmp_name'], Request::server()->get('DOCUMENT_ROOT').$target) ){
			$sql = 'UPDATE `co_bloodInfo` SET `fileInfo` = \''.json_encode(['name'=>$file['name'], 'target'=>$target], JSON_UNESCAPED_UNICODE).'\' WHERE `sno` = '.$sno;
			$result = $this->db->query($sql);
		}
		return $result;
	}
	
	public function fileConverting(int $sno) : bool
	{
		ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);
		$sql = 'SELECT `fileInfo` FROM `co_bloodInfo` WHERE `sno` = '.$sno;
		$controlNo = json_decode( $this->db->query_fetch($sql, true)[0]['fileInfo'], true );
		$xls = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
		$fileNm = Request::server()->get('DOCUMENT_ROOT').$controlNo['target'];
		$chk = $xls->canRead( $fileNm );
		$xls->setReadDataOnly(true);
		$this->sheet = $xls->setReadEmptyCells(false)->load( $fileNm )->getActiveSheet();
		$max_ = $this->sheet->getHighestRow();
		$sheetData = $this->sheet->toArray();
		$fileData = [];
		for( $i=4; $i<$max_; $i++ ){
			// $sheetData[$i][2] 날짜  | $sheetData[$i][3] 0인것만  | $sheetData[$i][4] 측정값
			if( $sheetData[$i][3] == '0' ){
				$dateTime = explode(' ', $sheetData[$i][2]);
				$fileData[] = ['date'=>$dateTime[0], 'time'=>str_pad($dateTime[1], 5, '0', STR_PAD_LEFT).':00', 'mg'=>$sheetData[$i][4]];
			}
		}
		$sql = 'UPDATE `co_bloodInfo` SET `fileData` = \''.json_encode($fileData, JSON_UNESCAPED_UNICODE).'\' WHERE `sno` = '.$sno;
		return $this->db->query($sql);
	}
	

}


