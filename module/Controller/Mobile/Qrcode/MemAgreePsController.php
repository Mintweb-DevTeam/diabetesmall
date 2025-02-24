<?php
namespace Controller\Mobile\Qrcode;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Request;
class MemAgreePsController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		if( !$post['memNm'] || !$post['cellPhone'] || !$post['memId'] || !$post['memPw'] ){
			$this->alert('잘못된 접근 입니다.', null, '/Qrcode/');
		}
		$sql = 'INSERT INTO `co_abbottMember` SET `memNm` = \''.$post['memNm'].'\', `cellPhone` = \''.$post['cellPhone'].'\', `email` = \''.$post['memId'].'\' ';
		$sql .= ($post['pharmacy_code']) ? ', `pharmacy_code` = \''.$post['pharmacy_code'].'\' ' : ', `pharmacy_code` = NULL ';
		
		$inform = new BuyerInform();
        $privateApprovalOption = $inform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION);
        $privateConsign = $inform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN);
        $privateOffer = $inform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER);
		
		if($privateApprovalOption)
			foreach($privateApprovalOption as $row){
				$privateApprovalOptionFl[ $row['sno'] ] = ( $post['privateApprovalOption'][ $row['sno'] ] == 'y' ) ? 'y' : 'n';
			}
		
		if($privateConsign)
			foreach($privateConsign as $row){
				$privateConsignFl[ $row['sno'] ] = ( $post['privateConsign'][ $row['sno'] ] == 'y' ) ? 'y' : 'n';
			}
		
		if($privateOffer)
			foreach($privateOffer as $row){
				$privateOfferFl[ $row['sno'] ] = ( $post['privateOffer'][ $row['sno'] ] == 'y' ) ? 'y' : 'n';
			}
			
		if( is_array( $privateApprovalOptionFl ) ) $sql_[] = ' `privateApprovalOptionFl` = \''.json_encode($privateApprovalOptionFl).'\' ';
		if( is_array( $privateConsignFl ) ) $sql_[] = ' `privateConsignFl` = \''.json_encode($privateConsignFl).'\' ';
		if( is_array( $privateOfferFl ) ) $sql_[] = ' `privateOfferFl` = \''.json_encode($privateOfferFl).'\' ';
		 
		$sql_1 = (is_array($sql_)) ? implode(',', $sql_) : '';
		$sql .= isset($sql_1) ? ', '.$sql_1 : '';
		$this->db->query($sql);
		echo '<script>parent.window.location.replace("./complete.php?type=success&memId='.$post['memId'].'&cellPhone='.$post['cellPhone'].'&memNm='.$post['memNm'].'&memPw='.$post['memPw'].'");</script>';
		exit;
    }
}