<?php
namespace Controller\Admin\Goods;

class IndbProxyController extends \Controller\Admin\Controller
{


	public function index()
	{
		$db = \App::load(\DB::class);

		$in = \Request::request()->all();

		$sql="delete from wm_proxy_config";
		$db->query($sql);

		$in['cellPhone']=str_replace("-","",$in['cellPhone']);

		$sql="insert into wm_proxy_config set cellPhone='{$in['cellPhone']}',smsPassword='{$in['smsPassword']}',filePw='{$in['filePw']}',regDt=sysdate()";
		$db->query($sql);
			
		$this->layer("등록되었습니다.");
		exit;
	}

}