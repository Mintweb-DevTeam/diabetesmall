<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <!--input id="regph" type="submit" value="메디컬샵 등록" class="btn btn-red-line" id="btnJoin"/-->
</div>
<script>
$(function(){
	$('select[name="pageNum"]').on('change', function(){
		$('input[name="pageNum"]').val( $(this).val() );
		$('#frmSearchBase').submit();
	})
	$('#regph').click(function(){
		window.location.replace("/member/abotte_list.php");
	});
})
</script>
<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=$get['pageNum']?>">
    <div class="table-title">
        리브레 멤버쉽 검색
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
                    <option value="memNm" <?=($get['filde'] == 'memNm') ? 'selected' : ''?>>회원명</option>
                    <option value="cellPhone" <?=($get['filde'] == 'cellPhone') ? 'selected' : ''?>>전화번호</option>
                    <option value="email" <?=($get['filde'] == 'email') ? 'selected' : ''?>>이메일</option>
                </select>
                <input name="search" type="text" value="<?=$get['search']?>" class="form-control">
            </td>
        </tr>
        <tr>
            <th>동의정보</th>
            <td>
				<label><input name="agree" type="radio" value="all" onChange="agree_ch(this)" checked>전체</label>
				<label style="margin: 0 10px 0 20px"><input name="agree" type="radio" value="cho" onChange="agree_ch(this)" <?=($get['agree'] == 'cho') ? 'checked' : '' ?> >선택</label>
				
                <label><input name="privateConsignFl[20]" type="checkbox" class="agree-check" value="y" <?=($get['privateConsignFl']['20'] == 'y') ? 'checked' : '' ?> <?=($get['agree'] == 'cho') ? '' : 'disabled checked' ?>>마케팅용 정보수집 동의</label>
                <label style="margin: 0 10px"><input name="privateConsignFl[23]" type="checkbox" class="agree-check" value="y" <?=($get['privateConsignFl']['23'] == 'y') ? 'checked' : '' ?> <?=($get['agree'] == 'cho') ? '' : 'disabled checked' ?>>마케팅/광고 수신</label>
                <label ><input name="privateOfferFl[25]" type="checkbox" class="agree-check" value="y" <?=($get['privateOfferFl']['25'] == 'y') ? 'checked' : '' ?> <?=($get['agree'] == 'cho') ? '' : 'disabled checked' ?>>3자 개인정보제공</label>
                <label style="margin: 0 10px"><input name="privateOfferFl[26]" type="checkbox" class="agree-check" value="y" <?=($get['privateOfferFl']['26'] == 'y') ? 'checked' : '' ?> <?=($get['agree'] == 'cho') ? '' : 'disabled checked' ?>>3자 민감정보제공</label>
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
            전체 <strong><?=$total?></strong> 명 &nbsp;&nbsp;&nbsp;
			<span style="color: #FF0004; font-weight: bold">※ 이름을 클릭하면 상세정보를 확인 가능 합니다.</span>
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
            <col width="1%"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
			<col/>
            <col width="1%"/>
            <col width="1%"/>
            <col width="1%"/>
        </colgroup>
        <thead>
        <tr>
            <th><input type="checkbox" onChange="all_chek(this)" ></th>
            <th>이름</th>
			<th>연락처</th>
			<th>이메일</th>
			<th class="hide">필수항목</th>
			<th>마케팅용 정보수집 동의</th>
			<th>마케팅/광고 수신</th>
			<th>3자 개인정보제공</th>
			<th>3자 민감정보제공</th>
			<th>접속경로</th>
			<th>가입일시</th>
			<th>업데이트일시</th>
            <th>메일</th>
            <th>SMS</th>
            <th>정보수정</th>
        </tr>
        </thead>
        <tbody>
			<?php if( is_array($list) ) { 
		foreach( $list as $row ) {
	
			/* if($row['isJoin'] == 'y'){
				echo '<tr><td class="center">'.$row['No'].'</td><td class="center"><a href="#." onClick="view_info(this)" data-name="'.$row['memNm'].'" data-email="'.$row['email'].'" data-cellphone="'.$row['cellPhone'].'" data-isjoin="'.$row['isJoin'].'" data-regdt="'.$row['regDt'].'" data-pharmacyname="'.$row['pharmacy_name'].'" >'.$row['memNm'].'</a></td><td colspan="3">회원 전환일시 : '.$row['joinDt'].'</td><td class="center"><button type="button" class="btn btn-white btn-sm" onClick="agrred_change(this)" data-ismem="'.$row['isJoin'].'" data-sno="'.$row['sno'].'" data-memno="'.$row['memNo'].'" >수정</button></td></tr>';
			}else{*/
			?>
            <tr>
                <td class="center"><input type="checkbox" value="<?=$row['sno']?>" data-email="<?=$row['email']?>" data-cellphone="<?=$row['cellPhone']?>" class="sno"></td>
                <td class="center"><a href="#." onClick="view_info(this)" data-name="<?=$row['memNm']?>" data-email="<?=$row['email']?>" data-cellphone="<?=$row['cellPhone']?>" data-isjoin="<?=$row['isJoin']?>" data-regdt="<?=$row['regDt']?>" data-pharmacyname="<?=$row['pharmacy_name']?>" ><?=$row['memNm']?></a></td>
				<td class="center"><?=$row['cellPhone']?></td>
				<td class="center"><?=$row['email']?></td>
                <td class="hide">
					<div class="agree-div">
						<label><input name="privateApprovalOptionFl<?=$row['sno']?>19" class="co_radio<?=$row['sno']?>" data-name="privateApprovalOptionFl" data-sno="19" type="radio" value="y" checked>동의함</label>
						<label><input name="privateApprovalOptionFl<?=$row['sno']?>19" class="co_radio<?=$row['sno']?>" data-name="privateApprovalOptionFl" data-sno="19" type="radio" value="n" <?=($row['privateApprovalOptionFl'][19] == 'n') ? 'checked' : '';?> >동의안함</label>
						<p>개인정보 수집 및 이용</p>
					</div>
					<div class="agree-div">
						<label><input name="privateApprovalOptionFl<?=$row['sno']?>21" class="co_radio<?=$row['sno']?>" data-name="privateApprovalOptionFl" data-sno="21" type="radio" value="y" checked>동의함</label>
						<label><input name="privateApprovalOptionFl<?=$row['sno']?>21" class="co_radio<?=$row['sno']?>" data-name="privateApprovalOptionFl" data-sno="21" type="radio" value="n" <?=($row['privateApprovalOptionFl'][21] == 'n') ? 'checked' : '';?> >동의안함</label>
						<p>개인정보 수집 및 이용</p>
					</div>
					
					<div class="agree-div">
						<label><input name="privateOfferFl<?=$row['sno']?>5" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="5" type="radio" value="y" checked>동의함</label>
						<label><input name="privateOfferFl<?=$row['sno']?>5" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="5" type="radio" value="n" <?=($row['privateOfferFl'][5] == 'n') ? 'checked' : '';?> >동의안함</label>
						<p>개인정보 제공</p>
					</div>
					
					<div class="agree-div">
						<label><input name="privateOfferFl<?=$row['sno']?>22" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="22" type="radio" value="y" checked>동의함</label>
						<label><input name="privateOfferFl<?=$row['sno']?>22" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="22" type="radio" value="n" <?=($row['privateOfferFl'][22] == 'n') ? 'checked' : '';?> >동의안함</label>
						<p>민감정보 제공</p>
					</div>
				</td>
				<!--마케팅용 정보수집 동의-->
				<td class="center">
					<div class="agree-div">
						<label><input name="privateConsignFl<?=$row['sno']?>20" class="co_radio<?=$row['sno']?>" data-name="privateConsignFl" data-sno="20" type="radio" value="y" checked>동의함</label>
						<label style="margin-left: 10px"><input name="privateConsignFl<?=$row['sno']?>20" class="co_radio<?=$row['sno']?>" data-name="privateConsignFl" data-sno="20" type="radio" value="n" <?=($row['privateConsignFl'][20] == 'n') ? 'checked' : '';?> >동의안함</label>
					</div>
				</td>
				<!--마케팅/광고 수신-->
				<td class="center">
					<div class="agree-div">
						<label><input name="privateConsignFl<?=$row['sno']?>23" class="co_radio<?=$row['sno']?>" data-name="privateConsignFl" data-sno="23" type="radio" value="y" checked>동의함</label>
						<label style="margin-left: 10px"><input name="privateConsignFl<?=$row['sno']?>23" class="co_radio<?=$row['sno']?>" data-name="privateConsignFl" data-sno="23" type="radio" value="n" <?=($row['privateConsignFl'][23] == 'n') ? 'checked' : '';?> >동의안함</label>
					</div>
				</td>
				<td class="center">
					<div class="agree-div">
						<label><input name="privateOfferFl<?=$row['sno']?>25" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="25" type="radio" value="y" checked>동의함</label>
						<label style="margin-left: 10px"><input name="privateOfferFl<?=$row['sno']?>25" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="25" type="radio" value="n" <?=($row['privateOfferFl'][25] == 'n') ? 'checked' : '';?> >동의안함</label>
					</div>
				</td>
				<td class="center">
					<div class="agree-div">
						<label><input name="privateOfferFl<?=$row['sno']?>26" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="26" type="radio" value="y" checked>동의함</label>
						<label style="margin-left: 10px"><input name="privateOfferFl<?=$row['sno']?>26" class="co_radio<?=$row['sno']?>" data-name="privateOfferFl" data-sno="26" type="radio" value="n" <?=($row['privateOfferFl'][26] == 'n') ? 'checked' : '';?> >동의안함</label>
					</div>
				</td>
				<td class="center"><?=strtoupper($row['device'])?></td>
				<td class="center"><?=$row['regDt']?></td>
				<td class="center"><?=$row['modDt']?></td>
				<td class="center">
					<button type="button" class="btn btn-white btn-sm" onClick="send_member(this)" data-target="<?=$row['email']?>" data-methode="mail" >메일</button>
				</td>
				<td class="center">
					<button type="button" class="btn btn-white btn-sm" onClick="send_member(this)" data-target="<?=$row['cellPhone']?>" data-methode="sms" >SMS</button>
				</td>
				
                <td class="center"><button type="button" class="btn btn-white btn-sm" onClick="agrred_change(this)" data-ismem="<?=$row['isJoin']?>" data-sno="<?=$row['sno']?>" data-memno="<?=$row['memNo']?>" >수정</button></td>
            </tr>
			<?php } }/* } */else { ?>
            <tr><td class="center" colspan="13">검색된 정보가 없습니다.</td></tr>
			<?php } ?>
        </tbody>
    </table>

    <div class="table-action clearfix">
        <div class="pull-left">
			<button type="button" onClick="send_member(this)" data-target="array" data-methode="mail" class="btn btn-white">메일</button>
			<button type="button" onClick="send_member(this)" data-target="array" data-methode="sms" class="btn btn-white">SMS</button>
			<button type="button" onClick="abotte_del()" class="btn btn-white">선택삭제</button>
        </div>
        <div class="pull-right">
			<a href="./co_excel_down.php?dbname=abbottMember" class="btn btn-white btn-icon-excel">엑셀다운로드</a>
        </div>
    </div>
    <div class="center">
		<?=$page?>
    </div>
