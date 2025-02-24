<br>Skin Location : /admin/member/_mandate_print.php <br><br><br><br>
<table class="table table-cols">
	<colgroup>
		<col width="15%">
		<col width="35%">
		<col width="15%">
		<col width="35%">
	</colgroup>
	<tr>
		<th>이름</th><td><?=$data['memNm']?></td>
		<th>전화번호</th><td><?=$data['cellPhone']?></td>
	</tr>
	<tr>
		<th>이메일</th><td><?=$data['email']?></td>
		<th>사용자와의관계</th><td><?=$data['relation']?></td>
	</tr>
	<tr>
		<th>차상위 해당</th>
		<td colspan="3">
			<?=$data['charsang']?> OR 
			<input type="checkbox" <?=($data['charsang'] == 'y') ? 'checked' : '' ?> />
		</td>
	</tr>
</table>