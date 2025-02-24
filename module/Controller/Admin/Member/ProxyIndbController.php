<?php

namespace Controller\Admin\Member;


use App;
use Request;

class ProxyIndbController extends \Controller\Admin\Controller
{

	public function index()
	{
		$db = App::load(\DB::class);
		$in=Request::request()->all();

		$proxyObj = new \Component\Proxyservice\ProxyService();
		
		switch($in['mode']){
			case "print_log":
				$Admin_Id =  \Session::get('manager.managerId');
				$ip = \Request::getRemoteAddress();
				$sql="insert into wm_proxy_downLog set apply_idx=?,memId=?,down_type='p',regDt=sysdate(),ip=?";
				$db->bind_query($sql,['iss',$in['applyidx'],$Admin_Id,$ip]);

				$data=[];
				$data['success']=1;
				$this->json($data);

				break;
			default:
				
				foreach($in['chk'] as $k =>$t){

					$proxyObj->chkDel("admin",$t);

				}
				$msg="삭제되었습니다.";
				break;
		}

		$this->layer($msg);
		exit;
	}

}