<script>
var all_chek = function(obj){
		if( $(obj).is(':checked') ) $('.sno').prop('checked', true);
		else $('.sno').prop('checked', false);
	},
	abotte_del = function(){
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
			data:{'snos':snos,'type':'del_abotte_mem'},
			dataType : "json",
			success: function(x){
				alert('처리되었습니다.');
				location.reload();
			},
			error:function(request,status,error){
				console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
		});
	},
	agree_ch = function(obj){
		if( $(obj).val() === 'all' ) $('.agree-check').prop('disabled', true);
		else $('.agree-check').prop('disabled', false);
	};
function agrred_change(obj){
	if( $(obj).data('ismem') == 'y' ){
		window.open("/share/member_crm.php?popupMode=yes&memNo="+$(obj).data('memno'), "Popup", "width=1200,height=850");
		return;
	}
	var fd = new FormData();
	fd.append( 'sno', $(obj).data('sno'));
	fd.append( 'type', 'abotte');
	$('.co_radio'+$(obj).data('sno')+':checked').each(function(){
		fd.append( $(this).data('name')+'['+$(this).data('sno')+']', $(this).val() );
	});
	$.ajax({
		url:'./member_agree_ajax.php',
		dataType:"text",
		cache:false,
		contentType:false,
		processData:false,
		data:fd,
		type:'POST',
		success: function(x){
			alert('처리되었습니다.');
			location.reload();
		},
		error:function(request,status,error){
			alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
       	}
	});
	
}
	
	
	
	
var trs = '<tr><th>이름</th><td>%name%</td></tr><tr><th>이메일</th><td>%email%</td></tr><tr><th>휴대폰번호</th><td>%cellphone%</td></tr><tr><th>회원전환</th><td>%isjoin%</td></tr><tr><th>메디컬샵</th><td>%pharmacyname%</td></tr><tr><th>등록일시</th><td>%regdt%</td></tr>';
function view_info(obj){
	var html = trs.co_split({ 'name':$(obj).data('name'), 'email':$(obj).data('email'), 'cellphone':$(obj).data('cellphone'), 'isjoin':$(obj).data('isjoin'), 'regdt':$(obj).data('regdt'), 'pharmacyname':$(obj).data('pharmacyname') });
	$('#tbody').html(html);
	$('#bnt-hide').trigger('click');
}
String.prototype.co_split = String.prototype.co_split || function () { 
    "use strict";
    var str = this.toString();
    if (arguments.length) {
        var t = typeof arguments[0];
        var key;
        var args = ("string" === t || "number" === t) ? Array.prototype.slice.call(arguments) : arguments[0];
        for (key in args) {
            str = str.replace(new RegExp("\\%" + key + "\\%", "gi"), args[key]);
        }
    }
    return str;
};
var send_target = new Array(),
	send_methode = '';
