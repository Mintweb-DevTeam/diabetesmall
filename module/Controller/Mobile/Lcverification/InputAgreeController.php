<?php
namespace Controller\Mobile\Lcverification;
use Request;
use Component\Cossia\Cossia;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
class InputAgreeController extends \Controller\Mobile\Controller {
    public function index(){
		
		/*
		$post = Request::post()->all();
		if( !$post['name'] ){
			//$this->alert('잘못된 접근 입니다.', null, './index.php');
		}
		$this->setData('post', $post);
		
		$db = \App::load(\DB::class);
		$sql="select * from co_abbottMember where REPLACE(cellPhone,'-','')=?";
		$row = $db->query_fetch($sql,['s',$post['phone']]);
				
		$privateApprovalOptionFl	= json_decode($row[0]['privateApprovalOptionFl']);//개인정보 수집 및 이용선택
		
		
		$privateApprovalOptionFlArr=[];
		foreach($privateApprovalOptionFl as $key => $f){
			$privateApprovalOptionFlArr[$key]=$f;
		}
		$this->setData("privateApprovalOptionFl",$privateApprovalOptionFlArr);

		
		$privateConsignFl			= json_decode($row[0]['privateConsignFl']);//개인정보동의
		//$this->setData("privateConsignFl",$privateConsignFl);
		
		$privateConsignFlArr=[];
		foreach($privateConsignFl as $key => $f){
			$privateConsignFlArr[$key]=$f;
		}
		$this->setData("privateConsignFl",$privateConsignFlArr);
		
		
		$privateOfferFl				= json_decode($row[0]['privateOfferFl']);//제3자제공동의 
		
		$privateOfferFlArr=[];
		foreach($privateOfferFl as $key => $f){
			$privateOfferFlArr[$key]=$f;
		}
		$this->setData("privateOfferFl",$privateOfferFlArr);
		
		//$this->getView()->setDefine('header', 'lcverification/_header.html');
		//$this->getView()->setDefine('footer', 'lcverification/_footer.html');
		
		$this->getView()->setDefine('agree1', 'lcverification/_agree1.html');
		$this->getView()->setDefine('agree2', 'lcverification/_agree2.html');
		$this->getView()->setDefine('agree3', 'lcverification/_agree3.html');
		$this->getView()->setDefine('agree4', 'lcverification/_agree4.html');
		$this->getView()->setDefine('agree5', 'lcverification/_agree5.html');
		
		*/
		
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = \Request::post()->all();
		if( !$post['cellPhone'] ){
			$this->alert('올바른 접근방법이 아닙니다.',null,'/lcverification/');
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