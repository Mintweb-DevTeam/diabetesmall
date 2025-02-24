<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>

<form action="?" id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<input name="pageNum" type="hidden" value="<?=Request::get()->get('pageNum')?>">
    <div class="table-title">
        검색
    </div>
    <div class="search-detail-box form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>검색어</th>
            <td>
				<?= gd_select_box('key', 'key', $combineSearch, null, Request::get()->get('key'), null, null, 'form-control'); ?>
                <input name="keyword" type="text" value="<?=Request::get()->get('keyword')?>" class="form-control" autocomplete="off">
            </td>
        </tr>
		<tr>
            <th>검색기간</th>
            <td>
				<div class="input-group js-datepicker">
                    <input type="text" class="form-control" name="sDate"
                           value="<?=Request::get()->get('sDate')?>" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div> ~
				<div class="input-group js-datepicker">
                    <input type="text" class="form-control" name="eDate" value="<?=Request::get()->get('eDate')?>" autocomplete="off">
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
	<thead>
		<th>상세보기</th>
		<th>민감정보동의일</th>
		<th>관리번호</th>
		<th>환자명</th>
		<th>생년월일</th>
		<th>법정대리인</th>
		<th>성별</th>
		<th>회원아이디</th>
		<th>전화번호</th>
		<th>원본파일</th>
		<th>정리파일</th>
	</thead>
	<tbody>
		<?php
		if( isset($data) && count($data) ) :
			foreach( $data as $row ) :
		?>
		<tr>
			<td class="text-center"><a href="javascript:view_detail(<?=$row['sno']?>)" class="glyphicon glyphicon-new-window"></a></td>
			<td class="text-center"><?=$row['agreeDt']?></td>
			<td class="text-center"><?=$row['controlNo']?></td>
			<td class="text-center"><?=$row['patientName']?></td>
			<td class="text-center"><?=$row['patientBirth']?></td>
			<td class="text-center"><?=$row['agent']?></td>
			<td class="text-center"><?=($row['gender'] == 'm')?'남':'여'?></td>
			<td class="text-center"><?=$row['memId']?></td>
			<td class="text-center"><?=$row['cellPhone']?></td>
			<td class="text-center">
				<a href="./blood_file_down.php?sno=<?=$row['sno']?>" class="btn-icon-excel"></a>
			</td>
			<td class="text-center">
				<a href="javascript:data_down(<?=$row['sno']?>)" class="btn-icon-excel"></a>
			</td>
		</tr>
		<?php
			endforeach;
		else :
		?>
		<tr>
			<td class="text-center" colspan="11">검색결과가 없습니다.</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
<div class="table-action clearfix">
	<div class="pull-left">
		&nbsp;
	</div>
	<div class="pull-right">
	</div>
</div>
<div class="center">
	<?= $page->getPage();?>
</div>
<style>
.bootstrap-dialog-message {height: 400px;}
.modal-table {width:100%; margin:0 auto;overflow:hidden; border-top: none}
.modal-table thead{float:left; width: calc( 100% - 17px )}
.modal-table thead tr{display:flex;width:100%;}
.modal-table thead th{flex: 1}

.modal-table tbody {float:left;width:100%;height:229px;overflow-y:auto;overflow-x:hidden;}
.modal-table tbody tr{display:flex;width:100%; }
.modal-table tbody td{flex: 1; border-right:0 !important}
.modal-table tbody tr:last-child{border-bottom:none;}

.modal-table tbody td{text-align:center;}
</style>

<script>
$(function(){
	$('select[name="pageNum"]').on('change', function(){
		$('input[name="pageNum"]').val( $(this).val() );
		$('#frmSearchBase').submit();
	});
})
const top_table = `<table class="table table-cols">
						<colgroup>
							<col class="width-sm">
							<col>
						</colgroup>
						<tr>
							<th>관리번호</th><td>%controlNo%</td>
						</tr>
						<tr>
							<th>성명</th><td>%patientName%</td>
						</tr>
						<tr>
							<th>생년월일</th><td>%patientBirth%</td>
						</tr>
					</table>`,
	  body_table = `<table class="table table-rows modal-table">
						<thead>
							<th>측정일자</th>
							<th>측정시간</th>
							<th>측정혈당값(mb/dl)</th>
						</thead>
						<tbody>
							%body%
						</tbody>
					</table>`,
	  body = `<tr>
				<td>%date%</td>
				<td>%time%</td>
				<td>%mg%</td>
			  </tr>`,
data_down = (sno) => {
	location.href=`./blood_data_down.php?sno=${sno}`;
},
view_detail=(sno)=>{
	const data = real_ajax(sno);
	let body_ = '',
		html = top_table.co_split({
			controlNo: data.controlNo,
			patientName: data.patientName,
			patientBirth: data.patientBirth
		});;
	for(let i in data.fileData){
		body_ += body.co_split({
			date: data.fileData[i].date,
			time: data.fileData[i].time,
			mg: data.fileData[i].mg
		});
	}
	html += body_table.co_split({body: body_});
	BootstrapDialog.show({
		title: '혈당정보제공 상세보기',
		message: html,
		closable: false,
		// draggable: true,
		buttons: [{
			label: ' 닫기',
			icon: 'glyphicon glyphicon-ban-circle',
			cssClass: 'btn-black',
			action: function(dialog) {
				dialog.close();
			}
		},{
			label: ' 다운로드',
			icon: 'glyphicon glyphicon-download-alt',
			// cssClass: 'btn-danger',
			action: function(dialog) {
				data_down(sno);
			}
		}]
	});
},
real_ajax=(sno)=>{
	var jqXHR = $.ajax({
		url:'./blood_ajax.php',
		type:'POST',
		dataType:"json",
		async: false,
		data:{sno: sno},
		success: function(d){}
	});
	return jqXHR.responseJSON;;
}

String.prototype.co_split = String.prototype.co_split || function () { 
    "use strict";
    let str = this.toString();
    if (arguments.length) {
        let t = typeof arguments[0],
            key,
            args = ("string" === t || "number" === t) ? Array.prototype.slice.call(arguments) : arguments[0];
        for (key in args) {
            str = str.replace(new RegExp("\\%" + key + "\\%", "gi"), args[key]);
        }
    }
    return str;
};

</script>