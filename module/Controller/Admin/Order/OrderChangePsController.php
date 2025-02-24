<?php
namespace Controller\Admin\Order;

 use Request;
 use App;
 
class OrderChangePsController extends \Bundle\Controller\Admin\Order\OrderChangePsController
{
    public function index()
    {
        $post = Request::post()->toArray();
        $server = Request::server()->toArray();
         
         if ($post['orderNo'] && in_array($post['mode'], ['cancel', 'refund']) && $post['refund']['refundMethod'] == 'PG환불') {

            $cnt = count($post['refund']['statusCheck']);
            $db = App::load('DB');
            $tmp2 = $db->fetch("SELECT COUNT(*) as cnt FROM " . DB_ORDER_GOODS . " WHERE orderNo='{$post['orderNo']}'");
            $cnt2 = $tmp2['cnt'];

            $od = $db->fetch("SELECT * FROM " . DB_ORDER . " WHERE orderNo='{$post['orderNo']}'");
            if ($cnt == $cnt2 && $od) {
                $orderStatus = substr($od['orderStatus'], 0, 1);

				$strSQL="select count() as cnt from wm_subscription_schedule_list where orderNo=?";
				$strRow = $db->query_fetch($strSQL,['s',$od[$post['orderNo']]],false);

                //if ($orderStatus != 'r' && $od['pgName'] == 'sub') {
				if ($orderStatus != 'r' && $od['pgName'] == 'inicis' && $strRow['cnt']>0) {

                    $subObj = App::load(\Component\Subscription\Subscription::class);
                    $subObj->cancel($post['orderNo'], false);
                }
            }
        }
        /* 튜닝 END */

        parent::index();
    }
}