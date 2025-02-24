<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>
<script>
$(function(){
	$('select[name="pageNum"]').on('change', function(){
		$('input[name="pageNum"]').val( $(this).val() );
		$('#frmSearchBase').submit();
	})
	$('#regph').click(function(){
		window.location.replace("/member/pharmacy_reg.php");
	});
})
</script>
<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=$get['pageNum']?>">
    <div class="table-title">
        메디컬샵 실적 검색
    </div>
    <div class="search-detail-box form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>검색기간</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="sDate" value="<?=$get['sDate']?>" autocomplete="off">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
				~
				<div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="eDate" value="<?=$get['eDate']?>" autocomplete="off">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
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
            전체 <strong><?=$total?></strong> 개
        </div>
    </div>

    <table class="table table-rows">
        <colgroup>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col class="width-xs"/>
			<col class="width-xs"/>
        </colgroup>
        <thead>
        <tr>
            <th>코드</th>
            <th>메디컬샵명</th>
            <th>전화번호</th>
            <th>우편번호</th>
            <th>주소</th>
            <th>상세주소</th>
			<th>카카오실적</th>
            <th>실적</th>
        </tr>
        </thead>
        <tbody>
			<?php if( is_array($list) ) foreach( $list as $row ) { ?>
            <tr>
                <td class="center"><?=$row['code']?></td>
                <td class="center"><?=$row['name']?></td>
                <td class="center"><?=$row['phone']?></td>
                <td class="center"><?=$row['post']?></td>
                <td class="center"><?=$row['address1']?></td>
                <td class="center"><?=$row['address2']?></td>
                <td class="center"><?=number_format($row['kakao'])?></td>
				<td class="center"><a href="#." onClick="view_now(this)" data-sdate="<?=$get['sDate']?>" data-edate="<?=$get['eDate']?>" data-code="<?=$row['code']?>" data-kakao="<?=number_format($row['kakao'])?>"><?=number_format($row['cnt'])?></a></td>
            </tr>
			<?php } else { ?>
            <tr><td class="center" colspan="8">검색된 정보가 없습니다.</td></tr>
			<?php } ?>
        </tbody>
    </table>

    <div class="table-action clearfix">
        <div class="pull-right" style="height: 30px">
			<?=($get['sDate'] || $get['eDate']) ? '<a href="./co_excel_down_performance.php?sDate='.$get['sDate'].'&eDate='.$get['eDate'].'" class="btn btn-white btn-icon-excel">엑셀다운로드</a>' : ''?>
			
        </div>

    </div>
<button type="hidden" id="bnt-hide" data-toggle="modal" data-target="#exampleModal" ></button>
<style>
.excel-btn{float: right}
</style>
<div class="modal fade" id="exampleModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="table-title" id="exampleModalLabel">실적보기
		 <div class="excel-btn">
			 
		 </div>
		 </h4>
		  
      </div>
      <div class="modal-body text-center">
		  <table class="table table-rows">
			<thead>
				<th>이름</th>
				<th>전화번호</th>
				<th>이메일</th>
				<th>카카오</th>
				<th>가입일</th>
			</thead>
			<tbody id="mem-list">
			</tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-lg btn-black" data-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

<script>
var memtr = '<tr><td>%memNm%</td><td>%cellPhone%</td><td>%email%</td><td>%kakao%</td><td>%regDt%</td></tr>';
function view_now(obj){
	$('#mem-list').html('');
	$.ajax({
		url:'./cossia_ajax.php',
		type:'POST',
		data:{'type':'pharmacy_mem', 'sdate':$(obj).data('sdate'), 'edate':$(obj).data('edate'), 'code':$(obj).data('code') },
		dataType : "json",
		success: function(x){
			var html = '',
				ecxel_btn = (x.length) ? '<a href="./co_excel_down_pcode.php?sDate='+$(obj).data('sdate')+'&eDate='+$(obj).data('edate')+'&code='+$(obj).data('code')+'" class="btn btn-white btn-icon-excel">엑셀다운로드</a>' : '';
			// excel-btn
			for(var i=0 in x){
				html += memtr.split('%memNm%').join(x[i].memNm).split('%cellPhone%').join(x[i].cellPhone).split('%email%').join(x[i].email).split('%kakao%').join(x[i].coKakaoChannel).split('%regDt%').join(x[i].regDt);
			}
			$('.excel-btn').html(ecxel_btn);
			$('#mem-list').html(html);
			$('#bnt-hide').trigger('click');
		},
		error:function(request,status,error){
			console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
	});
}
</script>