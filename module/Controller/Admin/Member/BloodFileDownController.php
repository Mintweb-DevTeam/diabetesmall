<?php
namespace Controller\Admin\Member;
use Request;
class BloodFileDownController extends \Controller\Admin\Controller
{
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$sql = 'SELECT `fileInfo` FROM `co_bloodInfo` WHERE `sno` = '.Request::get()->get('sno');
		$info = json_decode( $this->db->query_fetch($sql, true)[0]['fileInfo'], true );
		// $this->download( Request::server()->get('DOCUMENT_ROOT').$info['target'], $info['name'] );
		
		$filepath = Request::server()->get('DOCUMENT_ROOT').$info['target'];
		$filesize = filesize($filepath);
		$filename = $info['name'];
		if( $this->is_ie() ) $filename = $this->utf2euc($filename);
		header("Pragma: public");
		header("Expires: 0");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: $filesize");
		readfile($filepath);
		exit;
	}
	
	function utf2euc($str)
	{
		return iconv("UTF-8","cp949//IGNORE", $str);
	}
	
	function is_ie()
	{
		if( strpos( Request::server()->get('HTTP_USER_AGENT'), 'MSIE' ) !== false ) return true;
		if( strpos( Request::server()->get('HTTP_USER_AGENT'), 'Windows NT 6.1' ) !== false ) return true;
		return false;
	}
}