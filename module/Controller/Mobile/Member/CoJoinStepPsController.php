<?php

namespace Controller\Mobile\Member;

use Request;
use Encryptor;

class CoJoinStepPsController extends \Controller\Mobile\Controller
{
    public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();

		if( !$post['memId'] && !$post['abbott_sno'] ){
			$this->alert('잘못된 접근 입니다.', null, '/member/co_join_stepa.php');
			exit;
		}

        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== START
        $wm = new \Component\Wm\Wm();
        if ($wm->agreementFl) {
            if (empty($post['agreementSf'])) {
                $post['agreementSf'] = 'n';
            }
        }
        // 웹앤모바일 회원 가입 관련 제 3자 정보 제공 동의 추가 ================================================== END

		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->inCoMember($post);
		if($data){	// 성공
			//if(\Request::getRemoteAddress()=="182.216.219.157" || \Request::getRemoteAddress()=="211.49.123.117"){
				$post['memId'] = urlencode(Encryptor::encrypt($post['memId']));
			//}			
			echo '<script>parent.window.location.replace("./co_join_step_end.php?memId='.$post['memId'].'&abbott_sno='.$post['abbott_sno'].'");</script>';
		}else{	// 실패
			echo '<script>parent.alert("일시적인 오류 입니다.\n다시 시도해 주세요");</script>';
		}
		exit;
    }
}