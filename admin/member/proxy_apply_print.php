<script src="/admin/script/printThis.js"></script>
<style>
		#contents{font-size:14px;}
		.title{background: #00000014;height: 120px;line-height: 120px;text-align: center;margin: 50px 0px;}
		h4 { font-size:40px;line-height: 120px;font-weight:bold;}
		.content_body{width:80%;margin:auto;}
		.Table{border-spacing:0px;width:100%;}
		.Table th,td{padding:5px 5px;;}
		.Table th{text-align:center;}
		.Table td{height:40px;}
		.th{text-align:center;font-weight:bold;}

		.borderTop{border-top:1px solid #000;}
		.borderBottom{border-bottom:1px solid #000;}
		.borderBottomStrong{border-bottom:2px solid #000;}
		.borderLeft{border-left:1px solid #000;}
		.borderRight{border-right:1px solid #000;}
		.period{word-spacing:5px;text-align:center;}

		.tel1{text-align:right;}
		.tel2{word-spacing:5px;padding:3px;0;x}

		.info1{}
		.info1 span:nth-child(2){float:right;margin-right:10px;}
		.item div span{display:inline-block;width:45%;}
		.guide{margin: 20px auto;}
		.guide div:nth-child(1){padding-left: 50px;}
		.guide div:nth-child(1) span:nth-child(1){display: block;word-spacing: 10px;}
		.guide div:nth-child(1) span:nth-child(2){word-spacing: 10px;}
		.guide div:nth-child(2){text-align: right;margin-top: 15px;}
		.guide div:nth-child(2) span:nth-child(1){word-spacing: 5px;}
		.guide div:nth-child(3){text-align: right;margin-top: 15px;}
		.guide div:nth-child(3) span:nth-child(1){word-spacing: 5px;vertical-align: middle;}
		.guide div:nth-child(3) span:nth-child(2){word-spacing: 5px;vertical-align: middle;}
		.guide div:nth-child(4){padding-bottom: 20px;margin-top:15px;color:red;}
		.guide div:nth-child(4) span:nth-child(1){}
		.guide div:nth-child(4) span:nth-child(2){word-spacing: 10px;}
		.guide div:nth-child(4) span:nth-child(3){display:block;word-spacing: 10px;padding-left: 15px;}

		.btn {text-align:center;width:100%;margin-bottom:20px;}
		.btn .btn_print{background-color: #4584c5;color: #fff;width: 150px;display: inline-block;margin: 10px 0px;border: 0px;height: 35px;line-height:35px;}
		.btn .btn_pdf{background-color: red;color: #fff;width: 150px;display: inline-block;margin: 10px 0px;border: 0px;height: 35px;line-height:35px;}


		.sign1{display: inline-block;position:relative;}
		.sign_img img{    width: 180%;position: absolute;top: -10px;right:-50%;}
		.pname_sign{display:inline-block;margin: 0px 20px;}
		.pname_sign em{display: inline-block;  border-bottom: 1px solid #000;    text-align: center;       padding: 0px 10px;}
</style>
<div id="container">
	<div id="contents">
		<div class="print_area" >
		<div class='info'><span>■국민건강보험법 시행규칙(별지 제19호의6서식)</span></div>
		<div class="title">
			<h4>요양비 지급청구 위임장</h4>
		</div>
		<div class="content_body">

			<div class='info1'><span>※뒤쪽의 유의사항을 읽고 작성해 주시기 바라며,【 】에는 해당되는 곳에 √표시를 합니다.</span><span >(앞쪽)</span></div>
			
			<table class="Table">
				<colgroup>
					<col width="20%"/>
					<col width="20%"/>
					<col width="20%"/>
					<col/>
				</colgroup>
				<tbody>
					<tr>
						<th rowspan="6" class="borderTop borderRight">① 위임인</th>
						<td rowspan="2" class="borderTop borderRight th">가입자 또는<br>피부양자</td>
						<td class="borderTop borderRight">성명</td>
						<td class="borderTop"><?=$pname?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">주민(외국인)등록번호</td>
						<td  class="borderTop"><?=$pjumin?></td>
					</tr>
					<tr>
						<td rowspan="3"  class="borderTop borderRight th">법정대리인</br>또는 가족</td>
						<td class="borderTop borderRight">성명</td>
						<td class="borderTop"><?=$lname?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">생년월일</td>
						<td class="borderTop"><?php if(!empty($birth)){?><?=$birth?><?php }?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">가입자·피부양자와의 관계</td>
						<td class="borderTop"><?=$conn?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">전화번호</br>(위임사항 및<br>지급내역 수신용)</td>
						<td colspan="3" class="borderTop">
							<div class='tel1'> <?=substr($cellPhone,0,3)?>-<?=substr($cellPhone,3,4)?>-<?=substr($cellPhone,7,4)?>【√】문자메시지 수신동의</div>
							<div class='tel2'>위임처리결과 및 지급내역 등을 전송받을 연락처로 정확히 적어 주시기 바랍니다.</div>
							<div class='tel3'>(문자메시지 수신을 동의한 경우에만 발송)</div>
						</td>
					</tr>
					<tr>
						<th rowspan="4" class="borderTop borderRight">② 준요양기관</th>
						<td class="borderTop borderRight th">상호</td>
						<td colspan="2" class="borderTop"><?=$companyNm?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">사업자등록번호</br>(법인등록번호)</td>
						<td colspan="2" class="borderTop"><?=$businessNo?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">대표자</td>
						<td colspan="2" class="borderTop">윤길호</td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">전화번호</td>
						<td colspan="2" class="borderTop"><?=$phone?></td>
					</tr>

					<tr>
					
						<th rowspan="4" class="borderTop borderRight">③ 요양비 수령계좌</th>
						<td class="borderTop borderRight th">수령자</td>
						<td colspan="2" class="borderTop">아델라헬스케어</td>
					</tr>
					<tr>
						<td rowspan="3" class="borderTop borderRight th">수령계좌</td>
						<td class="borderTop borderRight">금융기관</td>
						<td class="borderTop">우리은행</td>
					</tr>
					<tr>
						<td class="borderTop borderRight">예금주</td>
						<td class="borderTop"><?=$companyNm?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">계좌번호</td>
						<td class="borderTop">1005-003-972156</td>
					</tr>

					<tr>
						<th class="borderTop borderRight">④ 위임사항</th>
						<td colspan="3" class="borderTop item">
							<div><span>1)【&nbsp;&nbsp;&nbsp;&nbsp;】자동복막투석 소모성 재료</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】복막관류액</span></div>
							<div><span>2)【&nbsp;&nbsp;&nbsp;&nbsp;】가정용 산소발생기</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】휴대용 산소발생기</span></div>
							<div><span>3)【√】당뇨병 소모성 재로</span><span>【√】연속혈당특정용 전극(센서)</span></div>
							<div><span>4)【&nbsp;&nbsp;&nbsp;&nbsp;】자가도뇨 소모성 재로</span></div>
							<div><span>5)【&nbsp;&nbsp;&nbsp;&nbsp;】인공호흡기 및 기본소모품</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】선택소모품</span></div>
							<div><span>6)【&nbsp;&nbsp;&nbsp;&nbsp;】기침유발기</span></div>
							<div><span>7)【&nbsp;&nbsp;&nbsp;&nbsp;】양압기 및 소모품</span></div>
							<div><span>8)【&nbsp;&nbsp;&nbsp;&nbsp;】연속할당측정기</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】인슐린자동주입기</span></div>
							<div><span>9)【&nbsp;&nbsp;&nbsp;&nbsp;】출산비</span></div>
							<div></br><span>※【√】표시,중복 표시가능</span></div>
						</td>
					</tr>
					<tr>
						<th class="borderTop borderRight">⑤ 위임기간</th>
						<td colspan="3" class="borderTop period">
							<span><?=substr($regDt,0,4)?>년 <?=substr($regDt,5,2)?>월 <?=substr($regDt,8,2)?>일부터</span><span> <?=$end_date?></span> 까지(최장5년)
						</td>
					</tr>
					<tr>
						<td colspan="4" class="borderTop">
							<div class="guide">
							<div>
								<span>「국민건강보험법」 제43조4항 및 같은 법 시행규칙 제23조제4항에 따라 요양비 지급 청구에 관한</span>
								<span>사항을 위와 같이 위임합니다.</span>
							</div>
							<div style="text-align:right;">
								<span><?=substr($regDt,0,4)?>년&nbsp;&nbsp;<?=substr($regDt,5,2)?>월&nbsp;&nbsp;<?=substr($regDt,8,2)?>일</span>
							</div>
							<div>
								

								<span>위임인</span> 
								<div class="pname_sign"><em><?=$pname?></em></div>
								<div class="sign1">
									<span>(서명 또는 인)</span>
									<div class="sign_img"><img src="<?=$sign?>"/></div>
								</div>
							</div>

							<!-- <div class="borderBottomStrong">
								
								<span>※</span>
								<span>국민건강보험법 시행규칙 제23조4항에 따라, 위임하는 사람의 신분증 사본과 준요양기관 대표자</span>
								<span>신분증 또는 사업자등록증 사본을 각각 제출하셔야 합니다.</span>
							</div> -->
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		</div>
				<div class="btn">
					<span class="register btn_print">인쇄</span>

					<!--span class="register btn_pdf">PDF다운로드</span-->
				</div>

	</div>
</div>
<form id="pdf_form" name="pdf_form" method="post" action="pdf_down.php">
	<input type="hidden" name="idx" value="<?=$idx?>"/>
	<textarea name="pdf_content" id="pdf_content"  style="width:0px;height:0px"></textarea>
</form>
<script type="text/javascript">

	$(function(){
		$(".btn_print").on("click",function(){
			
			$.ajax({
				method:"POST",
				url:"proxy_indb.php",
				data:{applyidx:"<?=$idx?>",mode:"print_log"}
			
			})
			.done(function(msg){
			
				if(msg.success==1)
					$('.print_area').printThis({importCSS: true,importStyle: true,printContainer:true,removeInline:false});
				else
					alert("인쇄 오류입니다.");
			});
			
		});

		$(".btn_pdf").on("click",function(){
			$("#pdf_content").html($.trim($(".print_area").html()));
			//$("#pdf_form").action="pdf_down.php";
			//$("#pdf_form").target="ifrmProcess";

			//document.pdf_form.submit();

		});
	
	});


</script>

