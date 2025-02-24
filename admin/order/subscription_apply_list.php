<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
</div>

<div class='table-title'>신청 검색</div>
<form name="sform" method='get' action='' autocomplete='off'> 
<table class='table table-cols'>
<tr>
    <th width='100'>신청일</th>
    <td class='form-inline'>
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
    </td>
</tr>
<tr>
    <th width='100'>검색조건</th>
    <td class='form-inline'>
        <select name='sopt' class='form-control'>
            <option value='all'<?php if ($search['sopt'] == 'all') echo " selected";?>>- 통합검색 -</option>
            <option value='a.uid'<?php if ($search['sopt'] == 'a.uid') echo " selected";?>>신청번호</option>
            <option value='name'<?php if ($search['sopt'] == 'name') echo " selected";?>>이름</option>
            <option value='m.memId'<?php if ($search['sopt'] == 'm.memId') echo " selected";?>>아이디</option>
            <option value='mobile'<?php if ($search['sopt'] == 'mobile') echo " selected";?>>연락처</option>
        </select>
        <input type='text' name='skey' class='form-control' value='<?=$search['skey']?>' size='30'> / 
        <input type='checkbox' name='autoExtend' value='1'<?php if ($search['autoExtend']) echo " checked";?>>자동연장 신청건 검색
    </td>
</tr>
</table>
<div class='center'><input type='button' value='검색하기' class='btn btn-lg btn-black btn-search'></div>
</form>
<p>&nbsp;</p>
<div class='table-title'>신청 리스트</div>
<form method='post' action='../order/indb_subscription.php' target='ifrmProcess' autocomplete='off'>
<table class='table table-rows'>
    <tr>
		<th width='30'></th>
        <th width='20' class='center'><input type='checkbox' class='js-checkall' data-target-name='uid'></th>
        <th width='100' class='center'>신청번호</th>
        <th width='80' class='center'>신청일</th>
        <th width='130' class='center'>주문자/신청회원</th>
        <th width='130' class='center'>받는분</th>
        <th class='center'>주문상품</th>
        <th width='150' class='center'>정기배송기간</th>
        <th width='100' class='center'>배송주기</th>
        <th width='80' class='center'>배송횟수</th>
		 <th width='80' class='center'>배송회차</th>
		 <th width='80' class='center'>연장횟수</th>
        <th width='100' class='center'>자동연장</th>
        <th width='100' class='center'>결제카드</th>
        <th width='250' class='center'>관리</th>
    </tr>
<?php if (gd_isset($list)) : ?>
<?php foreach ($list as $li) : 
            $period = explode("_", $li['period']);
?>
    <tr>
		<td align="center"><?=$page->idx--?></td>
        <td width='20' align='center'><input type='checkbox' name='uid[]' value='<?=$li['uid']?>'></td>
        <td align='center'><?=$li['uid']?></td>
        <td align='center'><?=date("Y.m.d", $li['regStamp'])?></td>
        <td align='center'>
            <div><?=$li['orderName']?></div>
            <?php if ($li['memId']) : ?>
            <div style='cursor: pointer;' onclick="window.open('../share/member_crm.php?popupMode=yes&memNo=<?=$li['memNo']?>', '', 'width=1100, height=650, scrollbars=yes');"><?=$li['memNm']?>(<?=$li['memId']?>)</div>
            <?php endif; ?>
        </td>
        <td align='center'><?=$li['receiverName']?></td>
        <td align='center'>
            <?=$li['items'][0]['goodsNm']?>
            <?php if (count($li['items']) > 1) echo "외".(count($li['items'])  - 1) . "건"; ?>
            <span class='btn btn-white' onclick="window.open('../order/subscription_view_goods.php?uid=<?=$li['uid']?>', '', 'width=700, height=500, scrollbar=yes');">주문상품보기</span>
        </td>
        <td align='center'>
            <?php if ($li['scheduleList']) : ?>
            <?=date("Y.m.d", $li['scheduleList'][0]['schedule_stamp'])?>~<?=date("Y.m.d", $li['scheduleList'][count($li['scheduleList']) - 1]['schedule_stamp'])?>
            <?php endif; ?>
        </td>
        <td align='center'>
            <?=$period[0]?><?=($period[1] == 'week')?"주":"달"?> 마다<br>
            <span class='btn btn-white btn-sm' onclick="window.open('../order/subscription_change_period.php?uid=<?=$li['uid']?>', '', 'width=700, height=350, scrollbar=yes');">주기변경</span>
        </td>
        <td align='center'><?=$li['deliveryEa']?>(총<?=count($li['scheduleList'])?>회)</td>

		<td align='center'>
			<?=$li['schedule_number']?>
		</td>
		<td align='center'>
			<?=$li['extend_count']?>
		</td>
        <td align='center'>
            <input type='checkbox' name='autoExtend[<?=$li['uid']?>]' value='1'<?=$li['autoExtend']?" checked":""?>> 자동연장
        </td>


        <td align='center'>
            <?=$li['cardNm']?><br>
            <span class='btn btn-sm btn-white' onclick="window.open('../order/subscription_card_change.php?uid=<?=$li['uid']?>', '', 'width=500, height=250; scrollbars=yes');">카드변경</span>
        </td>
        <td align='center' nowrap>
            <span class='btn btn-black' onclick="window.open('../order/subscription_delivery_info.php?uid=<?=$li['uid']?>', '', 'width=800, height=600, scrollbar=yes');">배송정보</span>
            <span class='btn btn-black' onclick="window.open('../order/subscription_schedule_list.php?uid=<?=$li['uid']?>', '', 'width=1000, height=600, scrollbar=yes');">결제일정</span>
            <span class='btn btn-black' onclick="window.open('../order/subscription_order_list.php?uid=<?=$li['uid']?>', '', 'width=1000, height=600, scrollbar=yes');">주문목록</span>
        </td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
<div class='table-action form-inline' style='padding-left: 10px;'>
    <select name='mode' class='form-control'>
        <option value='update_schedule'>수정</option>
        <option value='delete_schedule'>삭제</option>
    </select>
    <input type='submit' value='처리하기' class='btn btn-black' onclick="return confirm('정말 처리하시겠습니까?');">
	</form>


</div> <!-- table-action -->
	<!--민트웹 2022.11.07 추가시작-->
	<div>
		<button class="btn btn-white btn-icon-excel excel_down">엑셀다운로드</button>
	</div>
	<!--민트웹 2022.11.07 추가종료-->
<div class='center'><?=$pagination?></div>
<!--민트웹 2022.11.07 추가시작-->
<script>
	$(function(){
	
		$(".btn-search").click(function(){
			document.sform.target="";
			document.sform.action="";
			document.sform.method="get";
			document.sform.submit();
		
		});

		$(".excel_down").click(function(){
		
			
			document.sform.action="subscription_apply_excel_down.php";
			document.sform.submit();
		
		});
	
	});
</script>
<!--민트웹 2022.11.07 추가종료-->