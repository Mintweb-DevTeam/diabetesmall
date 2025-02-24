<?php
namespace Controller\Front\Goods;

class VideoViewController extends \Controller\Front\Controller
{
	//2024.02.08웹앤모바일 추가
	public function index()
	{
	
		$memNo = \Session::get("member.memNo");
		
		if(empty($memNo)){
		
			echo"<script>alert('로그인후 이용가능합니다.');location.href='../member/login.php';</script>";
		}
			
		$in = \Request::request()->all();
		
				
		/*if(\Request::getRemoteAddress() =="182.216.219.157"){
			$db=\App::load(\DB::class);
			$sql="select * from ".DB_MEMBER." where memNo=?";
			$memInfo = $db->query_fetch($sql,['i',$memNo],false);			
			$in['chkCellPhone']=$memInfo['cellPhone'];
			$this->setData("in",$in);
			$this->setData("cellPhone",$in['chkCellPhone']);
			if(empty($in['chkCellPhone'])){
				echo"<script>alert('정상적인 이용경로가 아닙니다.');document.location.href='/';</script>";
			}
			
		}else{
			*/
			if(empty($in['chkCellPhone'])){
				echo"<script>alert('정상적인 이용경로가 아닙니다.');document.location.href='/';</script>";
			}
			$this->setData("in",$in);
			$this->setData("cellPhone",$in['chkCellPhone']);
		//}
		
		
		$db = \App::load(\DB::class);
		
		$sql = "select * from wm_start_sms";
		$row = $db->fetch($sql);
		
		$this->setData("movie_link",$row['movie_link']);
		
		$buyerInformService = new \Component\Agreement\BuyerInform();
		//$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		//$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
		//$this->setData('privateApprovalOption', $privateApprovalOption);
		//$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);
		
	
	}

}