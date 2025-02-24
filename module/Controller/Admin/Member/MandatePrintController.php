<?php
namespace Controller\Admin\Member;
use Exception;
use Request;
class MandatePrintController extends \Controller\Admin\Controller {
    public function index(){
		try{
			$post = Request::post()->all();
			if (!is_object($this->db)) $this->db = \App::load('DB');
			$sql = 'SELECT 
						`c`.`charsang`, 
						`c`.`relation`, 
						`m`.`memNm`, 
						`m`.`cellPhone`, 
						`m`.`email` 
					FROM `co_mandate` AS `c` 
					LEFT JOIN `es_member` AS `m` ON `m`.`memNo` = `c`.`memNo` 
					WHERE `c`.`sno` = '.$post['sno'];
			
			$data = $this->db->query_fetch($sql, true)[0];
			$this->setData('data', $data);
			$this->setData('popupTitle', '위임청구 인쇄');
			$this->getView()->setDefine('layout', 'layout_blank.php');
			// $this->getView()->setPageName('member/_mandate_print.php');
			$this->getView()->setDefine('layoutForm', Request::getDirectoryUri() . '/_mandate_print.php');
            $this->getView()->setPageName('order/order_print.php');
		} catch (Exception $e) {
            throw new AlertCloseException($e->getMessage());
        }
    }
}