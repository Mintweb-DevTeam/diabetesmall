<?php
namespace Controller\Api\Load;

class FileUploadController extends \Controller\Api\Controller
{

	//2024.04.16 웹앤모바일
	public function index()
	{
		
		$image_mime_types = array('image/jpeg','image/png','image/gif','application/pdf');		
		$allowed_extensions = array('jpg', 'jpeg', 'png', 'gif','pdf');
		
		$header = \getallheaders();
	 
		$post=\Request::post()->toArray();	
		$Files=\Request::files()->toArray();
		$server =\Request::server()->toArray();
		
		$fileDir=$post['fileDir'];
			
		$filename = $Files['upfiles'];
		$ext = pathinfo($post['filename'], PATHINFO_EXTENSION);
		
		$file_mime_type = mime_content_type($Files['upfiles']['tmp_name']);

		$return_data=[];
		
		if (in_array($file_mime_type, $image_mime_types)) {
			if (!in_array(strtolower($ext), $allowed_extensions)) {
				$return_data['file_name']="";
				
			}else{
				$fname = date("YmdHis").uniqid().".".$ext;
				$file_path = $server['DOCUMENT_ROOT']."/data/wm_upload/".$fileDir.$fname;
				
				
				move_uploaded_file($Files['upfiles']['tmp_name'], $file_path);
				
				$return_data['file_name']=$fname;
			}
		}else{
			
			
			$return_data['file_name']="";
		}
		

		echo json_encode($return_data);
		exit();
	
	}

}