<?php

namespace Controller\Admin\Member;

use App;
use Request;
use Component\Policy\Policy;

class ProxyApplyPrintController extends \Controller\Admin\Controller
{
	public function index()
	{
		$this->getView()->setDefine("layout", "layout_blank");
		$idx = Request::get()->get("sno");

		$this->setData("idx",$idx);

		$db = App::load(\DB::class);
		$sql="select * from wm_proxy_apply where idx=?";
		$applyRow = $db->query_fetch($sql,['i',$idx]);

		$policy = new Policy();
		$po_data = $policy->getValue('basic.info',1);


		$this->setData($po_data);


		
		$proxyObj= new \Component\Proxyservice\ProxyService();
		
		

		$applyRow[0]['pjumin']=$proxyObj->Decrypt($applyRow[0]['pjumin']);

		$this->setData($applyRow[0]);
	
		$tmp_date=date("Y-m-d",strtotime($applyRow[0]['regDt']."+5 year"));
		$end_date=date("Y년 m월 d일",strtotime($tmp_date."-1 day"));
		
				
		$this->setData("end_date",$end_date);
	}

}
?>