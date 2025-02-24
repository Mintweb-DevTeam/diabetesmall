<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>
<div class='table-title'>구매회원 검색</div>
<form name='sfrm' method='get'>
	<table class='table table-cols'>
		<colgroup>
			<col class="width-md"/>
			<col>
			<col/>
		</colgroup>
		<tbody>
			<tr>
				<th>검색어</th>
				<td>
					<div class="form-inline">
						<select class="form-control " id="sopt" name="sopt">
						<option value="memNm"<?php if ($search['sopt'] == 'memNm') echo " selected";?>>이름</option>
						<option value="cellPhone"<?php if ($search['sopt'] == 'cellPhone') echo " selected";?>>휴대폰</option>
						</select>
						<input type="text" name="skey" value="<?=$search['skey']; ?>" class="form-control">
					</div>
				</td>
			</tr>
			<tr>
				<th>기간검색</th>
				<td>
					<div class="form-inline">
						<select name="searchDateFl" class="form-control">
						<option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
						</select>

						<div class="input-group js-datepicker">
						<input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" />
						<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
					</div>
					~
					<div class="input-group js-datepicker">
						<input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" />
						<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
						</div>
						<?= gd_search_date($search['searchPeriod']) ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<div class='center'><input type='submit' value='검색하기' class='btn btn-lg btn-black'></div>
	</br>
	<div class="table-header">
		<div class="pull-left">
			검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
			전체 <strong><?=number_format($page->recode['amount']);?></strong>개
		</div>
		<div class="pull-right" style="margin-bottom:10px;">
			<div class="form-inline">
				<?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value($page->page['pageNumList']), '개 보기', $page->page['list']); ?>
					
				<input type='button' value='구매회원 등록하기' class='btn btn-white buy_member_register'>
				<input type='button' value='구매회원 선택삭제' class='btn btn-red buy_member_delete'>
			</div>
		</div>   
	</div>
</form>

<form name="MemberListForm" method='post' action='../member/indb_buy_member.php' target='ifrmProcess' autocomplete='off'>
<input type='hidden' name='mode' value=''>

<table class='table table-rows'>
	<colgroup>
		<col width="5%"/>
		<col width=""/>
		<col width=""/>
		<col width="15%"/>
		<col width="15%"/>
	</colgroup>
	<thead>
		<tr>
			<th width='20' class='center'><input type='checkbox' class='js-checkall' data-target-name='chk'></th>
			<th width='60' class='center'>구매가능 회원 이름</th>
			<th width='100' class='center'>구매가능 회원 휴대폰</th>
			<th>등록일</th>
			<th>관리</th>
		</tr>
	</thead>
  <tbody>
<?php if (gd_isset($list)) { ?>
<?php foreach ($list as $key => $li) { ?>
<tr>
	<td class='center' nowrap>
		<input type='checkbox' name='chk[]' value='<?=$li['idx']?>'>
	</td>
	<td><?=$li['memNm']?></td>
	<td><?=preg_replace("/(\d{3})(\d{4})(\d{4})/", "$1-$2-$3", $li['cellPhone'])?></td>
	<td class='center' nowrap><?=$li['regDt']?></td>
	<td class='center' nowrap><span class="btn btn-white" onclick="document.location.href='../member/buy_member_register.php?idx=<?=$li['idx']?>'">수정</span> <span class="btn btn-red delete">삭제</span></td>
	
</tr>
<?php }

}else{ ?>
	<tr>
		<td colspan="5" class='center' nowrap>등록된 구매회원이 없습니다.</td>
	</tr>
	
<?php }?>

   </tbody>
</table>
<div class='table-action form-inline' style='padding-left: 10px;'>
	<input type='button' value='구매회원 등록하기' class='btn btn-white buy_member_register'>
	<input type='button' value='구매회원 선택삭제' class='btn btn-red buy_member_delete'>	
	<button type="button" class="btn btn-white btn-icon-excel buy_member_excel">구매회원 엑셀등록</button>	
</div>
<div class="text-center"><?=$page->getPage();?></div>
</form>
<script type="text/javascript">
	$(function(){
		$(".buy_member_register").click(function(){
			
			$("input[name='mode']").val("");
			document.location.href="buy_member_register.php";
		
		
		});
		
		$(".buy_member_excel").click(function(){
			
			$("input[name='mode']").val("");
			document.location.href="buy_member_register.php?mode=excel";
		
		
		});
		
		
		$(".buy_member_delete").click(function(){
			
			if($("input:checkbox[name='chk[]']:checked").length<=0){
			
				alert("삭제할 구매회원을 선택해주세요.");
				return false;
			}
			
			if(confirm("해당 구매회원을 삭제합니다. 삭제후에는 복구할수 없습니다. 그래도 진행하시겠습니까?") == true){
				$("input[name='mode']").val("loop_delete");
				
				document.MemberListForm.submit();
			}			
			
		});
		
		
		$(".delete").click(function(){
			$("input[name='mode']").val("");
			if(confirm("해당 구매회원을 삭제합니다. 삭제후에는 복구할수 없습니다. 그래도 진행하시겠습니까?") == true){
				let idx = $(this).closest("tr").find("input:checkbox[name='chk[]']").val();
				$.ajax({
					url:"indb_buy_member.php",
					method:"POST",
					data:{idx:idx,mode:"delete"}
				
				})
				.done(function(res){
				
					if(res.result == 1){
						alert('삭제되었습니다.');
						setTimeout(function(){
							document.location.reload();
						},1000);
					}else{
						alert('삭제 오류입니다.');
					}
				})
			
			}
		});
		
		$("select[name='pageNum']").change(function(){
		
			document.sfrm.submit();
			
		});
	
	});

</script>