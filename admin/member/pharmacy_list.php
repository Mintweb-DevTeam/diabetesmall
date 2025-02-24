<script src="/admin/script/jquery.qrcode.min.js"></script>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input id="regph" type="submit" value="메디컬샵 등록" class="btn btn-red-line" id="btnJoin"/>
</div>
<script>
var view_qr = function(obj){
	$('#output').qrcode({ 
		width: 3000,
		height: 3000,
		text: 'http://m.diabetesmall.co.kr/Qrcode/?code='+$(obj).data('code')+'&device=qr'
    });
	$('#qr-name').text( $(obj).data('name') );
	var canvas = $('#output canvas');
    var img = canvas.get(0).toDataURL("image/png");
	$('#qrcod').prop('title', $(obj).data('name'));
	$('#qrcod').prop('src', img);
	$('#bnt-hide').trigger('click');
}
$(function(){
	$('select[name="pageNum"]').on('change', function(){
		$('input[name="pageNum"]').val( $(this).val() );
		$('#frmSearchBase').submit();
	})
	$('#regph').click(function(){
		window.location.replace("/member/pharmacy_reg.php");
	});
	
})

//2023.08.08웹앤모바일 메디컬샵 삭제기능추가
function mediDel(no){
	if(confirm("해당 메디컬샵을 삭제하시겠습니까?삭제후에는 복구되지 않습니다.")==true){
		$.ajax({
			method:"POST",
			url	: "wm_in_db.php",
			data:{no:no}
		})
		.done(function(msg){
			alert("삭제되었습니다.");
			document.location.reload();
		})
	}
}
</script>
<button type="hidden" id="bnt-hide" data-toggle="modal" data-target="#exampleModal" ></button>
<div class="modal fade" id="exampleModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="table-title" id="exampleModalLabel">QR Code 보기</h4>
      </div>
      <div class="modal-body text-center">
		  <div class="table-title" id="qr-name">약국명</div><br>
		  <img src="" id="qrcod" title="" style="width: 150px">
		  <div id="output" class="hide"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-lg btn-black" data-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>





<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=$get['pageNum']?>">
    <div class="table-title">
        메디컬샵 검색
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
                    <option value="name" <?=($get['filde'] == 'name') ? 'selected' : ''?>>메디컬샵명</option>
                    <option value="code" <?=($get['filde'] == 'code') ? 'selected' : ''?>>코드</option>
                    <option value="phone" <?=($get['filde'] == 'phone') ? 'selected' : ''?>>전화번호</option>
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
            전체 <strong><?=$total?></strong> 개
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
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <!--col class="width-xs"/>
			<col class="width-xs"/-->
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>코드</th>
            <th>메디컬샵명</th>
            <th>전화번호</th>
            <th>우편번호</th>
            <th>주소</th>
            <th>상세주소</th>
            <th>정보수정</th>
			<!--<th>QR Code</th> -->
        </tr>
        </thead>
        <tbody>
			<?php if( is_array($list) ) foreach( $list as $row ) { ?>
            <tr>
                <td class="center"><?=$row['No']?></td>
                <td class="center"><?=$row['code']?></td>
                <td class="center"><?=$row['name']?></td>
                <td class="center"><?=$row['phone']?></td>
                <td class="center"><?=$row['post']?></td>
                <td class="center"><?=$row['address1']?></td>
                <td class="center"><?=$row['address2']?></td>
                <td class="center"><a href="./pharmacy_reg.php?sno=<?=$row['sno']?>" class="btn btn-white btn-sm user-board">수정</a> 
				<!--2023.08.08웹앤모바일 메디컬샵 삭제기능추가-->
				<a href="javascript:mediDel('<?=$row['sno']?>');" class="btn btn-red btn-sm">삭제</a></td>
				<!--<td class="center"><button type="button" onClick="view_qr(this)" data-name="<?=$row['name']?>" data-code="<?=$row['code']?>" class="btn btn-white btn-sm">보기</button></td>-->
            </tr>
			<?php } else { ?>
            <tr><td class="center" colspan="9">검색된 정보가 없습니다.</td></tr>
			<?php } ?>
        </tbody>
    </table>

    <div class="table-action clearfix">
        <div class="pull-right">
			<a href="./co_excel_down.php" class="btn btn-white btn-icon-excel">엑셀다운로드</a>
        </div>

    </div>

    <div class="center">
		<?=$page?>
    </div>