<form method='post' action='../goods/indb_proxy.php' autocomplete='off' target='ifrmProcess'>
<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
    <input type='submit' value='저장' class='btn btn-red'>
</div>
<div class='table-title'>대리청구서비스 설정</div>
<table class="table table-cols">
	<tr>
		<th  width='140'>연락처</th>
		<td><input type="text" name="cellPhone" value="<?=$cellPhone?>"  class='form-control' placeholder=",제외"/></td>
	</tr>
	<!--tr>
		<th>sms발송비밀번호</th>
		<td><input type="text" name="smsPassword" value="<?//=$smsPassword?>"  class='form-control'/></td>
	</tr-->
	<tr>
		<th>파일비밀번호</th>
		<td><input type="text" name="filePw" value="<?=$filePw?>"  class='form-control'/></td>
	</tr>
</table>
<div>
	<input type='submit' value='저장' class='btn btn-red'>
</div>
</form>