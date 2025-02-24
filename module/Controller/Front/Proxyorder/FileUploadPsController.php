<?php
namespace Controller\Front\Proxyorder;

use Request;
use App;
use Component\Policy\Policy;

//2024.04.16 웹앤모바일

class FileUploadPsController extends \Controller\Front\Controller
{

	public function index()
	{
		if(empty($file))
			$file = Request::files()->toArray();
		
		$memNo = \Session::get("member.memNo");
		
		if(empty($memNo)){
			exit;
		}
		
		$db = App::load(\DB::class);
		
		//12시간 이후에 등록가능하도록 체크
		$sql="select count(*) AS cnt FROM wm_upload_file WHERE memNo = '$memNo' AND regDt >= NOW() - INTERVAL 12 HOUR;";
		$row = $db->query_fetch($sql);
		
		if($row[0]['cnt']>0){
			echo "double";
			exit;
		}

		
		$baseDomain="api.diabetesmall.co.kr";
		$domain ="http://".$baseDomain;
		
		//ssl사용중인지 체크
		$sql = "select * from ".DB_SSL_CONFIG." where sslConfigDomain=?";
		$sslRow = $db->query_fetch($sql,['s',$baseDomain],false);
		
		if($sslRow['sslConfigUse']=='y'){
			$domain ="https://".$baseDomain.":".$sslRow['sslConfigPort'];
		}
		
		$data = array();
		
		$file_name="upfile";
		
		$data["upfiles"] = curl_file_create($file[$file_name]['tmp_name']);
		$data["filename"] = $file[$file_name]['name']; // 파일명 추가
				
		$header = array();
		$header[] = 'Content-Type: multipart/form-data';
		
		$url=$domain."/load/file_upload.php";

		$server = Request::server()->toArray();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		$res = curl_exec($ch);
		curl_close($ch);

		$jdata = json_decode($res);
		
		
		if(!empty($jdata->file_name)){
			$nowDate =date("Y-m-d H:i:s");
			$fname  =$jdata->file_name;
			
			$sql="insert into wm_upload_file set memNo=?,oriFname='{$data['filename']}',fname='{$fname}',regDt=?";
			$db->bind_query($sql,['is',$memNo,$nowDate]);
			echo "success";
		}else{
			echo "fail";
		}		
		exit;
		
	}
}
?>