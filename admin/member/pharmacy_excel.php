<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>
<div>
	<div class="table-title">메디컬샵 엑셀 업로드</div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-sm"/>
			<col/>
		</colgroup>
		<tr>
			<th>엑셀파일 업로드</th>
			<td>
				<div class="form-inline">
					<input type="file" id="file" name="excel" class="form-control" accept="application/vnd.ms-excel" />
					<button type="button" onClick="ajaxUp()" class="btn btn-sm btn-white">엑셀업로드</button>
				</div>
				<div class="notice-danger mgt10">
					sno 가 기입된 메디컬샵은 코드 및 모든 데이타가 변경 됩니다.
				</div>
				<div class="notice-info">
					메디컬샵 코드가 없을 경우 등록되지 않습니다.
				</div>
			</td>
		</tr>
	</table>
</div>
<style>
.excel-up{margin-top: 50px; border-top: 1px solid #888;padding-top: 15px}
.excel-danger{color: #fa2828; background: url(/admin/gd_share/img/icon_notice_red.png) no-repeat left 4px; width: 40px; padding-left: 15px; display: inline-block}
.excel-td{text-align: center; position: relative}
.hide{display: none}
</style>
<script>
var type = 'feed',
	wrong = false,
	overlap = '<br><div class=\'excel-danger\'>중복</div>',
	necessary = '<div class=\'excel-danger\'>필수</div>',
	trs = '<tr><td class="excel-td"><input name="sno[%index%]" type="hidden" value="%sno%">%sno%</td><td class="excel-td"><input name="code[%index%]" type="hidden" value="%code%">%code%</td><td class="excel-td"><input name="name[%index%]" type="hidden" value="%name%">%name%</td><td class="excel-td"><input name="phone[%index%]" type="hidden" value="%phone%">%phone%</td><td class="excel-td"><input name="post[%index%]" type="hidden" value="%post%">%post%</td><td class="excel-td"><input name="address1[%index%]" type="hidden" value="%address1%">%address1%</td><td class="excel-td"><input name="address2[%index%]" type="hidden" value="%address2%">%address2%</td></tr>';
function ajaxUp(){
	if( !$("#file").val() ){
		alert('선택된 파일이 없습니다.');
		return;
	}
	var fd = new FormData();
	fd.append( 'excel', $('#file')[0].files[0]);
	fd.append( 'type', type );
	$.ajax({
		url:'./excel_read.php',
		dataType:"json",
		cache:false,
		contentType:false,
		processData:false,
		data:fd,
		type:'POST',
		success: function(x){
			// console.log(x);
			if(x.code != 200){
				alert(x.msg);
			}else{
				$('#excel-body').html('');
				$('.excel-up').removeClass('hide');
				var max_ = x.data.length;
				for(var i = 0; i<max_; i++){
					var code = '', name = '', sno = '';
					if(x.data[i].codeover == 'f'){
						code = $.trim(x.data[i].code)+overlap;
						wrong = true;
					}else{
						code = $.trim(x.data[i].code);
						if( $.trim(code) == '' ){
							code = necessary+code;
							wrong = true;
						}else{
							code = $.trim(code);
						}
					}
					if( $.trim(x.data[i].name) == '' ){
						name = necessary;
						wrong = true;
					}else{
						name = $.trim(x.data[i].name);
					}
					if(x.data[i].snoover){
					   sno = $.trim(x.data[i].sno)+overlap;
						wrong = true;
					}else{
						sno = $.trim(x.data[i].sno);
					}
					$('#excel-body').append(
						trs.co_split({
							'index':i,
							'sno':sno,
							'code':code,
							'name':name,
							'phone':$.trim(x.data[i].phone),
							'post':$.trim(x.data[i].post),
							'address1':$.trim(x.data[i].address1),
							'address2':$.trim(x.data[i].address2),
						}));
				}
			}
		}
	});
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
function submit_(){
	if(wrong){
		alert('필수값 및 중복값을 확인해 주세요');
		return;
	}
	$('#frm').submit();
}
</script>
<div class="excel-up hide">
	<div class="table-title">파일 업로드 확인</div>
	<iframe name="frm_" style="display: none"></iframe>
	<form id="frm" method="post" action="./pharmacy_excel_ps.php" target="frm_" >
	<table class="table table-rows">
		<thead>
			<th>sno</th>
			<th>code</th>
			<th>name</th>
			<th>phone</th>
			<th>post</th>
			<th>address1</th>
			<th>address2</th>
		</thead>
		<tbody id="excel-body">
		</tbody>
	</table>
	</form>
	<center>
	<button type="button" onClick="submit_()" class="btn btn-red btn-register">저장</button>
	</center>
</div>
<div class="information">
    <div class="helper_left">
        <div class="helper_right">
            <div class="helper_bottom">
                <div class="helper_right_top">
                    <div class="helper_right_bottom">

                        <div class="content">
                            <ul class="pdl0">
                                <li>
                                    <h3>메디컬샵 엑셀 다운로드</h3>
                                    1. 아래 &quot;메디컬샵 엑셀 다운로드&quot; 버튼을 눌러 참고하시기 바랍니다.<br/> 2. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.<br/>
									<a href="./co_excel_down.php" class="btn btn-white btn-icon-excel mgt10" style="text-decoration: none">메디컬샵 엑셀 다운로드</a>
                                </li>
                                <li>
                                    <h3>메디컬샵 업로드 방법</h3>
                                    1. 아래 항목 설명되어 있는 것을 기준으로 엑셀 파일을 작성을 합니다.<br/> 2. 메디컬샵 다운로드에서 받은 엑셀이나 상단 &quot;메디컬샵 엑셀 다운로드&quot;에서 받은 엑셀을 기준으로 파일을 작성을 합니다.<br/> 3. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 절대 csv 파일이 아닌 진짜 엑셀 파일입니다.(xls)<br/> 4. 작성된 엑셀 파일을 업로드 합니다.<br/> 5. 파일 업로드를 확인 합니다.<br/> 6. 저장 버튼을 눌러 내용을 저장 합니다.
                                </li>
                                <li>
                                    <h3>주의사항</h3>
                                    1. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;만 가능하며, csv 파일은 업로드가 되지 않습니다.<br/> 2. 엑셀 2003 사용자의 경우는 그냥 저장을 하시면 되고, 엑셀 2007 이나 엑셀 2010 인 경우는 새이름으로 저장을 선택해서 &quot;Excel 97-2003 통합문서&quot;로 저장을 하십시요.<br/> 3. 엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.<br/> 4. 엑셀 파일이 작성이 완료 되었다면 하나의 메디컬샵만 테스트로 올려보고 확인후 이상이 없으시면 나머지를 올리시기 바랍니다.<br/> 5. 엑셀 내용중 &quot;1번째 줄은 DB Code&quot;, &quot;2번째 줄부터&quot; 데이터 입니다.<br/> 6. 엑셀 내용중 1번째 줄 &quot;excel DB&quot; 코드는 필수 데이타 입니다. 그리고 반드시 내용은 &quot;2번째 줄부터&quot; 작성 하셔야 합니다.<br/>7. 엑셀샘플파일 내 일부 열을 삭제하고 업로드하면 회원정보 등록 및 수정이 불가능하니 유의 바랍니다.<br/>
                                </li>
                                <li>
                                    <h3>항목 설명</h3>
                                    1. 아래 설명된 내용을 확인후 작성을 해주세요.<br/>
                                </li>
                            </ul>
                            <table class="input" style="width: 100%">
                                <colgroup>
                                    <col class="width-sm"/>
                                    <col/>
                                </colgroup>
                                <tr>
                                    <th>항목 설명</th>
                                    <td>
                                        <table class="content_table" style="width: 100%">
                                            <colgroup>
                                                <col class="width-sm"/>
                                                <col class="width-xs"/>
                                                <col/>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>한글필드명</th>
                                                <th>영문필드명</th>
                                                <th>설명</th>
                                            </tr>
                                            </thead>
                                                <tr>
                                                    <th class="desc01">자동번호</td>
                                                    <td class="desc02">sno</td>
                                                    <td>서버에서 자동으로 부여하는 자동번호 입니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">코드</td>
                                                    <td class="desc02">code</td>
                                                    <td><div class="excel-danger">필수</div> 메디컬샵코드 입니다. <div class="excel-danger" style="width: 58px; margin-left: 5px">Unique</div>한 값이 들어가야 합니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">메디컬샵명</td>
                                                    <td class="desc02">name</td>
                                                    <td><div class="excel-danger">필수</div> 메디컬샵명 입니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">전화번호</td>
                                                    <td class="desc02">phone</td>
                                                    <td>핸드폰 번호 / 전화번호 등 동일한 메디컬샵명을 구분할 수 있는 번호입니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">우편번호</td>
                                                    <td class="desc02">post</td>
                                                    <td>메디컬샵의 우편번호 입니다. 5자리 번호를 넣어야 합니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">주소</td>
                                                    <td class="desc02">address1</td>
                                                    <td>메디컬샵의 주소 입니다.</td>
                                                </tr>
                                                <tr>
                                                    <th class="desc01">상세 주소</td>
                                                    <td class="desc02">address1</td>
                                                    <td>메디컬샵의 상세주소 입니다.</td>
                                                </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>