<style>
	.btn-group{text-align:center;}
	.proxy_list td{text-align:center;}
</style>
<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>

<form id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
	<div class="search-detail-box form-inline">
	<table class="table table-cols">
        <colgroup>
            <col class="20%">
            <col>

        </colgroup>
		<tbody>
			<tr>
				<th>위임일</th>
				<td>
					<div class="input-group js-datepicker" >
						<input type="text" class="form-control" placeholder="" name="entryDt[]"
							   value="<?= gd_isset($entryDt[0]); ?>" />
						<span class="input-group-addon">
							<span class="btn-icon-calendar">
							</span>
						</span>
					</div>
					~
					<div class="input-group js-datepicker" >
						<input type="text" class="form-control" placeholder="" name="entryDt[]"
							   value="<?= gd_isset($entryDt[1]); ?>" />
						<span class="input-group-addon">
							<span class="btn-icon-calendar">
							</span>
						</span>
					</div>
					<?php  echo gd_search_date('', 'entryDt', true); ?>
				</td>
			</tr>
			<tr>
				<th>검색어</th>
				<td>
					<select name="skey" class="form-control">
						<option value="all" <?php if($skey=="all" || empty($skey))echo"selected";?>>통합검색</option>
						<option value="memId"<?php if($skey=="memId")echo"selected";?>>신청자ID</option>
						<option value="pname"<?php if($skey=="pname")echo"selected";?>>가입자명</option>
					</select>
					<input type="text" name="searchString" class="form-control" value="<?=$searchString?>"/>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
	</div>
</form>
<form id="list_form" method='post' action='proxy_indb.php' autocomplete='off' target='ifrmProcess'>
<div class='table-title'>검색<?=$amount?>개/전체<?=$total?>개</div>
<table class="table table-cols proxy_list">
	<colgroup>
		<col width="5%"/>
		<col width="5%"/>
		<col width="15%"/>
		<col width="15%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="15%"/>
		<col width="15%"/>
		<col width=""/>
	</colgroup>
	<thead>
		<tr>
			<th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/></th>
			<th>NO</th>
			<th>위임일</th>
			<th>만료일</th>
			<th>신청자ID</th>
			<th>가입자명</th>
			<th>전화번호</th>
			<th>주민등록증</th>
			<th>위임장</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $key =>$val){?>
		<tr>
			<td><input type="checkbox" name="chk[]" value="<?= $val['idx']; ?>"/></td>
			<td><?=$page->idx--?></td>
			<td><?=$val['regDt']?></td>
			<td><?=$val['regDt']?></td>
			<td><?=$val['memId']?></td>
			<td><?=$val['pname']?></td>
			<td><?=$val['cellPhone']?></td>
			<td>
				<a href="proxy_img_down.php?&applyidx=<?=$val['idx']?>&fname=<?=$val['upfile']?>&name=<?=$val['pname']?>" class="btn btn-black"/>다운로드</span></a>
				<a href="javascript:window.open('down_log.php?mode=j&idx=<?=$val['idx']?>','down_log','width=500,height=500')"><span class='btn btn-white' >다운로드이력</span></a>
			</td>
			<td>
				<span class="btn btn-black" onclick="print_('<?=$val['idx']?>')">저장및인쇄</span>
				<a href="javascript:window.open('down_log.php?mode=p&idx=<?=$val['idx']?>','down_log','width=500,height=500')"><span class='btn btn-white' >인쇄이력</span></a>
			</td>
		</tr>
		<?php }?>
	</tbody>
</table>

<div class="text-center">
	<?= $page->getPage(); ?>
</div>
<div>
	선택한 신청건을<input type='button' value='삭제' class='btn btn-red' onclick="apply_del();">
</div>
</form>
<script type="text/javascript">
	function apply_del(){
	
		var chk=0;
		if($("input:checkbox[name='chk[]']:checked").length<=0){
			alert("삭제한 요양비지급청구위임건을 선택해주세요.");
			return false;
		}

		if(confirm("삭제후에는 복구할수 없습니다.")==true){
			
			$("#list_form").submit();
		}
	}

	function print_(sno){
		window.open("proxy_apply_print.php?sno="+sno,"proxy","width=700,height=900;")
	}
</script>