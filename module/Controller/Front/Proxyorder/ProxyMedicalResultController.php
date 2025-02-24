<?php
namespace Controller\Front\Proxyorder;

use App;
use Request;
use Component\Policy\Policy;
class ProxyMedicalResultController extends \Controller\Front\Controller
{

	public function index()
	{
		$session = \Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
			exit;
		}

		$policy = new Policy();
		$po_data = $policy->getValue('basic.info',1);


		$this->setData($po_data);

		$db = App::load(\DB::class);
		$memNo=\Session::get("member.memNo");
		$siteKey=\Session::get('siteKey');


		$idx = Request::get()->get("sno");


		$sql="select * from wm_proxy_apply where idx=?";
		$applyRow = $db->query_fetch($sql,['i',$idx]);

		if($applyRow[0]['siteKey']!=$siteKey){
		
			$this->js("alert('정보가 없습니다.');document.location.href='./proxy_service.php';");
			return false;
		}

		$proxyObj= new \Component\Proxyservice\ProxyService();

		$applyRow[0]['pjumin']=$proxyObj->Decrypt($applyRow[0]['pjumin']);
		
		$this->setData($applyRow[0]);

		$start_date=date("Y년 m월 d일");
		
		$tmp_date=date("Y-m-d",strtotime(" +5 year"));
		$end_date = date("Y년 m월 d일",strtotime($tmp_date."-1 day"));
		
		$this->setData("start_date",$start_date);
		$this->setData("end_date",$end_date);
		
	}

}