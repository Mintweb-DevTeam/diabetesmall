<?php
namespace Controller\Front\Wm;

use Exception;
class WmTestController extends \Controller\Front\Controller
{
	
	protected $db;
	protected $sheet;

	
	public function index()
	{

		set_time_limit(0);
		
		$buyObj = new \Component\Wm\BuyCheck();
		$k=$buyObj->goodsBuyChk("1000000078");
		
		gd_debug($k);
		
        //$obj = \App::load(\Component\Subscription\Subscription::class);
        		
		/*$server = \Request::server()->toArray();
		
		$this->db = \App::Load('DB');
		$file_name = $server['DOCUMENT_ROOT']."/data/wm_upload/aa.xls";//explode(".",$filesValue['excelUploadFile']['name']);
		
		$xls = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
	    //$chk = $xls->canRead($file_name);
		$xls->setReadDataOnly(true);
	    $this->sheet = $xls->setReadEmptyCells(false)->load($file_name)->getActiveSheet();
	
		$this->read_excel();
		*/
		exit;
		
	}
	
	protected function read_excel()
	{
		
		$subObj = new \Component\Subscription\Subscription();
		
		$rows = $this->sheet->getRowIterator(2);//몇번째 열부터 읽을 것인지
		$stamp = strtotime(date("Ymd"));
		
		foreach ($rows as $row) {
			$rowData=[];
			
			//각 셀 데이터 
			foreach ($row->getCellIterator() as $cell) {
				  
				  $rowData[] = $cell->getValue();
				 
			 }
			 
			
			 foreach($rowData as $key => $v){
				 
				 if($v != "2305171852489902"){
					//gd_debug($v);
					
					$sqls="select * from wm_subscription_apply where uid='$v'";
					$info=$this->db->fetch($sqls);
					
					$sql = "SELECT COUNT(idx) as cnt FROM wm_subscription_schedule_list WHERE uid='{$v}' AND schedule_stamp >= {$stamp} AND isPayed='0' AND isStop='0'";
					$row = $this->db->fetch($sql);

							
					if (!empty($row['cnt'])) {
						$ea = $info['deliveryEa']-6;
						if($ea>0){
							gd_debug($v);
							gd_debug($info['deliveryEa']);
							//$subObj->extendSchedule($v,$ea,$info['period']);
						}
										 
					}
					
				 
				 }
			 }
			 
			// gd_debug("end");
		}	
	}

}