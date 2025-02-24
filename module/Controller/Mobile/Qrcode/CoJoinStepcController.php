<?php
namespace Controller\Mobile\Qrcode;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Request;
class CoJoinStepcController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		if( !$post['memNm'] || !$post['cellPhone'] || !$post['memId'] ){
			$this->alert('잘못된 접근 입니다.', null, './join_is_abbott.php');
		}
		$cossia = new \Component\Cossia\Cossia;
		$post['cellPhone'] = $cossia->getCellPhone($post['cellPhone']);
		if( $post['cellPhone'] === false ){
			echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
			exit;
		}
		$this->setData('post', $post);
		$buyerInformService = new \Component\Agreement\BuyerInform();
		$privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content');
		$privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content');
		$privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content');
		$this->setData('privateApprovalOption', $privateApprovalOption);
		$this->setData('privateOffer', $privateOffer);
		$this->setData('privateConsign', $privateConsign);
		
		
		//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="112.146.205.124" || \Request::getRemoteAddress()=="211.49.123.117"){
			
			if(!empty($post['pharmacy_code'])){
			
				$db		= \App::load(\DB::class);
				$sql	= "select count(sno) as cnt from co_pharmacy where code=?";
				$row	= $db->query_fetch($sql,['s',$post['pharmacy_code']]);
				
				if(empty($row[0]['cnt'])){
					echo '<script>parent.alert("추천인 정보를 확인해주세요.");history.back();</script>';
					exit;
				}
				
			}
			$this->getView()->setPageName('qrcode/co_join_stepc2.php');
		//}
    }
}