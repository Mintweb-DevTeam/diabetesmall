<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="submit" value="완료" class="btn btn-red-line"/>
</div>
<script>
$(function(){
	$('input[name="code"]').on('change', function(){
		if( $(this).val() ){
			$.ajax({
				url:'./cossia_ajax.php',
				type:'POST',
				data:{'type':'check_pharm_code', 'code' : $(this).val()},
				dataType : "json",
				success: function(x){
					if( x.cnt != 0 ){
						alert('사용이 불가능한 코드 입니다.');
						$('input[name="code"]').val('');
						return;
					}
				},
				error:function(request,status,error){
					console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
				}
			});
		}
	});
	$('input[type="submit"]').click(function(){
		if( !$('input[name="code"]').val() || !$('input[name="name"]').val() ){
			alert('코드와 메디컬샵명은 필수 입니다.');
			return;
		}
		$('#frm').submit();
	})
})
</script>
<div class="form-inline">
	<iframe name="frm_" style="display: none"></iframe>
	<form id="frm" method="post" action="./pharmacy_reg_ps.php" target="frm_" >
	<table class="table table-cols">
		<colgroup>
			<col class="width-xs"/>
			<col/>
			<col class="width-xs"/>
			<col/>
		</colgroup>
		<tbody>
			<tr>
				<th class="require">코드</th>
				<td colspan="3">
					<input name="sno" type="hidden" value="<?=$data['sno']?>">
					<input name="code" type="text" value="<?=$data['code']?>" class="form-control">
				</td>
			</tr>
			<tr>
				<th class="require">메디컬샵명</th>
				<td><input name="name" type="text" value="<?=$data['name']?>" class="form-control" ></td>
				<th>전화번호</th>
				<td><input name="phone" type="text" value="<?=$data['phone']?>" class="form-control" ></td>
			</tr>
			<tr>
				<th>주소</th>
				<td colspan="3">
					<div class="form-inline mgb5">
						<input type="text" name="post" class="form-control" value="<?=$data['post']?>">
                        <input type="button" onclick="postcode_search('post', 'address1');" value="우편번호찾기" class="btn btn-gray btn-sm">
                    </div>
					<div class="form-inline">
						<input type="text" name="address1" class="form-control width-2xl" value="<?=$data['address1']?>">
                        <input type="text" name="address2" class="form-control width-2xl" value="<?=$data['address2']?>">
                    </div>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
</div>
