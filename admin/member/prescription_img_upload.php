<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>

<form name="sform" method="post">

	<div class="search-detail-box form-inline">
		<table class="table table-cols">
			<colgroup>
				<col class="20%">
				<col>

			</colgroup>
			<tbody>
				<tr>
					<th>처방전 등록일</th>
					<td>
						<div class="input-group js-datepicker">
							<input type="text" class="form-control" placeholder="" name="regDt[]" value="<?=$regDt[0]?>">
							<span class="input-group-addon">
								<span class="btn-icon-calendar">
								</span>
							</span>
						</div>
						~
						<div class="input-group js-datepicker">
							<input type="text" class="form-control" placeholder="" name="regDt[]" value="<?=$regDt[1]?>">
							<span class="input-group-addon">
								<span class="btn-icon-calendar">
								</span>
							</span>
						</div>
						<?php  echo gd_search_date('', 'regDt', true); ?>
				</tr>
				<tr>
					<th>검색어</th>
					<td>
						<select name="skey" class="form-control">
							<option value="all" <?php if($skey=="all" || empty($skey))echo"selected";?>>통합검색</option>
							<option value="memId" <?php if($skey=="memId")echo"selected";?>>회원ID</option>
							<option value="memNm" <?php if($skey=="memNm")echo"selected";?>>회원명</option>
							<option value="email" <?php if($skey=="email")echo"selected";?>>이메일</option>
							<option value="cellPhone" <?php if($skey=="cellPhone")echo"selected";?>>휴대폰</option>
						</select>
						<input type="text" name="searchString" class="form-control" value="<?=$searchString?>">
					</td>
				</tr>
			</tbody>
		</table>
	</div>	
	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
	</div>	
</form>

<form name="dataForm" id="dataForm" method="post">
	<table class="table table-cols">
		<colgroup>
			<col width="5%"/>
			<col width="10%"/>
			<col width="15%"/>
			<col width="15%"/>
			<col width="15%"/>
			<col width=""/>
			<col width="15%"/>
		</colgroup>
		<thead>
			<tr>
				<th> <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/></th>
				<th>회원명</th>
				<th>회원ID</th>
				<th>이메일</th>
				<th>휴대폰번호</th>
				<th>처방전파일</th>
				<th>처방전등록일</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			
			foreach($data as $key => $val){?>
			<tr>
				<td align="center"><input type="checkbox" name="chk[]" value="<?=$val['idx']?>"/></td>
				<td align="center"><?=$val['memNm']?></td>
				<td align="center"><?=$val['memId']?></td>
				<td align="center"><?=$val['email']?></td>
				<td align="center"><?=$val['cellPhone']?></td>
				<td><?=$val['ori_file_name']?> <a href="./prescription_file_down.php?file_name=<?=$val['file_name']?>"><span class="btn btn-red">다운로드</span></a></td>
				<td align="center"><?=$val['regDt']?></td>
			</tr>
			<?php }?>
		</tbody>
	</table>
	<div class="table-action clearfix">
		<div class="pull-left">
			<button type="button" class="btn btn-red delete_btn">선택삭제</button>
		</div>
		<div class="pull-right">
			<button type="button" class="btn btn-white btn-icon-excel excelDownBtn" data-target-form="frmSearchBase" data-search-count="0" data-total-count="9843" data-target-list-form="frmList" data-target-list-sno="chk">엑셀다운로드</button>
		</div>
	</div>
	<div class="text-center">
		<?= $page->getPage(); ?>
	</div>	
</form>

<form name="excelForm" id="excelForm" method="post" action="prescription_img_upload_indb.php" target="ifrmProcess">
	<input type="hidden" name="qry" value="<?=$strSQL?>"/>
	<input type="hidden" name="mode" value="excel_down"/>
</form>
<script>
    $(document).ready(function () {

		$(".delete_btn").on("click",function(){
			
			if($("input:checkbox[name='chk[]']:checked").length<=0){
			
				alert("삭제할 데이터를 선택한세요.");
				return false;
			}
			
			
			if(confirm("선택한 자료를 정말 삭제하시겠습니까?") == true){
				let param = $("#dataForm").serialize();
				param+="&mode=del";
				
				$.ajax({
					url:"prescription_img_upload_indb.php",
					method:"POST",
					data:param
				})
				.done(function(msg){
					alert("삭제 되었습니다.");
					document.location.reload();
				});
			}
		
		});
		
		$(".excelDownBtn").on("click",function(){
			$("#excelForm").submit();
		});
	});
</script>
