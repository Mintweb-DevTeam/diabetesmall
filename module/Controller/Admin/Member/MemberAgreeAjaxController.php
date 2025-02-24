<?php
namespace Controller\Admin\Member;
use Request;
class MemberAgreeAjaxController extends \Controller\Admin\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
        $post = Request::post()->all();
		foreach($post as $key => $val){
			if($key != 'memNo' && $key != 'type' && $key != 'sno') $sql_[] = ' `'.$key.'` = \''.json_encode($val).'\' ';
		}
		
		
		//2024.02.20웹앤모바일 추가시작
		
	
		if(count($sql_)>0)
			$modDt=",modDt=sysdate()";
		else
			$modDt="modDt=sysdate()";
		//2024.02.20웹앤모바일 추가종료
		
		switch($post['type']){
			
			
			case 'abotte':
				$sql = 'UPDATE `co_abbottMember` SET '.implode(',', $sql_).$modDt.' WHERE `sno` = \''.$post['sno'].'\'';
				break;
			default :
				
				//2024.02.21웹앤모바일 추가시작
				$data_arr=array(19=>"privateApprovalOptionFl",21=>"privateApprovalOptionFl",20=>"privateConsignFl",23=>"privateConsignFl",22=>"privateOfferFl",25=>"privateOfferFl",26=>"privateOfferFl");
				
				foreach($data_arr as $key => $v){
					
					if(!empty($post[$v][$key]))
						$data[$v][$key]=$post[$v][$key];
					else
						$data[$v][$key]='n';
					
				}
				
				$privateApprovalOptionFl = json_encode($data['privateApprovalOptionFl']);
				$privateConsignFl = json_encode($data['privateConsignFl']);
				$privateOfferFl = json_encode($data['privateOfferFl']);
								
				$sql="select * from ".DB_MEMBER." where memNo='{$post['memNo']}'";
				$row = $this->db->fetch($sql);
				
				$coSQL="update co_abbottMember set privateApprovalOptionFl='{$privateApprovalOptionFl}',privateConsignFl='{$privateConsignFl}',privateOfferFl='{$privateOfferFl}',modDt=sysdate() where email='{$row['memId']}' and REPLACE(cellPhone,'-','')=REPLACE('{$row['cellPhone']}','-','')";	
				
				$this->db->query($coSQL);
				//2024.02.21웹앤모바일 추가종료
				
				$sql = 'UPDATE `es_member` SET '.implode(',', $sql_).' WHERE `memNo` = \''.$post['memNo'].'\'';
				break;
		}

		
		$this->db->query($sql);
        exit;
    }
}
