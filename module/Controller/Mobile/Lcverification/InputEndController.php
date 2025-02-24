<?php
namespace Controller\Mobile\Lcverification;
use Request;
use Exception;
class InputEndController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = \Request::post()->all();
		$sql = 'UPDATE `co_abbottMember` SET `privateApprovalOptionFl` = \''.json_encode($post['privateApprovalOptionFl']).'\', `privateOfferFl` = \''.json_encode($post['privateOfferFl']).'\', `privateConsignFl` = \''.json_encode($post['privateConsignFl']).'\',modDt=sysdate() WHERE `sno` = '.$post['sno'];
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157"){
			
			$strSQL="select * from co_abbottMember where sno='{$post['sno']}'";
			$row = $this->db->fetch($strSQL);
			
			
			$msql="select privateConsignFl from ".DB_MEMBER." where abbott_sno='{$post['sno']}'";
			
			$mrow = $this->db->query_fetch($msql);
			$mdata = json_decode($mrow[0]['privateConsignFl'],true);
			
			if(!empty($mdata[6]))
				$privateConsignFl[6]=$mdata[6];
			else if(empty($mdata[6]))
				$privateConsignFl[6]='n';
			
			$privateConsignFl[20]=$post['privateConsignFl'][20];
			$privateConsignFl[23]=$post['privateConsignFl'][23];
			$json_privateConsignFl = json_encode($privateConsignFl);
			
			$privateOfferFl[5]=$post['privateOfferFl'][5];
			$privateOfferFl[22]=$post['privateOfferFl'][22];			
			$privateOfferFl[25]=$post['privateOfferFl'][25];
			$privateOfferFl[26]=$post['privateOfferFl'][26];
			$json_privateOfferFl = json_encode($privateOfferFl);
			
			
			//$sqls="update ".DB_MEMBER." set privateConsignFl='{$json_privateConsignFl}' where memNm='{$row['memNm']}' and replace(cellPhone,'-','') = replace('{$row['cellPhone']}','-','') and memId='{$row['email']}'";
			$sqls="update ".DB_MEMBER." set privateOfferFl='{$json_privateOfferFl}',privateConsignFl='{$json_privateConsignFl}' where abbott_sno='{$post['sno']}'";
			
			$this->db->query($sqls);
			
			
		//	}
		$result = $this->db->query($sql);
		if($result){
			
			echo '<script>alert("감사합니다. 처리되었습니다.");parent.location.replace("https://www.diabetesmall.co.kr");</script>';
		
		}else{
			echo '<script>alert("일시적 오류 입니다. 다시 시도해 주세요");parent.location.reload();</script>';
		}
		exit;
	}
	
	
}

