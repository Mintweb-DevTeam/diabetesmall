<?php

namespace Controller\Admin\Goods;


use Request;
use App;


class IndbBuyGoodsController extends \Controller\Admin\Controller
{

	public function index()
	{
	
		$db = App::load(\DB::class);
		$param = Request::post()->toArray();
		
		
		$mode = $param['mode'];
		
		switch($mode){
		
			default:
				
				foreach($param['goodsNo'] as $key => $val){
				
					$buyGoods='n';
					
					if(!empty($param['chk'][$val])){
						$buyGoods='y';
					}
					
					
					$sql="update ".DB_GOODS." set buyGoods='{$buyGoods}' where goodsNo='{$val}'";
					$db->query($sql);
					
					$searchSQL = "update ".DB_GOODS_SEARCH." set buyGoods='{$buyGoods}' where goodsNo='{$val}'";
					$db->query($searchSQL);						
					
				}
				

				$this->layer("처리되었습니다.");
				
				break;
		
		}
	
		exit();
	}

}