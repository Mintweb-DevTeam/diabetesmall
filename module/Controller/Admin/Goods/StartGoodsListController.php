<?php
namespace Controller\Admin\Goods;

use App;
use Request;

class StartGoodsListController extends \Controller\Admin\Controller 
{
    public function index()
    {
        $this->callMenu('goods', 'start_goods', 'start_goods_list');
        
        $cate = App::load('\\Component\\Category\\CategoryAdmin');
        $gobj = App::load("\Component\Goods\Goods");
        $db = App::load('DB');

        $list = $q = [];
        $conds = "";
        $get = Request::get()->toArray();

        if ($get['sopt'] && $get['skey'])
          $q[] = "a.{$get['sopt']} LIKE '%{$get['skey']}%'";

        if ($get['start_goods'])
            $q[] = "(select count(wg.idx) as cnt from wm_start_goods wg where wg.goodsNo=a.goodsNo)>'0'";
        
        $get['searchDateFl'] = $get['searchDateFl']?$get['searchDateFl']:"regDt";

        if ($get['searchDate'][0]) {
          $sstamp = strtotime($get['searchDate'][0]);
          $q[] = "a.{$get['searchDateFl']} >= '".date("Y-m-d H:i:s", $sstamp)."'";
        }

        if ($get['searchDate'][1]) {
          $estamp = strtotime($get['searchDate'][1]) + (60 * 60 * 24);
          $q[] = "a.{$get['searchDateFl']} <= '".date("Y-m-d H:i:s", $estamp)."'";
        }

        $cateCd = "";
        if ($get['cateGoods']) {
          foreach ($get['cateGoods'] as $c) {
            if ($c)
              $cateCd = $c;
          }
        }

        if ($cateCd)
          $q[] = "gl.cateCd LIKE '{$cateCd}%'";
        

        if ($q)
          $conds = " AND " . implode(" AND ", $q);

        $page = $get['page']?$get['page']:1;
        $limit = 50;
        $sql = "SELECT COUNT(*) as cnt FROM " . DB_GOODS . " WHERE delFl='n'";
        $tmp = $db->query_fetch($sql);
        $amount = $tmp[0]['cnt'];

        $sql = "SELECT COUNT(DISTINCT(a.goodsNo)) as cnt FROM " . DB_GOODS . " AS a
          LEFT JOIN " . DB_GOODS_LINK_CATEGORY . " AS gl ON a.goodsNo = gl.goodsNo WHERE a.delFl='n'{$conds}";

        $tmp = $db->query_fetch($sql);
        $total = $tmp[0]['cnt'];

        $offset = ($page - 1) * $limit;
        $sql = "SELECT DISTINCT(a.goodsNo), a.optionFl FROM " . DB_GOODS . " AS a
          LEFT JOIN " . DB_GOODS_LINK_CATEGORY . " AS gl ON a.goodsNo = gl.goodsNo
        WHERE a.delFl='n'{$conds} ORDER BY a.goodsNo desc LIMIT {$offset}, {$limit}";
        $cateList = [];
        if ($tmp = $db->query_fetch($sql)) {
          foreach ($tmp as $t) {
              if ($t['optionFl'] == 'y') {
                  $row = $db->fetch("SELECT COUNT(*) as cnt FROM " . DB_GOODS_OPTION . " WHERE goodsNo='{$t['goodsNo']}'");
                  if (empty($row['cnt']))
                      continue;
              }
          

            $goods = $gobj->getGoodsView($t['goodsNo']);


            $images = [];
            if ($imageList = $gobj->getGoodsImage($goods['goodsNo'], 'list')) {
              foreach ($imageList as $li) {
                $image = gd_html_goods_image($goods['goodsNo'], $li['imageName'], $goods['imagePath'], $goods['imageStorage'], 40, $goods['goodsNm'], '_blank');
                $images[] = $image;
              }
            }

            $goods['images'] = $images;

			$sql="select * from wm_start_goods where goodsNo='{$t['goodsNo']}'";
			$startRow = $db->fetch($sql);
			
			if(!empty($startRow['idx'])){
				$goods['start_goods'] =1;
				$goods['move_goodsNo'] =$startRow['move_goodsNo'];
			}else{
				$goods['start_goods'] =0;
				$goods['move_goodsNo'] ="";
			}
			
			
			if(\Request::getRemoteAddress()=="182.216.219.157"){	
			//2024.10.18웹앤모바일 추가시작
				$otherSQL="select * from wm_start_goods where other_goodsNo='{$t['goodsNo']}'";
				$otherStartRow = $db->fetch($otherSQL);
				
				if(!empty($otherStartRow['idx'])){
					$goods['other_start_goods'] =1;
					$goods['other_move_goodsNo'] =$otherStartRow['other_move_goodsNo'];
				}else{
					$goods['other_start_goods'] =0;
					$goods['other_move_goodsNo'] ="";
				}
				
				
				$eventSQL="select * from wm_event_goods where goodsNo='{$t['goodsNo']}'";
				$eventStartRow = $db->fetch($eventSQL);
				
				if(!empty($eventStartRow['idx'])){
					$goods['event_start_goods'] =1;
				}else{
					$goods['event_start_goods'] =0;
				}
				
			//2024.10.18웹앤모바일 추가종료
			}
			
			
			
			
			
			
            $list[] = $goods;
          }
        }

        $page = App::load("\Component\Page\Page", $page, $total, $amount, $limit);
        $page->setUrl(http_build_query($get));
        $this->setData("list", $list);
        $this->setData("page", $page);
        $this->setData("search", $get);
        $this->setData("cate", $cate);
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->getView()->setDefine("goodsSearchFrm", Request::getDirectoryUri() . "/goodsSearchForm3.php");
        
    }
}