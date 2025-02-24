<?php
namespace Controller\Mobile\Qrcode;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Request;
use Session;
class OldMemAgreePsController extends \Controller\Mobile\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		if( !$post['cellPhone'] ){
			$this->alert('잘못된 접근 입니다.', null, './co_join_stepa.php');
		}
		$cossia = new \Component\Cossia\Cossia;
		$post['cellPhone'] = $cossia->getCellPhone($post['cellPhone']);
		if( $post['cellPhone'] === false ){
			echo '<script>parent.alert("전화번호가 이상합니다.");</script>';
			exit;
		}
		$data = $cossia->abbottCheck($post['cellPhone']);
		if($data['code'] == 'isMem' || $data['code'] == 'isOk'){
			$url = ( $data['pharmacy_code'] == 'TRYNOWevent' && Session::get('join_view') == '1' ) ? 'http://www.cgmedu.co.kr/_online1' : './co_join_stepe.php?&sno='.$data['sno'].'&type=uze';
			Session::set('join_view', '2');
			echo '<script>parent.window.location.replace("'.$url.'");</script>';
		}/*else if ( $data['code'] == 'isOk' ){
			echo '<script>parent.window.location.replace("./co_join_stepe.php?&sno='.$data['sno'].'&type=uze");</script>';
		}*/else if(  $data['code'] == 'isNot' ){
			echo '<script>parent.alert("등록된 정보가 없습니다.");parent.window.location.replace("./co_join_stepb.php?pharmacy_code='.$post['pharmacy_code'].'");</script>';
		}
		exit;
    }
}