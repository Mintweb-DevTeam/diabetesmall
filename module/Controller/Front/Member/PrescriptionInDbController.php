<?php
namespace Controller\Front\Member;

use Request;
use App;
use Session;
use Component\Policy\Policy;
class PrescriptionInDbController extends \Controller\Front\Controller
{

	public function index()
	{
		
		if(Session::has('member')) {
			
			$db = \App::load(\DB::class);
			$maxFileSize = 2 * 1024 * 1024;
			$policy = new Policy();
			$poInfo = $policy->getValue("basic.info",1);
			$mallDomain = explode("://",$poInfo['mallDomain']);
			
			
			$sslSQL ="select * from ".DB_SSL_CONFIG." where sslConfigDomain like '%{$mallDomain[0]}' and sslConfigUse='y'";
			$sslROW = $db->fetch($sslSQL);
			
			if(!empty($sslROW['sslConfigNo'])){
				$protoCol = "https://";
			}else{
				$protoCol = "http://";
			}
			
			$url =$mallDomain[0];
			
			$memNo = \Session::get("member.memNo");

			$file = Request::files()->toArray();

			if ($file['size'] > $maxFileSize) {
				$this->js("alert('2M이상 파일은 업로드 할수 없습니다.');");
			}else{			
				
				
				$data = array();
				//$data["upfiles"] = curl_file_create($file["up_file"]['tmp_name']);
				$data["upfiles"] = curl_file_create($file["up_file"]['tmp_name'], $file["up_file"]['type'], $file["up_file"]['name']);
				$data["filename"] = $file["up_file"]['name']; // 파일명 추가
				$data["fileDir"] = "prescription_img/"; // 파일명 추가
				
				$header = array();
				$header[] = 'Content-Type: multipart/form-data';
				
				$url=$protoCol."api.".$url."/load/file_upload.php";
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				//curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				$res = curl_exec($ch);
				
				curl_close($ch);
				
				if ($res === false) {
					
					
				} else {
					$jdata = json_decode($res);
					
					if(!empty($jdata->file_name)){
						
						$sql="insert into wm_prescription_img set memNo='$memNo',file_name='{$jdata->file_name}',ori_file_name='{$file['up_file']['name']}',regDt=sysdate()";
						$db->query($sql);
					
						$this->js("alert('처방전 이미지가 업로드 되었습니다');parent.location.href='./prescription_img_up.php'");
					}else{
						$this->js("alert('파일종류를 확인해주세요.업로드 가능한 파일은 jpg,jpeg,gif,png,zip,pdf 파일입니다.');parent.location.href='./prescription_img_up.php'");
					}
				}				
			}
			
		} else {
			$this->js("alert('로그인후 이용가능합니다.');parent.location.href='./login.php';");
		}		
		
		exit();
	}
}