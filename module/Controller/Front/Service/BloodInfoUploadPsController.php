<?php
namespace Controller\Front\Service;
use Request;
use Session;
class BloodInfoUploadPsController extends \Controller\Front\Controller
{
	public function index()
	{
		$post = Request::post()->all();
		$session = Session::all();
		$post['memNo'] = $session['member']['memNo'];
		$file = Request::files()->get('csv_file');
		$blood = new \Component\Cossia\BloodInfo;
		$sno = $blood->setInfo($post);
		if( $blood->fileUploaded($sno, $file) ) $result = $blood->fileConverting($sno);
		if( $result ) echo '<script>parent.alert("처리 되었습니다.");parent.location.replace("/main/html.php?htmid=service/blood_sugar_info.html");</script>';
		else echo '<script>parent.alert("일시적 오류 입니다. 다시 시도해 주세요");parent.location.reload();</script>';
		exit;
	}
}