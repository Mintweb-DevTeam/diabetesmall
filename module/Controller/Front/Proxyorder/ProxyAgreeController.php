<?php
namespace Controller\Front\Proxyorder;

use App;
use Request;

class ProxyAgreeController extends \Controller\Front\Controller
{

	public function index()
	{
	
		$session = \Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
			exit;
		}

		$data=[];
		if (Request::isAjax()) {
			$idx = Request::post()->get("sno");
			$db = App::load(\DB::class);
			$memNo=\Session::get("member.memNo");
			$siteKey=\Session::get('siteKey');

			$sql="select * from wm_proxy_apply where idx=?";
			$applyRow = $db->query_fetch($sql,['i',$idx]);

			
			if($applyRow[0]['siteKey']==$siteKey){
				
				$sql="update wm_proxy_apply set agree='y' where idx=?";
				$db->bind_query($sql,['i',$idx]);
				$data['result']='y';

			}else{
				$data['result']='n';
			}
		}else{
			$data['result']='n';
		}

		$this->json($data);

		exit;
	
	}
}