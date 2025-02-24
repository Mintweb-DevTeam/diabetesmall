<?php
namespace Controller\Front\Goods;

class AgreeSubController extends \Controller\Front\Controller
{
	//2024.02.08웹앤모바일 추가
	public function index()
	{
		$db = \App::load(\DB::class);
		$in = \Request::post()->toArray();
		
		//gd_debug($in);
		

		$memNo = \Session::get("member.memNo");
	
		if(\Request::getRemoteAddress() =="182.216.219.157"){
		//	$in['cellPhone']=$memInfo[0]['cellPhone'];
		
		//gd_debug($in);
			//exit;
		}
	
		if($in['agree1']!='y' || empty($memNo) || empty($in['cellPhone'])){
			
			
			echo "";
		}else{
			$sql="select * from ".DB_MEMBER." where memNo=?";
			$memInfo = $db->query_fetch($sql,['i',$memNo]);
			
			$cellPhone = str_replace("-",'',$in['cellPhone']);//$memInfo[0]['cellPhone'];
			$email = $memInfo[0]['email'];
			$memNm = $memInfo[0]['memNm'];
			
			$privateConsignFl[20]=$in['privateConsignFl'][20][0];
			$privateConsignFl[23]=$in['privateConsignFl'][23][0];
			
			$abbottMemberData = json_encode($privateConsignFl);
			
			$where ="where memNm='{$memNm}' and REPLACE(cellPhone,'-','')='{$cellPhone}' and email='{$email}'";
			
			$strSQL = "select count(sno) as cnt from co_abbottMember ".$where;
			
			$row = $db->fetch($strSQL);
			
			
			if($row['cnt']>0){
				$sql="update co_abbottMember set privateConsignFl='{$abbottMemberData}',modDt=sysdate() ".$where;
				$db->query($sql);
			}
			
			
			$type = 'sha512';
			$encryptKey = "Vo4YzlGm12";
			$data = $encryptKey.$cellPhone;
			$encCellPhone = hash($type, $data);
		
			$data_arr['name']= $memNm;
			$data_arr['hp']= $cellPhone;
			$data_arr['hpCipher']= $encCellPhone;
			$data_arr['email']= $email;
			$jdata = json_encode($data_arr);
			
			echo"<script>parent.orderSubmit('d','{$jdata}');</script>";
		}
		exit();
	}

}