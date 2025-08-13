<?php

namespace Controller\Front\Service;

use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Request;

class KakaoMarketingOptController extends \Controller\Front\Controller
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

        if(\Request::isMobileDevice()) {
            echo "
            <script>
            location.href='https://m.diabetesmall.co.kr/service/kakao_marketing_opt.php';
            </script>
            ";
        }
    }
}
