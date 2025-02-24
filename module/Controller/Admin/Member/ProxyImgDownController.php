<?php
namespace Controller\Admin\Member;

class ProxyImgDownController extends \Controller\Admin\Controller
{

	public function index()
	{
		$db = \App::load(\DB::class);
		$server = \Request::server()->toArray();
		$base_dir=$server['DOCUMENT_ROOT']."/data/upfile/";
		$in = \Request::request()->all();

		if(!empty($in['fname'])){

			$Admin_Id =  \Session::get('manager.managerId');
			$ip = \Request::getRemoteAddress();
			$sql="insert into wm_proxy_downLog set apply_idx=?,memId=?,down_type='j',regDt=sysdate(),ip=?";
			$db->bind_query($sql,['iss',$in['applyidx'],$Admin_Id,$ip]);

			$file_path = $base_dir.$in['fname'];

			if(is_file($file_path)){

				$ex=explode(".",$in['fname'],2);
				
				$this->download($file_path, $in['name'].".".$ex[1]);
			}


		}
		exit();
	
	}

}



