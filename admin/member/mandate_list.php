<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>
<script>
$(function(){
	$('select[name="pageNum"]').on('change', function(){
		$('input[name="pageNum"]').val( $(this).val() );
		$('#frmSearchBase').submit();
	})
})
</script>
<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=$get['pageNum']?>">
    <div class="table-title">
        위임청구 회원 검색
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
                <select name="filde" class="form-control">
					<option value="memId" <?=($get['filde'] == 'memId') ? 'selected' : ''?>>아이디</option>
                    <option value="memNm" <?=($get['filde'] == 'memNm') ? 'selected' : ''?>>회원명</option>
                    <option value="cellPhone" <?=($get['filde'] == 'cellPhone') ? 'selected' : ''?>>전화번호</option>
                </select>
                <input name="search" type="text" value="<?=$get['search']?>" class="form-control">
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
		전체 <strong><?=$total?></strong> 명
	</div>
	<div class="pull-right">
		<div>
			<select class="form-control" name="pageNum">
				<option value="10" <?=($get['pageNum'] == 10) ? 'selected' : ''?>>10개 보기</option>
				<option value="20" <?=($get['pageNum'] == 20) ? 'selected' : ''?>>20개 보기</option>
				<option value="30" <?=($get['pageNum'] == 30) ? 'selected' : ''?>>30개 보기</option>
				<option value="40" <?=($get['pageNum'] == 40) ? 'selected' : ''?>>40개 보기</option>
				<option value="50" <?=($get['pageNum'] == 50) ? 'selected' : ''?>>50개 보기</option>
				<option value="60" <?=($get['pageNum'] == 60) ? 'selected' : ''?>>60개 보기</option>
				<option value="70" <?=($get['pageNum'] == 70) ? 'selected' : ''?>>70개 보기</option>
				<option value="80" <?=($get['pageNum'] == 80) ? 'selected' : ''?>>80개 보기</option>
				<option value="90" <?=($get['pageNum'] == 90) ? 'selected' : ''?>>90개 보기</option>
				<option value="100" <?=($get['pageNum'] == 100) ? 'selected' : ''?>>100개 보기</option>
				<option value="200" <?=($get['pageNum'] == 200) ? 'selected' : ''?>>200개 보기</option>
				<option value="300" <?=($get['pageNum'] == 300) ? 'selected' : ''?>>300개 보기</option>
				<option value="500" <?=($get['pageNum'] == 500) ? 'selected' : ''?>>500개 보기</option>
			</select>
		</div>
	</div>
</div>


<table class="table table-rows">
	<colgroup>
		<col width="5%">
		<col width="*">
		<col width="*">
		<col width="*">
		<col width="7%">
		<col width="7%">
		<col width="11%">
		<col width="5%">
	</colgroup>
	<thead>
		<tr>
			<th><input id="allc" type="checkbox" onChange="allc(this)"></th>
			<th>이름</th>
			<th>이메일</th>
			<th>전화번호</th>
			<th>관계</th>
			<th>차상위 해당</th>
			<th>청구일</th>
			<th>인쇄</th>
        </tr>
	</thead>
	<tbody>
		<?php if($list) foreach($list as $row){ ?>
		<tr>
			<td class="center">
				<input name="mamNo[]" class="memnos" type="checkbox" value="<?=$row['sno']?>" >
			</td>
			<td class="center"><?=$row['memNm']?></td>
			<td class="center"><?=$row['memId']?></td>
			<td class="center"><?=$row['cellPhone']?></td>
			<td class="center"><?=$row['relation']?></td>
			<td class="center"><?=$row['charsang']?></td>
			<td class="center"><?=$row['regDt']?></td>
			<td class="center"><button type="button" onClick="mandate_print_popup(<?=$row['sno']?>)" class="btn btn-white btn-sm">인쇄</button></td>
		</tr>
		<?php } else { ?>
		<tr><td colspan="8" class="center">검색된 정보가 없습니다.</td></tr>
		<?php } ?>
	</tbody>
</table>
<div class="table-action clearfix">
	<div class="pull-left">
		<button type="button" class="btn btn-white" onClick="del_mandate()">선택삭제</button>
	</div>
	<div class="pull-right">
	</div>
</div>
<div class="center">
	<?=$page?>
</div>
<script>
function del_mandate(){
	if( $('.memnos:checked').length == 0 ){
		alert('선택된 회원이 없습니다.');
		return;
	}
	var anos = [];
	$('.memnos:checked').each(function(){
		anos.push( $(this).val() );
	});
	$.ajax({
		url:'./cossia_ajax.php',
		type:'POST',
		data:{'type':'mandate_del', 'anos':anos},
		dataType : "json",
		success: function(x){
			location.reload();
		},
		error:function(request,status,error){
			console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
	});
}
function allc(obj){
	if( $(obj).is(':checked') ) $('.memnos').prop('checked', true);
	else $('.memnos').prop('checked', false);
}

function mandate_print_popup(sno) {
    var printForm = $('#frmMandatePrint');
    var orderPrint = window.open('', 'mandatePrintPopup', 'width=992,height=600,menubar=yes,scrollbars=yes');
	$('#frmMandatePrint input[name=\'sno\']').val(sno);
    printForm.attr('target', 'mandatePrintPopup');
    printForm.submit();
};
</script>
<!-- 프린트 출력을 위한 form -->
<form id="frmMandatePrint" name="frmMandatePrint" action="./mandate_print.php" method="post" class="display-none">
    <input type="hidden" name="sno" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->