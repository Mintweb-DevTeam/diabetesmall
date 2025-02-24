<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
    </h3>
</div>
<div class="table-title">
    리브레 멤버쉽 sms
</div>

<form name="frmSearch" id="frmSearch" action="in_db.php" method="post" target="ifrmProcess">
    <div class="search-detail-box">
        <table class="table table-cols">
		<input type="hidden" name="idx" value="<?=$idx?>"/>
			<colgroup>
				<col width="10%"/>
				<col/>
			</colgroup>
			<tr>
				<th>sms 안드로이드 내용</th>
				<td>
					<textarea name="androidContent" class="content"><?=$androidContent?></textarea>
				</td>
			</tr>
			<tr>
				<th>sms 아이폰 내용</th>
				<td>
					<textarea name="iphoneContent" class="content"><?=$iphoneContent?></textarea>
				</td>
			</tr>			
		</table>
		<div>
		<span class="btn btn-white confirm">확인</span>
		</div>
	</div>
</form>
<style>
	.content{width:500px;height:150px;}
</style>
<script type="text/javascript">
	$(function(){
	
		$(".confirm").click(function(){
		
			document.frmSearch.submit();
		});
	})
</script>