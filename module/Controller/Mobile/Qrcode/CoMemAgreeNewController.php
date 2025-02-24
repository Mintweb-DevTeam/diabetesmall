<?php
namespace Controller\Mobile\Qrcode;
use Request;
use Cookie;
class CoMemAgreeNewController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$cookie = Cookie::all();
		
		$cossia = new \Component\Cossia\Cossia;
		
		$sql = 'SELECT `sno`, `privateApprovalOptionFl`, `privateOfferFl`, `privateConsignFl` FROM `co_abbottMember` WHERE `memNm` = \''.$cookie['memNm'].'\' AND `cellPhone` = \''.$cossia->getCellPhone($cookie['cellPhone']).'\' AND `email` = \''.$cookie['memId'].'\'';
		$data = $this->db->query_fetch($sql, true)[0];
		if($data)
			foreach($data as $k => $r)
				$data[$k] = json_decode($r, true);
		
		$this->setData('data', $data);
		
		$buyerInformService = new \Component\Agreement\BuyerInform();
		$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
		$this->setData('privateApprovalOption', $privateApprovalOption);
		$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);
    }
}

