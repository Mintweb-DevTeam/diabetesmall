<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>
<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=$get['pageNum']?>">
    <div class="table-title">
        검색
    </div>
    <div class="search-detail-box form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>검색어</th>
            <td>
                <select name="key" class="form-control">
                    <option value="name" <?=($get['key'] == 'name') ? 'selected' : ''?>>이름</option>
                    <option value="phone" <?=($get['key'] == 'phone') ? 'selected' : ''?>>휴대폰</option>
                    <option value="email" <?=($get['key'] == 'email') ? 'selected' : ''?>>이메일</option>
                </select>
                <input name="keyword" type="text" value="<?=$get['keyword']?>" class="form-control">
            </td>
        </tr>
    </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button">
        </div>
    </div>
</form>



<div class="table-header form-inline">
	<div class="pull-left">
    	검색
        <strong><?= $page->recode['total']; ?></strong>
        명 / 전체
		<strong><?= $page->recode['amount']; ?></strong>
        명
    </div>
	<div class="pull-right">
		<div>
			<?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
		</div>
	</div>
</div>
<table class="table table-rows">
	<colgroup>
		<col width="4%">
		<col>
		<col>
		<col>
		<col>
		<col>
		<col>
		<col>
		<col>
		<col>
	</colgroup>
	<thead>
		<th>
			<input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk">
		</th>
		<th>이름</th>
		<th>휴대폰</th>
		<th>이메일</th>
		<th>개인정보 수집 및 이용(필)</th>
		<th>마케팅 수집 및 이용</th>
		<th>마케팅 정보 수신</th>
		<th>개인정보 제공(필)</th>
		<th>개인정보 제공</th>
		<th>등록일시</th>
	</thead>
	<tbody>
		<?php
		if($data) :
			foreach($data as $row) :
		?>
		<tr>
			<td class="text-center">
				<input type="checkbox" name="chk[]" class="sno" value="<?=$row['sno']?>">
			</td>
			<td class="text-center"><?=$row['name']?></td>
			<td class="text-center"><?=$row['phone']?></td>
			<td class="text-center"><?=$row['email']?></td>
			<td class="text-center"><?=$row['agree1']?></td>
			<td class="text-center"><?=$row['agree2']?></td>
			<td class="text-center"><?=$row['agree3']?></td>
			<td class="text-center"><?=$row['agree4']?></td>
			<td class="text-center"><?=$row['agree5']?></td>
			<td class="text-center"><?=$row['regDt']?></td>
		</tr>
		<?php
			endforeach;
		else :
		?>
		<tr>
			<td class="text-center" colspan="10">검색된 정보가 없습니다.</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
<div class="table-action clearfix">
	<div class="pull-left">
		<button type="button" onClick="agree_del()" class="btn btn-white">선택삭제</button>
		&nbsp;
		<!-- 삭제, 중복 확인 --->
	</div>
	<div class="pull-right">
		<a href="./co_excel_down.php?dbname=aboottAgree" class="btn btn-white btn-icon-excel">엑셀다운로드</a>
	</div>
</div>
<div class="center">
	<?= $page->getPage(); ?>
</div>
<script>
function agree_del(){
	if( $('.sno:checked').length === 0 ){
		alert('선택된 회원이 없습니다.');
		return;
	}
	var snos = [];
	$('.sno:checked').each(function(){
		snos.push( $(this).val() );
	});
	$.ajax({
		url:'./cossia_ajax.php',
		type:'POST',
		data:{'snos':snos,'type':'agree_del'},
		dataType : "json",
		success: function(x){
			alert('처리되었습니다.');
			location.reload();
		},
		error:function(request,status,error){
			console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
		}
	});
}
</script>