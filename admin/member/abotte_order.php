<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <!--input id="regph" type="submit" value="메디컬샵 등록" class="btn btn-red-line" id="btnJoin"/-->
</div>



<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit" autocomplete="off">
    <div class="table-title">
        리브레 주문정보 검색
    </div>
    <div class="search-detail-box form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>기간</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" name="sDate" value="<?=$get['sDate']?>"><span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                </div>
				~ 
				<div class="input-group js-datepicker">
                    <input type="text" class="form-control" name="eDate" value="<?=$get['eDate']?>"><span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                </div>
            </td>
			<th>
				상태
			</th>
			<td>
				<select name="status">
					<option value="" >전체</option>
					<?php
					foreach( $status as $key => $val ) :
						   $selected = ( isset($get['status']) &&  $get['status'] == $key ) ? 'selected' : '';
					?>
					<option value="<?=$key?>" <?=$selected?> ><?=$val?></option>
					<?php endforeach; ?>
				</select>
			</td>
        </tr>
    </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button">
        </div>
    </div>
</form>


<table class="table table-rows">
	<thead>
		<th>주문번호</th>
		<th>아델라 회원번호</th>
		<th>리브레 회원번호</th>
		<th>주문일시</th>
		<th>수정일시</th>
		<th>상품 주문번호</th>
		<th>수량</th>
		<th>처리상태</th>
		<th>배송횟수</th>
		<th>배송주기</th>
		<th>배송회차</th>
		<th>자동연장</th>
		<th>연장횟수</th>
		<th>센서추가증정</th>

	</thead>
	<tbody>
		<?php
		if( count($data) ) :
			foreach($data as $row) :
		?>
		<tr>
			<td class="text-center"><?=$row['orderNo']?></td>
			<td class="text-center"><?=$row['memNo']?></td>
			<td class="text-center"><?=$row['abbott_sno']?></td>
			<td class="text-center"><?=$row['regDt']?></td>
			<td class="text-center"><?=$row['modDt']?></td>
			<td class="text-center"><?=$row['goods_order_no']?></td>
			<td class="text-center"><?=$row['goodsCnt']?></td>
			<td class="text-center"><?=$row['orderStatus']?></td>

			<td class="text-center"><?=$row['schedule_tcount']?></td>
			<td class="text-center">
				<?php if(!empty($row['period'])){//2021.12.03민트웹 추가?>
				<?=$row['period']?>
				<?php }else{?>
				단건
				<?php }?>
			</td>
			<td class="text-center">
				<?php if($row['schedule']>0){//2021.12.03민트웹 추가?>
					<?=$row['schedule']?>/<?=$row['deliveryEa']?>
				<?php }else{?>

				<?php }?>
			</td>
			<td class="text-center"><?=$row['autoExtend']?></td>
			<td class="text-center"><?=$row['autoExtendCount']?></td>
			<td class="text-center"><?=$row['gift']?></td>

		</tr>
		<?php
			endforeach;
		else :
		?>
		<tr>
			<td colspan="10" class="text-center">검색된 결과가 없습니다.</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<div class="table-action clearfix">
	<div class="pull-right">
		<a href="javascript:get_abotte_excel()" class="btn btn-white btn-icon-excel">엑셀다운로드</a>
    </div>
</div>

<script>
function get_abotte_excel(){
	if( !$('input[name="sDate"]').val() && !$('input[name="eDate"]').val() ){
		alert('기간이 없습니다.');
		return;
	}
	window.location.href = './co_abotte_excel_down.php?type=order&sDate='+$('input[name="sDate"]').val()+'&eDate='+$('input[name="eDate"]').val();
}
</script>
