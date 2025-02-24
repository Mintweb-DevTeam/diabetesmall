<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <!--input id="regph" type="submit" value="메디컬샵 등록" class="btn btn-red-line" id="btnJoin"/-->
</div>



<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit" autocomplete="off">
    <div class="table-title">
        리브레 고객정보 검색
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
        </tr>
    </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button">
        </div>
    </div>
</form>







<table class="table table-rows">
	<thead>
		<th>리브레 회원번호</th>
		<th>아델라 회원번호</th>
		<th>이름</th>
		<th>연락처</th>
		<th>이메일</th>
		<th>약국코드</th>
		<th>접속경로</th>
		<th>가입일시</th>
		<th>명세표출력</th>
		<th>정보수집</th>
		<th>마케팅/광고</th>
		<th>3자 개인정보</th>
		<th>3자 민감정보</th>
	</thead>
	<tbody>
		<?php
		if( count($data) ) :
			foreach($data as $row) :
		?>
		<tr>
			<td class="text-center"><?=$row['abbott_sno']?></td>
			<td class="text-center"><?=$row['memNo']?></td>
			<td class="text-center"><?=$row['memNm']?></td>
			<td class="text-center"><?=$row['cellPhone']?></td>
			<td class="text-center"><?=$row['email']?></td>
			<td class="text-center"><?=$row['pharmacy_code']?></td>
			<td class="text-center"><?=$row['device']?></td>
			<td class="text-center"><?=$row['entryDt']?></td>
			<td class="text-center"><?=$row['is_print']?></td>
			<td class="text-center"><?=$row['Info_collection']?></td>
			<td class="text-center"><?=$row['marketing_reception']?></td>
			<td class="text-center"><?=$row['third_party_privacy']?></td>
			<td class="text-center"><?=$row['third_party_sensitive']?></td>
		</tr>
		<?php
			endforeach;
		else :
		?>
		<tr>
			<td colspan="13" class="text-center">검색된 결과가 없습니다.</td>
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
	window.location.href = './co_abotte_excel_down.php?sDate='+$('input[name="sDate"]').val()+'&eDate='+$('input[name="eDate"]').val();
}
</script>