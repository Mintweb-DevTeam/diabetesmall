<?php
namespace Controller\Admin\Member;

class PrescriptionFileDownController extends \Controller\Admin\Controller
{
	public function index()
	{
			
		$server  = \Request::server()->toArray();
		$file_name = \Request::get()->get("file_name");
		
		$result = preg_match('/gdadmin.diabetesmall.co.kr\/member\/prescription_img_upload\.php/', $server['HTTP_REFERER']);
		
		if($result != 1)
			return false;
			
		$img=array("jpg","jpeg","png","gif","pdf");
		
		if(empty($file_name))
			return false;
		
		
		
		$r = explode(".",$file_name,2);
		

		if(in_array($r[1],$img) === false){
			return false;
		}
		
		
		if($r[1]=="jpeg"){
			$r[1]="jpg";
		}
		
		$path=$server['DOCUMENT_ROOT']."/data/wm_upload/prescription_img/".$file_name;
		
		$newFile = date("YmdHis")."_".$r[0].".".$r[1];

		//$this->streamedDownload($path);
		$this->download($path, $newFile);
		
		exit();		
	}

}