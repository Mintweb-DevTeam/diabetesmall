{*** 회원로그인 | member/login.php ***}
{ # header }
<div class="login">
	<div class="login_box">
		<form id="formLogin" name="login_form" action="<!--{= loginActionUrl}-->" method="post">
			<fieldset>
				<div class="text-center mb20">
					<img src="/data/skin/mobile/moment/img/banner/4521f002a652c07e47e20df1f67ec8af_26207.jpg" alt="로그인 로고" title="로그인 로고" style="width:100px;">			
				</div>	

				<div class="member_login_box">
	
				<legend>{=__('로그인 영역')}</legend>
				<input type="hidden" name="returnUrl" value="{returnUrl}"/>
				<input type="hidden" name="close" value=""/>
				<input type="hidden" name="saveFlag" value="{data.saveFlag}"/>
				<input type="hidden" id="mode" name="mode" value="{modeLogin}"/>

					<dl>
						<h4 class="fcGray6 fw600 fs14 mb10 mt20">이메일</h4>
						<dt><label for="loginId" class="fcGray6 fw600 fs14 mb10">{=__('이메일')}</label></dt>
						<dd><input type="text" id="loginId" name="loginId" value="{data.loginId}" placeholder="{=__('')}" class="form-control"></dd>
					</dl>
					<dl>
						<h4 class="fcGray6 fw600 fs14 mb10 mt20">비밀번호</h4>
						<dt><label for="loginPwd">{=__('비밀번호')}</label></dt>
						<dd><input type="password" placeholder="{=__('')}" id="loginPwd" name="loginPwd" value="{data.loginPwd}" class="form-control"></dd>
					</dl>

					<div class="id_chk">
						<span class="form_element">
							<input type="checkbox" id="saveId" name="saveId" value="y" checked="{?!empty(data.loginId)}true{/}"/>
			                <label for="saveId" class="{?!empty(data.loginId)}on{/}">{=__('아이디 저장')}</label>
						</span>
						<p class="dn js_caution_msg1">{=__('아이디, 비밀번호가 일치하지 않습니다. 다시 입력해 주세요.')}</p>
					</div>
					
					<div><button type="submit" class="btn btn-blue wp100 fw600">{=__('로그인')}</button></div>
					<p class="text-center  fs13 pt15 mb50"><a href="#." class="fcGray6" id="btnFindPwd">비밀번호를 잊으셨나요? </a></p>
					<!-- <p><button id="btnFindId" class="btn_member_white">{=__('아이디 찾기')}</button></p> -->
					<div class="text-center">
						<p class="fs13 text-center fcGray4 dp-inblock clearfix">
							<span class="pull-left mr20 mt05">	아직 아델라 회원이 아니신가요? </span>
							<a href="#." id="btnJoinMember" class="btn btn-gray2 btn-sm w100">{=__('회원가입')}</a>
						</p>
					</div>


					
				</div>
				<!-- //login_box -->


				<div class="inp_chk">
					<input type="checkbox" name="saveAutoLogin" value="y" id="saveAutoLogin" <!--{? !empty(data.loginId) && !empty(data.loginPwd)}checked="checked"{/}--> />
					<label for="saveAutoLogin">{=__('로그인 상태 유지')}</label>
					<input type="checkbox" id="saveId" name="saveId" value="y" <!--{? !empty(data.loginId) }checked="checked"{/}-->/>
					<label for="saveId">{=__('아이디 저장')}</label>
				</div>
				<div class="submit">
					<button type="submit" class="member_login_order_btn">{=__('로그인')}</button>
				</div>
				<!-- //submit -->
			</fieldset>
		</form>
		<!--{ ? usePaycoLogin || useFacebookLogin || useNaverLogin || useKakaoLogin || useWonderLogin }-->
		<ul class="sns_login">
			<!--{ ? usePaycoLogin }-->
			<li class="payco js_btn_payco_login"><img src="../img/etc/txt_mo_payco.png" alt="{=__('PAYCO')} {=__('아이디 로그인')}"></li>
			<!--{ / }-->
			<!--{ ? useFacebookLogin }-->
			<li class="facebook js_btn_facebook_login" data-facebook-url="{=facebookUrl}"><img src="../img/etc/txt_mo_facebook.png" alt="{=__('FACEBOOK')} {=__('아이디 로그인')}"></li>
			<!--{ / }-->
			<!--{ ? useNaverLogin }-->
			<li class="naver js_btn_naver_login"><img src="../img/etc/txt_mo_naver.png" alt="{=__('네이버')} {=__('아이디 로그인')}" /></li>
			<!--{ / }-->
			<!--{ ? useKakaoLogin }-->
			<li class="kakao js_btn_kakao_login" data-return-url="{=kakaoReturnUrl}"><img src="../img/etc/txt_mo_kakao.png" alt="{=__('카카오')}  {=__('아이디 로그인')}"></li>
			<!--{ / }-->
			<!--{ ? useWonderLogin }-->
			<li class="wonder js_btn_wonder_login" data-wonder-type="login" data-wonder-url="{=wonderReturnUrl}"><img src="../img/etc/txt_mo_wonder.png" alt="{=__('위메프')}  {=__('아이디로 로그인')}"></li>
			<!--{ / }-->
		</ul>
		<!-- //sns_login -->
		<!--{ / }-->
		<ul class="login_find">
			<li><a href="../member/join_method.php">{=__('회원가입')}</a></li>
			<li><a href="../member/find_id.php">{=__('아이디찾기')}</a></li>
			<li><a href="../member/find_password.php">{=__('비밀번호찾기')}</a></li>
		</ul>
		<!-- //login_find -->
	</div>
	<!-- //login_box -->
	<div class="guest_order_box">
		<!--{ ? isMemberOrder == false }-->
		<p class="guest_txt">
			<strong>{=__('비회원 주문')}</strong>
		</p>
		<!--  //guest_txt -->
		<form action="../member/member_ps.php" method="post">
			<fieldset>
				<legend>{=__('비회원 주문하기')}</legend>
				<input type="hidden" name="mode" value="guest">
				<input type="hidden" name="returnUrl" value="{returnUrl}">
				<button type="submit" class="member_login_order_btn"> {=__('비회원 주문하기')}</button>
			</fieldset>
		</form>
		<!--{ / }-->
		<!--{ ? isMemberOrder == false }-->
		<p class="guest_txt">
			<strong>{=__('비회원 주문조회')}</strong>
			<span>{=__('주문자 이름과 주문 번호를 입력해주세요')}</span>
		</p>
		<!-- //guest_txt -->
		<form id="formOrderLogin" name="login_form" action="../member/member_ps.php" method="post">
			<fieldset>
				<legend>{=__('주문조회 영역')}</legend>
				<input type="hidden" name="mode" value="guestOrder">
				<input type="hidden" name="returnUrl" value="../mypage/order_view.php">
				<dl>
					<dt><label for="orderNm">{=__('주문자 이름')}</label></dt>
					<dd><input type="text" id="orderNm" name="orderNm" value="" maxlength="12" title="{=__('주문자명')}" required="required" msgr="{=__('주문자명을 입력하세요.')}" placeholder="{=__('주문자명')}"></dd>
				</dl>
				<dl>
					<dt><label for="orderNo">{=__('주문번호')}</label></dt>
					<dd><input type="text" id="orderNo" name="orderNo" maxlength="34" title="{=__('주문번호')}" required="required" msgr="{=__('주문번호를 입력하세요.')}" placeholder="{=__('주문번호')}" data-max-length="{=orderNoMaxLength}"></dd>
				</dl>
				<button type="submit" class="member_login_order_btn">{=__('주문 조회')}</button>
			</fieldset>
		</form>
		<!--{ / }-->
	</div>
	<!-- //guest_order_box -->
</div>
<script type="text/javascript" src="../js/jquery/jquery.serialize.object.js"></script>
<script type="text/javascript">
    var $formLogin;
    $(document).ready(function () {
        var order_no_max_length = $('input[name=orderNo]').data('max-length');
        $formLogin = $('#formLogin');
        $formLogin.validate({
            dialog: false,
            rules: {
                loginId: {
                    required: true
                },
                loginPwd: {
                    required: true
                }
            },
            messages: {
                loginId: {
                    required: "{=__('아이디를 입력해주세요')}"
                },
                loginPwd: {
                    required: "{=__('패스워드를 입력해주세요')}"
                }
            }, submitHandler: function (form) {
                if (window.location.search) {
                    var _tempUrl = window.location.search.substring(1);
                    var _tempVal = _tempUrl.split('=');

                    if (_tempVal[1] == 'lnCouponDown') {
                        $('input[name=returnUrl]').val(document.referrer);
                    }
                }
                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        // 비회원 주문조회 폼 체크
        $('#formOrderLogin').validate({
            rules: {
                orderNm: 'required',
                orderNo: {
                    required: true,
                    number: true,
                    maxlength: order_no_max_length
                }
            },
            messages: {
                orderNm: {
                    required: "{=__('주문자명을 입력해주세요.')}",
                },
                orderNo: {
                    required: "{=__('주문번호를 입력해주세요.')}",
                    number: "{=__('숫자로만 입력해주세요.')}",
                    maxlength: "{=__('주문번호는 " + order_no_max_length + "자리입니다.')}"
                }
            },
            submitHandler: function (form) {
                $.post(form.action, $(form).serializeObject()).done(function (data, textStatus, jqXhr) {
                    console.log(data);
                    if (data.result == 0) {
                        location.replace('../mypage/order_view.php?guest=y&orderNo=' + data.orderNo);
                    } else {
                        alert(data.message);
                        //$('.js-caution').empty().removeClass('caution-msg2').addClass('caution-msg1').html('주문자명과 주문번호가 일치하는 주문이 존재하지 않습니다. 다시 입력해 주세요.<br><span>주문번호와 비밀번호를 잊으신 경우, 고객센터로 문의하여 주시기 바랍니다.</span>');
                    }
                });
                return false;
            }
        });
    });
</script>
{ # footer }
