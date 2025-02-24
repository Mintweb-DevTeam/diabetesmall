<?php
namespace Controller\Front\Goods;

class EventVideoViewController extends \Controller\Front\Controller
{
	//2024.10.18웹앤모바일 추가
	public function index()
	{
	
		$memNo = \Session::get("member.memNo");
		
		if(empty($memNo)){
		
			echo"<script>alert('로그인후 이용가능합니다.');location.href='../member/login.php';</script>";
		}
			
		$in = \Request::request()->all();
		
				
		if(empty($in['chkCellPhone'])){
			echo"<script>alert('정상적인 이용경로가 아닙니다.');document.location.href='/';</script>";
		}
		$this->setData("in",$in);
		$this->setData("cellPhone",$in['chkCellPhone']);
		
		
		$db = \App::load(\DB::class);
		
		$sql = "select * from wm_event_sms";
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