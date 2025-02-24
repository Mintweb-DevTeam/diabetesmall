<form method='post' action='../goods/indb_start.php' autocomplete='off' target='ifrmProcess'>
<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
    <input type='submit' value='저장' class='btn btn-red'>
</div>
<input type='hidden' name='mode' value='update_config'>


<div class='table-title'>기본설정</div>
<table class='table table-cols'>
	<colgroup>
		<col width="15%"/>
		<col/>
	</colgroup>
	<tbody>
    <tr>
        <th>SMS인증비번</th>
        <td>
            <input type='text' name='smsPass' value='<?=$smsPass?>' class='form-control'>
        </td>
    </tr>
    <tr>
        <th>알림SMS문구</th>
        <td>
            <textarea name='smsTemplate' class='form-control' rows='5'><?=$smsTemplate?></textarea>
        </td>
    </tr>
    <tr>
        <th>스타트 동영상 링크</th>
        <td>
			<div>이미지 호스팅 주소 fslibre10.hgodo.com</div>
			<div>http 또는 https 포함한 링크를 입력해주세요.</div>
            <input type="text" name="movie_link" class="form-control" value="<?=$movie_link?>"/>
        </td>
    </tr>	
	
	</tbody>
	
	<?php if(\Request::getRemoteAddress()=="182.216.219.157"){?>		
	<tbody>
		
		<tr>
			<th>온라인기업강좌 알림SMS문구</th>
			<td>
				<textarea name='other_smsTemplate' class='form-control' rows='5'><?=$other_smsTemplate?></textarea>
			</td>
		</tr>
		<tr>
			<th>온라인기업강좌 동영상 링크</th>
			<td>
				<div>이미지 호스팅 주소 fslibre10.hgodo.com</div>
				<div>http 또는 https 포함한 링크를 입력해주세요.</div>
				<input type="text" name="other_movie_link" class="form-control" value="<?=$other_movie_link?>"/>
			</td>
		</tr>	
	</tbody>
	<?php }?>
</table>
<div class='center'><input type='submit' value='설정 저장하기' class='btn btn-lg btn-black'></div>
</form>