<?php
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
namespace Controller\Admin\Policy;

use Session;
use Request;
use App;

class WmAgreementController extends \Controller\Admin\Controller
{
    public $db = null;
    public function index()
    {
        $this->callMenu('policy', 'basic', 'wmAgreement');

        $this->db = App::load('DB');
        $row = $this->db->fetch("select * from wm_agreement where sno=1");
        $this->setData('data', stripslashes($row['contents']));
    }
}
// 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END