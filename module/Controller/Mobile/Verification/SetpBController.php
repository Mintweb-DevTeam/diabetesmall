<?php
namespace Controller\Mobile\Verification;
use Component\Cossia\Cossia;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;

class SetpBController extends \Controller\Mobile\Controller
{
	
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = \Request::post()->all();
		if( !$post['cellPhone'] ){
			$this->alert('올바른 접근방법이 아닙니다.',null,'/verification/');
		}
		
		$cossia = new Cossia;
		$sql = 'SELECT * FROM `co_abbottMember` WHERE `memNm` = \''.$post['memNm'].'\' AND `cellPhone` = \''.$cossia->getCellPhone( $post['cellPhone'] ).'\' AND `email` = \''.$post['email'].'\'';
		$data = $this->db->query_fetch($sql, true);
		if(!$data[0]){
			$this->alert('죄송합니다. 해당되는 회원이 아닙니다.',null,'/verification/');
		}
		$buyerInformService = new \Component\Agreement\BuyerInform();
		$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
		$this->setData('privateApprovalOption', $privateApprovalOption);
		$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);
		$this->setData('sno', $data[0]['sno']);
		$this->setData('privateApprovalOptionFl', json_decode( $data[0]['privateApprovalOptionFl'], true ));
		$this->setData('privateConsignFl', json_decode( $data[0]['privateConsignFl'], true ));
		$this->setData('privateOfferFl', json_decode( $data[0]['privateOfferFl'], true ));
		
	}
}
