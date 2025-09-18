<?php
namespace Controller\Admin\Member;
use Request;
class AbotteListController extends \Controller\Admin\Controller {
    public function index(){
        $this->callMenu('member', 'adela', 'abotte_list');
		$get = Request::get()->all();
		$get['page'] = $get['page'] ? $get['page'] : 1;
		$get['pageNum'] = $get['pageNum'] ? $get['pageNum'] : 10;
		$this->setData('get', $get);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->abbott_list($get);
		
	
		$this->setData('list', $data['data']);
		$this->setData('page', $data['page']);
		$this->setData('total', $data['total']);
		$buyerInformService = new \Component\Agreement\BuyerInform();
		$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
		$this->setData('privateApprovalOption', $privateApprovalOption);
		$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);

        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== START
        $wmSalesforce = new \Component\Wm\WmSalesforce();
        if ($wmSalesforce->applyFl) {
            $this->setData('wmSalesforce', true);
        }
        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== END
    }
}