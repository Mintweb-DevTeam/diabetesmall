<form method='post' action='../goods/indb_subscription.php' autocomplete='off' target='ifrmProcess'>
<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
    <input type='submit' value='저장' class='btn btn-red'>
</div>
<input type='hidden' name='mode' value='update_config'>
<input type='hidden' name='pg' value='inicis'>
<div class='table-title'>PG 설정</div>
<table class='table table-cols'>
    <tr>
        <th width='140'>사용형태</th>
        <td>
            <input type='radio' name='useMode' value='real'<?=($useMode == "real")?" checked":""?>>실사용&nbsp;
            <input type='radio' name='useMode' value='test'<?=($useMode != "real")?" checked":""?>>테스트 
        </td>
    </tr>
    <tr>
        <th>상점ID(MID)</th>
        <td>
            <input type='text' name='mid' value='<?=$mid?>' class='form-control'>
        </td>
    </tr>
    <tr>
        <th>사인키(Signkey)</th>
        <td>
            <input type='text' name='signKey' value='<?=$signKey?>' class='form-control'>
        </td>
    </tr>
    <tr>
        <th>이니라이트키</th>
        <td>
            <input type='text' name='lightKey' value='<?=$lightKey?>' class='form-control'>
        </td>
    </tr>
</table>


<div class='table-title'>정기결제 기본설정</div>
<table class='table table-cols'>
    <tr>
        <th width='140'>정기결제과금<br>사전알림</th>
        <td class='form-inline'>
            정기결제일 <input type='text' name='smsDays' value='<?=$smsDays?>' size='3' class='form-control right'>일전 결제 알림 SMS 전송
        </td>
    </tr>
    <tr>
        <th>정기배송일</th>
        <td class='form-inline'>
            정기결제 신청일 기준 정기결제일
			<select name="am_type">
				<option value="">시간선택</option>
				<?php for($i=0;$i<=23;$i++){
					
				?>
					<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT);?>"  <?php if(str_pad($i, 2, "0", STR_PAD_LEFT) == $am_type)echo"selected";?>><?=$i?>시 이전</option>
				<?php } ?>
			</select>
			<input type='text' name='am_deliveryDays' value='<?=$am_deliveryDays?>' size='3' class='form-control right'>일 이후 배송일로 표기(토, 일, 공휴일 제외)
			</br>
           정기결제 신청일 기준 정기결제일
			<select name="af_type">
				<option value="">시간선택</option>
				<?php for($i=0;$i<=23;$i++){?>
					<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT);?>" <?php if(str_pad($i, 2, "0", STR_PAD_LEFT) == $af_type)echo"selected";?>><?=$i?>시 이후</option>
				<?php } ?>
			</select>
			<input type='text' name='af_deliveryDays' value='<?=$af_deliveryDays?>' size='3' class='form-control right'>일 이후 배송일로 표기(토, 일, 공휴일 제외)
        </td>
    </tr>
    <!--tr>
        <th>정기배송취소<br>최소 구매횟수</th>
        <td class='form-inline'>
            최소 <input type='text' name='cancelEa' value='<?//=$cancelEa?>' size='3' class='form-control right'>회 이상 배송 후 고객 취소 가능
        </td>
    </tr-->
    <tr>
       <th>선택가능 배송주기</th>
       <td>
            <div style='margin-bottom: 5px;'>
            <?php for ($i = 1; $i <= 10; $i++) : ?>
                <input type='checkbox' name='period[]' value='<?=$i?>_week'<?php if (@in_array($i."_week", $period)) echo " checked";?>><?=$i?>주&nbsp;&nbsp;
            <?php endfor; ?>
            </div>
            <div>
            <?php for ($i = 1; $i <= 11; $i++) : ?>
                <input type='checkbox' name='period[]' value='<?=$i?>_month'<?php if (@in_array($i."_month", $period)) echo " checked";?>><?=$i?>달&nbsp;&nbsp;
            <?php endfor; ?>
            </div>
       </td>
    </tr>
    <tr>
        <th>선택가능 배송횟수</th>
        <td><input type='text' name='deliveryEa' value='<?=implode(",", $deliveryEa)?>' class='form-control' placeholder='여러개 입력시 콤마(,)로 구분하여 숫자 입력'></td>
    </tr>
    <tr>
        <th>배송회차별 할인율</th>
        <td>
            <input type='text' name='discount' value='<?=implode(",", $discount)?>' class='form-control' placeholder='여러개 입력시 콤마(,)로 구분하여 숫자 입력'>
        </td>
    </tr>
    <tr>
        <th>SMS인증비번</th>
        <td>
            <input type='text' name='smsPass' value='<?=$smsPass?>' class='form-control'>
        </td>
    </tr>
    <tr>
        <th>결제알림SMS문구</th>
        <td>
            <textarea name='smsTemplate' class='form-control' rows='5'><?=$smsTemplate?></textarea>
        </td>
    </tr>
</table>

<div class='table-title'>정기배송 약관설정</div>
<table class='table table-cols'>
    <tr>
        <th width='140'>정기배송약관</th>
        <td>
            <textarea name='terms' class='form-control' rows='15'><?=$terms?></textarea>
        </td>
    </tr>
    <tr>
        <th>결제카드등록약관</th>
        <td><textarea name='cardTerms' class='form-control' rows='15'><?=$cardTerms?></textarea></td>
    </tr>
</table>

<div class='table-title'>정기배송 이용안내(주문서)</div>
<table class='table table-cols'>
    <tr>
        <th width='140'>이용안내</th>
        <td><textarea name='orderGuide' class='form-control' rows='15'><?=$orderGuide?></textarea></td>
    </tr>
</table>
<div class='center'><input type='submit' value='설정 저장하기' class='btn btn-lg btn-black'></div>
</form>