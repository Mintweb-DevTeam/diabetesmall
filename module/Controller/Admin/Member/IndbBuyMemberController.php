<?php

namespace Controller\Admin\Member;


use Request;
use App;


class IndbBuyMemberController extends \Controller\Admin\Controller
{
	private $db="";
	private $managerNo="";
	private $ip="";
	protected $sheet;
	
	public function index()
	{
	
		$this->db = App::load(\DB::class);
		$param = Request::request()->all();
		$param['memNm'] =trim($param['memNm']);
		
		$mode = $param['mode'];
		
		$this->managerNo=\Session::get('manager.sno');
		$this->ip=\Request::getRemoteAddress();
		
		$managerNo=$this->managerNo;
		$ip=$this->ip;

		switch($mode){
			case "excel":
				
				$result=[];
			    $request = App::getInstance('request');
				$filesValue = $request->files()->toArray();
				
				$xls = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
				$chk = $xls->canRead($filesValue['upload_excel']['tmp_name']);
				
				if ($chk === false) {
					$this->layer("등록할 엑셀파일이 없습니다.");
				}else{
					$xls->setReadDataOnly(true);
					$this->sheet = $xls->setReadEmptyCells(false)->load($filesValue['upload_excel']['tmp_name'])->getActiveSheet();
					$r = $this->read_excel();
					
					if($r>0){
						$this->layer($r."건 등록 되었습니다.",'parent.window.location=\'../member/buy_member_list.php\'');
					}else{
						$this->layer("엑셀등록 실패입니다.");
					}
				}
				
				break;
			case"delete":
				
				//데이터 삭제
				$result=[];
				
				if(empty($param['idx'])){
					$result['result']=0;
				}else{
					
					$this->BuyMemberLog($param['idx'],"delete");
											
					
					$sql="delete from wm_buyGoodsMember where idx='{$param['idx']}'";
					$this->db->query($sql);
					
					$result['result']=1;
				}
				
				$this->json($result);
				
				
				break;
			case "loop_delete":
				
				if(count($param['chk'])>0){
					foreach($param['chk'] as $key => $idx){
						$this->BuyMemberLog($idx,"delete");
						
						$sql="delete from wm_buyGoodsMember where idx='{$idx}'";
						$this->db->query($sql);

					}
					
					$this->layer("삭제 되었습니다.");
				}else{
				
				}
				break;
			default:
				if(!empty($param['memNm']) && !empty($param['cellPhone'])){
					if(!empty($param['idx'])){
						//데이터 업데이트
						
						$this->BuyMemberLog($param['idx'],"update");
						
						$sql="update wm_buyGoodsMember set memNm='{$param['memNm']}',cellPhone='{$param['cellPhone']}',mod_ip='{$ip}',mod_managerSno='{$managerNo}',modDt=sysdate() where idx='{$param['idx']}'";
					}else{
						//데이터 신규등록
						$sql="insert into wm_buyGoodsMember set memNm='{$param['memNm']}',cellPhone='{$param['cellPhone']}',ip='{$ip}',managerSno='{$managerNo}'";
					}
					
					$this->db->query($sql);
					

					$this->layer("처리되었습니다.",'parent.window.location=\'../member/buy_member_list.php\'');
				}else{
					$this->layer("등록정보가 없습니다.");
				}
				break;
		
		}
	
		exit();
	}
	
	//업데이트 및 삭제시 로그처리함수
	private function BuyMemberLog($idx="",$gubun="delete")
	{
	
		if(empty($idx))
			return false;
		
		$managerNo=$this->managerNo;
		$ip=$this->ip;
		
		$data=[];
		$sql="select * from wm_buyGoodsMember where idx='{$idx}'";
		$row = $this->db->query_fetch($sql);
		$data['idx']=$row[0]['idx'];
		$data['memNm']=$row[0]['memNm'];
		$data['cellPhone']=$row[0]['cellPhone'];
		
		$jData = addSlashes(json_encode($data,JSON_UNESCAPED_UNICODE));
		
		$logSQL="insert into wm_buyGoodsMemberLog set gubun='{$gubun}',gubun_data='{$jData}',ip='{$ip}',managerNo='{$managerNo}'";
		$this->db->query($logSQL);
		
		
	}
	
	//엑셀 리딩 함수
	protected function read_excel()
	{
		
		$managerNo=$this->managerNo;
		$ip=$this->ip;
		
		$rows = $this->sheet->getRowIterator(3);//3번째 행부터 
	
		$rowDatas = [];
		
		foreach ($rows as $row) {
		   
		    $rowDatas[] = [];
		    
		    foreach ($row->getCellIterator() as $cell) {
		        
		        $rowDatas[count($rowDatas) - 1][] = $cell->getValue();
		    }
		}	
		
		$insertCount=0;
		foreach ($rowDatas   as $rowKey => $row) {

			$memNm = trim($row[0]);
			$cellPhone = str_replace("-",'',$row[1]);
			
			if(!empty($memNm) && !empty($cellPhone)){
				
				$sql="select count(idx) as cnt from wm_buyGoodsMember where memNm='{$memNm}' and cellPhone='{$cellPhone}'";
				$row = $this->db->query_fetch($sql);
				
				if(empty($row[0]['cnt'])){
					$sql="insert into wm_buyGoodsMember set memNm='$memNm',cellPhone='$cellPhone',ip='$ip',managerSno='$managerNo'";
					$this->db->query($sql);
					$insertCount++;
				}
			}
			
		}
		
		return $insertCount;
	
	}	

}