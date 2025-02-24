<?php
namespace Controller\Mobile\Goods;
class GoodsListController extends \Bundle\Controller\Mobile\Goods\GoodsListController
{
	public function index()
	{
		parent::index();
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$cossia = new \Component\Cossia\Cossia;
		$this->setData( 'is_charsang', $cossia->getCharsang() );


		 $memId = \Session::get("member.memId");
		//if ($memId=="dev@mintweb.co.kr") {
			
		$sub = new \Component\Subscription\Subscription();
		$sub_cfg = $sub->getCfg();
		
		$deliveryEa = end($sub_cfg['deliveryEa']);

		$this->setData("deliveryEa",$deliveryEa);
		
		$goods = new \Component\Goods\Goods();

		$db=\App::load(\DB::class);
		$cateCd = \Request::get()->get("cateCd");
		$sql="select g.goodsNo from ".DB_GOODS_LINK_CATEGORY." glc INNER JOIN ".DB_GOODS." g ON glc.goodsNo=g.goodsNo where glc.cateCd=? and g.delFl=? and g.isSubscription='1' and listViewFl='y'";
		$rows = $db->query_fetch($sql,['ss',$cateCd,'n']);
		
		$goods_data=[];
		foreach($rows as $key => $val){
			$goods_view = $goods->getGoodsView($val['goodsNo']);

			$goods_data[$key]["goodsNo"]=$val['goodsNo'];
			$goods_data[$key]["goodsNm"]=$goods_view['goodsNm'];
			$goods_data[$key]["goodsPrice"]=$goods_view['goodsPrice'];
			$goods_data[$key]["subscription_shortDescription"]=$goods_view['subscription_shortDescription'];
				
			$imgSQL="select * from ".DB_GOODS_IMAGE." where goodsNo=?";
			$image = $goods->getGoodsImage($val['goodsNo'],['list']);

			$goods_data[$key]["goodsImage"]="/data/goods/".$goods_view['imagePath']."/".$image[0]['imageName'];
		
		}
		$this->setData("goods_data",$goods_data);
			
		//}
		
		//2024.01.31���ظ���� �߰�����
		$now_date=date("Y-m-d");
		//if($now_date <= "2024-04-30"){//��ü ��û������ ���� ��Ų���� �ǵ����� �ڵ� �ּ�ó����-2024.04.30���ظ����
			
			$this->getView()->setPageName('goods/goods_list_new.php');
		//}
		//2024.01.31���ظ���� �߰�����

	}
}