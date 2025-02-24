<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>
<?php include $goodsSearchFrm; ?>
<div class='table-title'>상품목록</div>

<div class="table-header">
  <div class="pull-left">
    검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
    전체 <strong><?=number_format($page->recode['amount']);?></strong>개
  </div> <!-- pull-left -->
</div> <!-- table-header -->
<form name="GoodsListForm" method='post' action='../goods/indb_buy_goods.php' target='ifrmProcess' autocomplete='off'>
<input type='hidden' name='mode' value=''>
<div class="pull-right" style="margin-bottom:10px;">
	<input type='button' value='설정하기' class='btn btn-black buy_goods_set'>
</div>
<table class='table table-rows'>
  <thead>
  <tr>
    <th width='20' class='center'><input type='checkbox' class='js-checkall' data-target-name='goodsNo'></th>
    <th width='60' class='center'>구매 상품</th>
	
    <th width='100' class='center'>상품코드</th>
    <th width='40' class='center'>이미지</th>
    <th class='center'>상품명</th>
    <th class='center'>상품상태</th>
  </tr>
  </thead>
  <tbody>
<?php if (gd_isset($list)) : ?>
<?php foreach ($list as $key => $li) : ?>
<tr>
	<td class='center' nowrap>
		<input type='checkbox' name='goodsNo[]' value='<?=$li['goodsNo']?>'>
	</td>
	<td class='center' nowrap>
		<input type='checkbox' name='chk[<?=$li['goodsNo']?>]' value='1' <?php if($li['buyGoods']=='y')echo"checked";?>>
	</td>
	<td class='center' nowrap><?=$li['goodsNo']?></td>
	<td class='center' nowrap><?=$li['images'][0]?></td>
	<td><span onclick="goods_register_popup('<?=$li['goodsNo']?>');" style='cursor: pointer;'><?=$li['goodsNm']?></span></td>
	<td>
		<?php if($li['goodsSellFl'] == 'y'){?>
			판매
		<?php }else{?>
			판매안함
		<?php }?>
		
	</td>
	
</tr>
<?php endforeach; ?>
<?php endif; ?>
   </tbody>
</table>
<div class='table-action form-inline' style='padding-left: 10px;'>
    <input type='button' value='설정하기' class='btn btn-black buy_goods_set'>
</div>
<div class="text-center"><?=$page->getPage();?></div>
</form>
<script type="text/javascript">
	$(function(){
		
		/*
		$("input:checkbox[name*='chk[']").click(function(){
			
			let checked = $(this).prop("checked");
			
			$("input:checkbox[name*='chk[']").each(function(){
				$(this).prop("checked", false);
			
			});
			
			if(checked==true)
				$(this).prop("checked",true);
		});
		
		*/
		$(".buy_goods_set").click(function(){
		
			if($("input:checkbox[name^='goodsNo[']:checked").length<=0){
				alert("구매상품으로 설정 또는 구매상품 해제할 상품을 선택하세요.");
				return false;
			}		
		
			if(confirm("선택한 상품을 구매상품으로 설정 또는 구매상품 해제 하시겠습니까?")==true){
				
				document.GoodsListForm.submit();
			}
		});
	
	});

</script>

