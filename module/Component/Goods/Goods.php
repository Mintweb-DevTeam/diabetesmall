<?php
namespace Component\Goods;
class Goods extends \Bundle\Component\Goods\Goods
{
	protected function setGoodsListField()
    {
		parent::setGoodsListField();
        $this->goodsListField = 'g.coViewOrder,'.$this->goodsListField;
    }
}