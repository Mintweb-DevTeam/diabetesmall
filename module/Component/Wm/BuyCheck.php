<?php

namespace Component\Wm;

use Request;
use App;
use Session;

class BuyCheck
{
	//2024.11.06웹앤모바일 구매가능상품을 구매가능한 회원인지 기능체크 클래스
	public function goodsBuyChk($goodsNo="")
	{
	
		$bool=0;
		if(empty($goodsNo)){
		
			return $bool;
		}
		
		$member = Session::get("member");
		$cellPhone = str_replace("-","",$member['cellPhone']);
		
		if(empty($cellPhone)){
			return $bool;
		}
		
		$db = App::load(\DB::class);
		
		$sql="select count(goodsNo) as cnt from ".DB_GOODS." where buyGoods='y' and goodsNo='$goodsNo'";
		$row = $db->query_fetch($sql);
		
		if($row[0]['cnt']>0){
			$sql="select count(idx) as cnt from wm_buyGoodsMember where cellPhone=?";
			$bRow = $db->query_fetch($sql,['s',$cellPhone]);
			
			if($bRow[0]['cnt']>0){
				$bool=1;
				return $bool;
			}else{
				$bool=2;
				return $bool;
			}
		
		}else{
			$bool=0;
			return $bool;
		}
		
	}


}