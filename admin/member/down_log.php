<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>
<div>
	<h3><?=$title?>내역</h3>
	<table class="table table-rows">
		<thead>
			<tr>
				<th>위임가입자명</th>
				<th>위임일</th>
				<th><?=$title?>계정</th>
				<th><?=$title?>일</th>

			</tr>
		</thead>
		<tbody>
			<?php 
			
			if(!empty($amount)){
				foreach($data as $key =>$t){?>
				<tr>
					<td><?=$t['pname']?></td>
					<td><?=$t['bRegDt']?></td>
					<td><?=$t['memId']?></td>
					<td><?=$t['regDt']?></td>
				</tr>
			<?php }
			}else if(empty($amount)){?>
				<tr><td colspan="4" align="center"><?=$title?>내역 없음</td></tr>
			<?php }?>
		</tbody>
	</table>
	<div class="text-center">
		<?= $page->getPage(); ?>
	</div>
	<div style="text-align:center;">
		<span class="btn btn-white" onclick="self.close();">닫기</span>
	</div>
</div>