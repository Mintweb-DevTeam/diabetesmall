<?php
namespace Component\Wm;

use App;
use Request;

//2024.10.21웹앤모바일 이벤트, 온라인 이벤트,오프라인 이벤트 상품인지 체크 추가
class StartEventGoodsCheck
{

	private $db="";
	
	public function __construct()
	{
		if(!is_object($this->db)){
		
			$this->db = App::load(\DB::class);
			
		}
		
	}
	
	
	public function GoodsTypeCheck($goodsNo)
	{
		
		if(empty($goodsNo)){
		
			return false;
		}
		
		$sql="select * from wm_start_goods where goodsNo=?";
		$row = $this->db->query_fetch($sql,['i',$goodsNo]);
		
		if(!empty($row[0]['idx']))
			$startGoods = 1;
		
		
		$sql="select * from wm_start_goods where other_goodsNo=?";
		$row = $this->db->query_fetch($sql,['i',$goodsNo]);
		
		if(!empty($row[0]['idx']))
			$otherStartGoods = 1;
		
		
		$sql="select * from wm_event_goods where goodsNo=?";
		$row = $this->db->query_fetch($sql,['i',$goodsNo]);
		
		if(!empty($row[0]['idx']))
			$eventGoods = 1;
		
		
		if($goodsNo==1000000079)
			$directGoods=1;
		
		return ["startGoods"=>$startGoods,"otherStartGoods"=>$otherStartGoods,"eventGoods"=>$eventGoods,"directGoods"=>1];
		
	}

}