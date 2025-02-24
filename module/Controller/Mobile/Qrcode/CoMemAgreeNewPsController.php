<?php
namespace Controller\Mobile\Qrcode;
use Request;
use Cookie;
class CoMemAgreeNewPsController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::request()->all();
		$sql = 'UPDATE `co_abbottMember` SET `privateApprovalOptionFl` = \''.json_encode($post['privateApprovalOptionFl']).'\', `privateOfferFl` = \''.json_encode($post['privateOfferFl']).'\', `privateConsignFl` = \''.json_encode($post['privateConsignFl']).'\' WHERE `sno` = '.$post['sno'];
		$result = $this->db->query($sql);
		$this->alert('처리되었습니다.', null, './co_join_stepe.php?text=uze&sno='.$post['sno']);
		exit;
	}
}