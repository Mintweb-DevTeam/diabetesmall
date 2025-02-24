<div class='page-header js-affix'>
    <h3>
		<?php if(empty($excel)){?>
			구매회원 <?php if(empty($idx)){echo"등록";}else{echo"수정";} php?>
		<?php }else{?>
			구매회원 엑셀등록
		<?php }?>
	</h3>
</div>
<div class='table-title'>
	<?php if(empty($excel)){?>
		구매회원 <?php if(empty($idx)){echo"등록";}else{echo"수정";} php?>
	<?php }else{?>
		구매회원 엑셀등록
	<?php }?>
</div>

<form name="mform" id="mform" action="indb_buy_member.php" method="post" target="ifrmProcess" enctype="multipart/form-data">

	<?php if(empty($excel)){
		//일반등록 및 수정	
	?>
		<input type="hidden" name="idx" value="<?=$idx?>"/>
		<input type="hidden" name="mode" value=""/>
		
		<table class="table table-cols">
			<colgroup>
				<col width="10%"/>
				<col/>
			</colgroup>
			
			<tbody>
				<tr>
					<th>이름</th>
					<td>
						<input type="text" name="memNm" value="<?=$data['memNm']?>" class="form-control  width-md"/>
					</td>
				</tr>
				<tr>
					<th>휴대폰 번호</th>
					<td>
						<input type="text" name="cellPhone" value="<?=$data['cellPhone']?>"  class="form-control js-number-only width-md"/>
					</td>
				</tr>			
			</tbody>
		</table>
		<div>
			<span class="btn btn-white btn-register"><?php if(empty($idx)){echo"등록";}else{echo"수정";} php?></span>
			<span class="btn btn-black" onclick="document.location.href='../member/buy_member_list.php';">목록</span>
		</div>
	
	<?php }else if($excel == 1){
		//엑셀등록	
	?>
		<div>
			<div>
				<strong>1.샘플파일을 다운로드후 참고 바랍니다. <a href="https://diabetesmall.co.kr/data/wm_upload/member_excel/member_excel.xls">[구매가능회원 엑셀 샘플 다운로드]</a></strong>
			</div>
			<div><strong>2.업로드 가능용량은 최대 5M 이며 엑셀 파일 저장은 반드시 "Excel 97-2003 통합문서"로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.</strong></div>
		</div>
		
		<table class="table table-cols">
			<input type="hidden" name="mode" value="excel"/>
			<colgroup>
				<col width="10%"/>
				<col />
			</colgroup>
			<tr>
				<th>구매회원 엑셀파일선택</th>
				<td>
					<div class="form-inline">
						<input type="file" name="upload_excel">
						<input type="button" class="btn btn-white btn-sm excel-btn" value="엑셀업로드">
					</div>
				</td>
			</tr>
		</table>
		<div>
			<span class="btn btn-black" onclick="document.location.href='../member/buy_member_list.php';">목록</span>
		</div>		
	<?php }?>
</form>

<script type="text/javascript">
	$(function(){
		$(".btn-register").click(function(){
			
			if(!$("input[name='memNm']").val()){
				alert("이름을 입력해 주세요.");
				return false;
			}
			
			if(!$("input[name='cellPhone']").val()){
				alert("휴대폰을 입력해 주세요.");
				return false;
			}	
			
			
			$.ajax({
				url:"buy_member_double_chk.php",
				method:"POST",
				data:$("#mform").serialize()
			})
			.done(function(res){
				
				if(res.success=="ok"){
					document.mform.submit();
				}else{
					alert("동일한 이름 및 휴대폰번호로 "+res.member_count+"건이 등록되어 있습니다.");
					return false;
				}
				
			});
		});
	
	
		$('.excel-btn').click(function() {
			var fileInput = $('#filestyle-0')[0];
			var filePath = fileInput.value;

			if (!filePath) {
				alert("파일을 선택해주세요.");
				return;
			}

			var fileExtension = filePath.split('.').pop().toLowerCase();
			if (fileExtension !== 'xls') {
				alert("엑셀 파일 (.xls)만 업로드 가능합니다.");
				return;
			}

			document.mform.submit();
		});	
	});
</script>