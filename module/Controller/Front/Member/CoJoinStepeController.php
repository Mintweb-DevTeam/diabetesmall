<?php
namespace Controller\Front\Member;

use Request;
use Encryptor;

class CoJoinStepeController extends \Controller\Front\Controller
{
	
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$get = Request::get()->all();
		if( !$get['sno'] && !$get['name'] ){
			$this->alert('잘못된 접근 입니다.', null, '/member/co_join_stepa.php');
			exit;
		}
		$cossia = new \Component\Cossia\Cossia;
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
			$get['name'] = Encryptor::decrypt($get['name']);
		//}
		
		
		$data = $cossia->getAbbottMember($get);
		if($data['isJoin'] == 'y'){
			$this->alert('이미 회원가입을 하셨습니다.', null, '/');
			exit;
		}
		
		$this->setData('data', $data);
		$buyerInformService = new \Component\Agreement\BuyerInform();
		$agreementInfo = $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::AGREEMENT);
        $privateApproval = $buyerInformService->getInformData(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL);
		$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');

		$this->setData('agreementInfo', $agreementInfo);
		$this->setData('privateApproval', $privateApproval);
		$this->setData('privateApprovalOption', $privateApprovalOption);
		$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);

        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {
            $privateInfo = $buyerInformService->getAgreementWithReplaceCode(\Component\Agreement\BuyerInformCode::BASE_PRIVATE);
            $this->setData('privateInfo', $privateInfo);
            $this->setData('wmAgreement', true);
            $this->setData('spInfo', $wm->getAgreementInfo());
        }
        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END
    }
}