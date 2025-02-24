{*** 회원가입 > 애보트 완료 | main/html.php?htmid=member/complete.html ***}
{ # header }
<div class="content_box">
	<div class="join_agreement_wrap">

		<div class="adela_member_cont">

			<div class="container">
				<h3 class="mem_title mb50 pb40" style="border-bottom:3px solid #ffc600">
					리브레케어 멤버십
					<p class="pull-right" style="margin-top:-40px;"><img src="../img/common/freestyle_libre_logo.jpg" alt="" /></p>
				</h3>
				<h4 class="fcOrange fs30 fw600 mb30">리브레케어 멤버십 등록이 완료되었습니다!</h4>
				<!-- <div class="mb50">
					<img src="/data/skin/mobile/moment/img/qrcode/test-img_01.jpg" alt="test-img_01" class="test-img_01">
				</div> -->
				<div class="form-wrap">
					<!-- YOU ARE {now}A LIBRECARE MEMBER !
					<h4 class="fs26 fcGray7 lineH12 mb30 fw600">You have agreed to the Terms &#38; Conditions.</h4> -->
					<ul class="complete-ul fs26 mb40 fcGray6 lineH16">
						<li>이름 : {post.memNm}</li>
						<li>이메일 : {post.memId}</li>
						<li>휴대폰 번호 : {post.cellPhone}</li>
					</ul>
					<a href="{url}" class="continue btn btn-orange btn-lg">쇼핑몰  회원가입 바로가기</a>
				</div>
			</div>
			<div class="container mail_form_new">
				<h4 class="fcBlue fs30 fw600">리브레케어 멤버십에 등록하시면</h4>
				<ul class="list-unstyled blue-list fs18 mb40 fcGray6 lineH16">
					<li><span class="">1</span>공식 지정 약국, 의료기기 판매점 및 온라인 샵에서 프리스타일 리브레 센서를 구매할 수 있습니다.</li>
					<li><span class="">2</span>품질 보증 서비스를 비롯한 다양한 고객 서비스를 받을 수 있습니다.</li>
					<li><span class="">3</span>다양한 혈당관리 관련 정보를 받아볼 수 있습니다.</li>
				</ul>
				<img src="../../adela_01/img/member/mail_form_logo.jpg" class="form_logo" alt="" />
			</div>


		</div>
		<!-- //member_cont -->
	</div>
	<!-- //join_agreement_wrap -->
</div>
<!-- //content_box -->
{ # footer }