<?php
namespace Controller\Admin\Member;

class BuyMemberDoubleChkController extends \Controller\Admin\Controller
{

	public function index()
	{
		$db = \App::load(\DB::class);
		$in = \Request::post()->toArray();
		
		$memNm=$in['memNm'];
		$cellPhone = str_replace("-",'',$in['cellPhone']);
		
		$return_data=[];
		if(empty($memNm) || empty($cellPhone)){
			$return_data['success']="fail";
			$return_data['member_count']=0;
		}else if(!empty($memNm) && !empty($cellPhone)) {
		
			$where="";
			if(!empty($in['idx'])){
				$where=" and idx != '{$in['idx']}'";
			}
			
			$sql="select count(idx) as cnt from wm_buyGoodsMember where memNm='{$memNm}' and cellPhone='{$cellPhone}'".$where;
			$row = $db->query_fetch($sql);
			
			$cnt = $row[0]['cnt'];
			

			
			if($cnt>0){
				$return_data['success']="fail";
				
			}else{
				$return_data['success']="ok";
				
				$cnt=1;
			}
			
			$return_data['member_count']=$cnt;
		
		}
		
		
		$this->json($return_data);
		
		exit;
	}

}