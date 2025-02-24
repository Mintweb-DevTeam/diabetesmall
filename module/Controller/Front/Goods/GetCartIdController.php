<?php
namespace Controller\Front\Goods;

use Request;
use App;
use Session;
use Component\Goods\Goods;

class GetCartIdController extends \Controller\Front\Controller
{

	public function index()
	{
		//2024.06.20 웹앤모바일 상품상세에서 장바구니 담기시 해당 상품의 장바구니 번호및 상품정보를 가져옴
		$db = App::load(\DB::class);
		$in = Request::request()->all();
		$siteKey = Session::get('siteKey');
		
		$memNo = Session::get("member.memNo");
		
		$where=" where 1=1 and goodsNo=? and optionSno=?";
		
		
		if(empty($memNo)){
			$where.=" and siteKey='{$siteKey}'";
		}else{
			$where.=" and memNo='{$memNo}'";
		}
		
		$sql="select sno,goodsCnt from ".DB_CART.$where." order by sno DESC";
		$rows = $db->query_fetch($sql,['ii',$in['goodsNo'][0],$in['optionSno'][0]]);
		
		$data['cartSno'] = $rows[0]['sno']; 
		$data['upDate'] = 0;
		
		//장바구니에 동일상품 동일옵션이 있는지 체크시작
		$sql="select sum(goodsCnt) as goodsCnt from ".DB_CART.$where." and sno<>'{$rows[0]['sno']}'";
		$diffRow = $db->query_fetch($sql,['ii',$in['goodsNo'][0],$in['optionSno'][0]],false);
		
		if(!empty($diffRow['goodsCnt'])){
			$data['upDate']=$diffRow['goodsCnt'];
		}
		//장바구니에 동일상품 동일옵션이 있는지 체크종료
		
		
		$Goods = new Goods();
		$getGoodsView = $Goods->getGoodsView($in['goodsNo'][0]);
		
		$data['goodsNo'] = $in['goodsNo'][0];
		$data['goodsNm'] = strip_tags($getGoodsView['goodsNm']);
		
		if(empty($getGoodsView['brandNm'])){
			$getGoodsView['brandNm'] = "아델라";
		}
		
		$data['brandNm'] = strip_tags($getGoodsView['brandNm']);		
		$data['cateNm']  = strip_tags($getGoodsView['cateNm']);
		
		$optionSql = "select concat(optionValue1,optionValue2,optionValue3,optionValue4,optionValue5) as optionValue from ".DB_GOODS_OPTION." where sno=?";
		$optionRow = $db->query_fetch($optionSql,['i',$in['optionSno'][0]],false);
		
		$variant = $optionRow['optionValue'];
		if(empty($variant))
			$variant=$getGoodsView['goodsNm'];
		
		$data['variant']=strip_tags($variant);
		
		//gd_debug($getGoodsView);
		$this->json($data);
		
		exit();
	}

}