<?php
namespace Controller\Admin\Member;

use App;
use Request;

class BuyMemberListController extends \Controller\Admin\Controller
{


	public function index()
	{
	
		//if(\Request::getRemoteAddress() != "182.216.219.157"){
		//	exit();	
		//}
		
		$this->callMenu('member', 'buy_member', 'buy_member_list');
		
       // $cate = App::load('\\Component\\Category\\CategoryAdmin');
       // $gobj = App::load("\Component\Goods\Goods");
        $db = App::load('DB');

        $list = $q = [];
        $conds = "";
        $get = Request::get()->toArray();


        if ($get['sopt'] && $get['skey'])
          $q[] = "a.{$get['sopt']} LIKE '%{$get['skey']}%'";
        
        $get['searchDateFl'] = $get['searchDateFl']?$get['searchDateFl']:"regDt";

        if ($get['searchDate'][0]) {
          $sstamp = strtotime($get['searchDate'][0]);
          $q[] = "a.{$get['searchDateFl']} >= '".date("Y-m-d H:i:s", $sstamp)."'";
        }

        if ($get['searchDate'][1]) {
          $estamp = strtotime($get['searchDate'][1]) + (60 * 60 * 24);
          $q[] = "a.{$get['searchDateFl']} <= '".date("Y-m-d H:i:s", $estamp)."'";
        }

       

        if ($q)
          $conds = " AND " . implode(" AND ", $q);

        $page = $get['page']?$get['page']:1;
		
        $limit = 10;
		
		
		if(!empty($get['pageNum'])){
			$limit = $get['pageNum'];
		}
		
        $sql = "SELECT COUNT(idx) as cnt FROM wm_buyGoodsMember WHERE 1=1";
        $tmp = $db->query_fetch($sql);
        $amount = $tmp[0]['cnt'];

        $sql = "SELECT COUNT(a.idx) as cnt FROM wm_buyGoodsMember AS a WHERE 1=1 {$conds}";
        $tmp = $db->query_fetch($sql);
        $total = $tmp[0]['cnt'];

        $offset = ($page - 1) * $limit;
        $sql = "SELECT a.* FROM wm_buyGoodsMember AS a WHERE 1=1{$conds} ORDER BY a.idx desc LIMIT {$offset}, {$limit}";
		$rows = $db->query_fetch($sql);
      

        $page = App::load("\Component\Page\Page", $page, $total, $amount, $limit);
        $page->setUrl(http_build_query($get));
        $this->setData("list", $rows);
        $this->setData("page", $page);
        $this->setData("search", $get);
       
	}

}