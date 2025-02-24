<?php
namespace Controller\Front\Mypage;
use Globals;
use Session;
use Request;
use Framework\Debug\Exception\AlertCloseException;
use Component\Member\Util\MemberUtil;
use UserFilePath;
use Framework\Utility\NumberUtils;

/*
* 거래명세표 출력시 회원정보 is_print 변경 
* 2021-01-10 Cossia
*/
class OrderPrintController extends \Bundle\Controller\Front\Mypage\OrderPrintController
{
	public function index()
	{
		parent::index();
		$order = \App::load('\\Component\\Order\\OrderAdmin');
		$orderData = $order->getOrderPrint(Request::post()->get('orderPrintCode'), Request::post()->get('orderPrintMode'))[0];
		if(Request::post()->get('orderPrintMode') == 'particular')
		{
			if (MemberUtil::checkLogin() == 'member' || MemberUtil::checkLogin() == 'guest')
			{
				if( $orderData['memNo'] == Session::get('member.memNo') )
				{
					if (!is_object($this->db)) $this->db = \App::load('DB');
					$sql = 'UPDATE `es_member` SET `is_print` = \'y\' WHERE `memNo` = '.Session::get('member.memNo');
					$this->db->query($sql);
				}
			}
		}
	}
	
}