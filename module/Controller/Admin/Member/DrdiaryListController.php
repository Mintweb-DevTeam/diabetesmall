<?php
namespace Controller\Admin\Member;
use Request;
class DrdiaryListController extends \Controller\Admin\Controller
{
    public function index(){
        $this->callMenu('member', 'adela', 'drdiary_list');
		$get = Request::get()->all();
		$get['page'] = $get['page'] ? $get['page'] : 1;
		$get['pageNum'] = $get['pageNum'] ? $get['pageNum'] : 10;
		$this->setData('get', $get);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->drdiary_list($get);
		
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
		
		
    }
}