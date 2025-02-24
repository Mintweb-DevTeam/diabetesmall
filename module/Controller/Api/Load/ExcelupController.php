<?php
namespace Controller\Api\Load;

use App;
use Request;
use Session;
use Component\Mall\Mall;

class ExcelupController extends \Controller\Api\Controller
{

	protected $sheet;
	protected $sql="";
	public function index()
	{
		
		//2024.01.11웹앤모바일 엑셀데이터등록
		exit;
		$db = \App::Load('DB');
		$this->sql="insert into co_abbottMember(memNm,cellPhone,email,privateApprovalOptionFl,privateConsignFl,privateOfferFl,pharmacy_code,device,regDt)values";
		$xls = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
		
		$server = \Request::server()->toArray();

	    $chk = $xls->canRead($server['CONTEXT_DOCUMENT_ROOT']."/data/upfile/20240109.xls");
		
		$xls->setReadDataOnly(true);
	    $this->sheet = $xls->setReadEmptyCells(false)->load($server['CONTEXT_DOCUMENT_ROOT']."/data/upfile/20240109.xls")->getActiveSheet();
		$this->read_excel();
		
		$sql = substr($this->sql, 0, -1);
		//$db->query($sql);
		echo $sql;
		exit();
	}

	//열
	
	protected function read_excel()
	{
		$rows = $this->sheet->getRowIterator(2);//몇번째 열부터 읽을 것인지
		
		$row_count = 0;
		
		while ($rows->valid()) {
		    $row = $rows->current();
			$this->processCells($row,$row_count);
		    $rows->next();
			$row_count++;//열카운트
		}
		return true;
	}
	protected function processCells($row,$row_count=0)
	{
        $cells = $row->getCellIterator();
		$idx = 0;//행카운트
		
		//memNm,cellPhone,email,privateApprovalOptionFl,privateConsignFl,privateOfferFl,pharmacy_code,device,regDt
		$insert_values="(";
		
		$privateApprovalOptionFlArr=[];
		
		$privateConsignFlArr=[];
		$privateOfferFlArr=[];
		
		while ($cells->valid() && $failMsg === null) 
		{
            $cell = $cells->current();
            $value = trim($cell->getValue());
			
			if($idx ==0)
				$insert_values.="'".$value."'";
			else if($idx ==1 || $idx==2 || $idx==7 || $idx==9){
				
				if($idx==9){
					
					$excelDateValue = $value;  // Replace this with your Excel date value

					$excelBaseDate = 1900;

					$unixTimestamp = ($excelDateValue - 25569) * 86400;

					$date = new \DateTime();
					$date->setTimestamp($unixTimestamp);

					$formattedDate = $date->format('Y-m-d H:i:s');
					$insert_values.=",'".$formattedDate."'";
				}else if($idx == 1){
					$tmpvalue='0'.$value;
					$valueArr[]=substr($tmpvalue,0,3);
					$valueArr[]=substr($tmpvalue,3,4);
					$valueArr[]=substr($tmpvalue,7,11);
					
					$insert_values.=",'".implode("-",$valueArr)."'";
					
				}else{
					$insert_values.=",'".$value."'";
				}
			
			}else if($idx==3){
				$privateApprovalOptionFlArr[19]='y';
				$privateApprovalOptionFlArr[21]='y';
				
				$privateApprovalOptionFl= json_encode($privateApprovalOptionFlArr);
				$insert_values.=",'".$privateApprovalOptionFl."'";
				
				$privateConsignFlArr[20]=$value;
			}else if($idx==4){
				$privateConsignFlArr[23]=$value;
				$privateConsignFl= json_encode($privateConsignFlArr);
				$insert_values.=",'".$privateConsignFl."'";
				
			}else if($idx==5){
				$privateOfferFlArr[5]='y';
				$privateOfferFlArr[22]='y';
				$privateOfferFlArr[25]=$value;
			}else if($idx==6){
				$privateOfferFlArr[26]=$value;
				
				$privateOfferFl= json_encode($privateOfferFlArr);
				$insert_values.=",'".$privateOfferFl."'";
				
			}else if($idx==8){
				$insert_values.=",'".strtolower('qr')."'";
			}
			
            $cells->next();
			$idx++;

		}
		$insert_values.=")";
		
		//$sql=$this->sql;
		
		$this->sql.=$insert_values.",";
		
	}
}