function send_member(obj){
	var text = ( $(obj).data('methode')  === 'mail' ) ? '메일' : 'SMS',
		target = ( $(obj).data('target') !== 'array' ) ? $(obj).data('target') : null,
		target_text = '';
	send_target = new Array();
	send_methode = $(obj).data('methode');
	if(!target){
		if( $('.sno:checked').length === 0 ){
			alert('선택된 회원이 없습니다.');
			return;
		}
		$('.sno:checked').each(function(){
			send_target.push( (send_methode === 'mail') ? $(this).data('email') : $(this).data('cellphone') );
		});
	}else send_target.push( target );
	if( send_target.length > 5 ){
		var texts = [];
		for(var i=0; i<5; i++) texts.push( send_target[i] );
		target_text = texts.join(', ')+' 외 '+(send_target.length - 5)+' 개';
	}else{
		target_text = send_target.join(', ');
	}
	$('#exampleModal2Label').text(text+' 발송');
	$('#receive').text( target_text );
	$('#send-content').val('');
	$('#bnt-sms').trigger('click');
}
function send_this(){
	if( $('#send-content').val().trim() === '' || $('#send-title').val().trim() === '' ){
		alert('제목, 내용은 필수 입니다.');
		return;
	}
	$.ajax({
		url:'./cossia_ajax.php',
		type:'POST',
		data:{'send_target':send_target, 'send_title' : $('#send-title').val(), 'send_content' : $('#send-content').val(), 'send_methode' : send_methode, 'type':'send_to_member'},
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
<button type="hidden" id="bnt-hide" class="hide" data-toggle="modal" data-target="#exampleModal" ></button>
<div class="modal fade" id="exampleModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="table-title" id="exampleModalLabel">회원 상세 정보</h4>
      </div>
      <div class="modal-body">
<div class="form-inline">
    <table class="table table-cols">
        <colgroup>
            <col width="20%"/>
            <col/>
        </colgroup>
		<tbody id="tbody">
		</tbody>
    </table>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

<button type="hidden" id="bnt-sms" class="hide" data-toggle="modal" data-target="#exampleModal2" ></button>
<div class="modal fade" id="exampleModal2" tabindex="1" role="dialog" aria-labelledby="exampleModal2Label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="table-title" id="exampleModal2Label"></h4>
      </div>
      <div class="modal-body">
		  <table class="table table-cols">
			  <colgroup>
				  <col width="100px">
				  <col width="*">
			  </colgroup>
			  <tr>
				  <th>수신</th><td id="receive"></td>
			  </tr>
			  <tr>
				  <th>제목</th><td><input id="send-title" type="text" style="width: 70%"></td>
			  </tr>
			  <tr>
				  <th>내용</th><td><textarea id="send-content" style="width: 100%; height: 150px; resize: none"></textarea></td>
			  </tr>
		  </table>
      </div>
      <div class="modal-footer">
		<button type="button" class="btn btn-secondary" onClick="send_this()" >전송</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

