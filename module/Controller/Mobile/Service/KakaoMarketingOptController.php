<?php

namespace Controller\Mobile\Service;

use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Request;


class KakaoMarketingOptController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $buyerInformService = new \Component\Agreement\BuyerInform();
        $privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
        $privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
        $privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
        $this->setData('privateApprovalOption', $privateApprovalOption);
        $this->setData('privateOffer', $privateOffer);
        $this->setData('privateConsign', $privateConsign);
        
    }
}
