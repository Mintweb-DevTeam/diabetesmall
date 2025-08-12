<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Controller\Admin\Policy;

use Session;
use Request;
use App;

class WmAgreementPsController extends \Controller\Admin\Controller
{
    public $db = null;
    public function index()
    {
        $postValue = Request::post()->toArray();
        
        $this->db = App::load('DB');
        $this->db->query("update wm_agreement set contents='" . addslashes($postValue['contents']) . "', managerNo='" . Session::get('manager.sno') . "' where sno=1");
        $this->layer('저장되었습니다.');exit;
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END