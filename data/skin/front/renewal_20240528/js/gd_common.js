/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * 공용 스크립트 및 프로토타입 정의
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */


/**
 * 자바스크립트 Trim함수
 *
 * @deprecated 자바스크립트 기본 함수 혹은 $.trim() 사용
 * @return string 좌우 공백 제거한 문자열
 */
//String.prototype.trim = function () {
//    return this.replace(/^\s+|\s+$/, '');
//};

/**
 * 자바스크립트 number_format 함수
 *
 * @deprecated numeral로 대체
 * @return string 문자열을 세자리 단위로 쉼표 찍기
 */
//String.prototype.number_format = function()
//{
//    return this.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
//};

// 멀티상점 변수 기본처리
if (typeof gdCurrencyDecimalFormat === 'undefined') {
    gdCurrencyDecimal = 0;
    gdCurrencyDecimalFormat = 0;
}

// IE9에서 console 객체가 없어 console 객체가 없는 경우 log로 사용하도록 처리
if (!window.console) console = {
    log: function () {
    }
};

// IE8 이하에서 Array.indexOf 지원하지 않는 경우에 대한 대응
if (typeof Array.prototype.indexOf !== 'function') {
    Array.prototype.indexOf = function (ele) {
        return $.inArray(ele, this);
    };
}

// IE8 이하에서 String.trim 지원하지 않는 경우에 대한 대응
if (typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function(){
        return $.trim(this);
    };
}

// @qnibus bugfix. toFixed 사용시 무조건 반올림 처리하는 부분으로 인해 고도몰5의 정책과 맞지 않아서 toFixed 대신 사용해야 함
if (typeof Number.prototype.toRealFixed !== 'function') {
    Number.prototype.toRealFixed = function (digits, format) {
        if (typeof digits === 'undefined') {
            digits = gdCurrencyDecimal;
        }
        if (typeof format === 'undefined') {
            format = gdCurrencyDecimalFormat;
        }

        return numeral(Math.floor(this.valueOf() * Math.pow(10, digits)) / Math.pow(10, digits)).format('0,' + format);
    };
}

/**
 * 스트링 치환 메소드
 * @returns {String}
 */
String.prototype.format = function () {
    var formatted = this;
    for (var i = 0; i < arguments.length; i++) {
        formatted = formatted.replace("{" + i + "}", arguments[i]);
    }
    return formatted;
};

/**
 * DOM 로드
 */
$(document).ready(function () {

    // IE 9 이하 placeholder 처리
    if (gd_is_ie() < 10) {
        $("input, textarea").placeholder();

        $('form').submit(function(e){
            $(this).find("input, textarea").each(function(){
                var _name = this.name;
                var _placeholder = this.getAttribute('placeholder');

                if (_name && _placeholder) {
                    var _target = document.getElementsByName(_name)[0];
                    if (_target.value == _placeholder) {
                        _target.value = '';
                    }
                }
            });
        });
    }

	// jQuery Validator 기본값 설정
    $.validator.setDefaults({
        onfocusout: false,
        onclick: false,
        onkeyup: false,
        errorPlacement: function (error, element) {
            // do nothing
        },
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                alert(validator.errorList[0].message);
                validator.errorList[0].element.focus();
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    // jQuery Validator 기본 메시지 설정
    jQuery.extend(jQuery.validator.messages, {
        required: __("필수항목 입니다."),
        remote: __("이 필드를 수정해주세요."),
        email: __("이메일을 정확하게 입력해주세요."),
        url: __("유효하지 않은 URL이 입력되었습니다."),
        date: __("날짜형식이 맞지 않습니다."),
        dateISO: __("유효한 ISO 타입의 날짜로 다시 입력해주세요."),
        number: __("숫자만 입력하실 수 있습니다."),
        digits: __("숫자만 입력하실 수 있습니다."),
        creditcard: __("유효한 신용카드 번호로 다시 입력해주세요."),
        equalTo: __("동일한 값을 입력해주세요."),
        accept: "Please enter a value with a valid extension.",
        maxlength: jQuery.validator.format(__("최대 %i 이하 입력해 주세요.", "{0}")),
        minlength: jQuery.validator.format(__("최소 %i 이상 입력해 주세요.", "{0}")),
        rangelength: jQuery.validator.format(__("%1$i자에서 %2$i까지 입력가능합니다.", ["{0}", "{1}"])),
        range: jQuery.validator.format(__("%1$i와 %2$i사이의 숫자를 입력해주세요.", ["{0}", "{1}"])),
        max: jQuery.validator.format(__("최대 %i 이상 입력하실 수 없습니다.", "{0}")),
        min: jQuery.validator.format(__("최소 %i 이하 입력하실 수 없습니다.", "{0}"))
    });

    //검색어
    $("#frmSearchTop").validate({
        submitHandler: function (form) {
            if ($("#frmSearchTop input[name='adUrl']").val() && $("#frmSearchTop input[name='keyword']").val() == '') document.location.href = $("#frmSearchTop input[name='adUrl']").val();
            else form.submit();
        },
        rules: {
            keyword: {
                required: function () {

                    if ($("#frmSearchTop input[name='adUrl']").val()) {
                        return false;o
                    }
                    else {
                        return true;
                    }
                }
            }
        },
        messages: {
            keyword: {
                required: __('검색어를 입력하세요.')
            }
        }
    });

    // 최근 검색어 삭제
    $('.btn_top_search_del').click(function(e){
        e.stopPropagation();
        $self = $(this);
        $.post('../goods/goods_ps.php', {
            'mode': 'delete_recent_keyword',
            'keyword': $(this).data('recent-keyword')
        }, function (data, status) {
            // 값이 없는 경우 성공
            if (status == 'success' && data == '') {
                if ($self.closest('ul').find('li').length == 1) {
                    $self.closest('li').remove();
                    $('.btn_top_search_all_del').remove();
                    $('.js_recent_area').append('<dd>' + __('최근 검색어가 없습니다.') + '</dd>');
                } else {
                    $self.closest('li').remove();
                }
            } else {
                console.log('request fail. ajax status (' + status + ')');
            }
        });
    });

    // 최근 검색어 전체 삭제
    $('.btn_top_search_all_del').click(function(e){
        $.post('../goods/goods_ps.php', {
            'mode': 'delete_recent_all_keyword'
        }, function (data, status) {
            // 값이 없는 경우 성공
            if (status == 'success' && data == '') {
                $('.js_recent_list').find('li').remove()
                $('.btn_top_search_all_del').remove();
                $('.js_recent_area').append('<dd>' + __('최근 검색어가 없습니다.') + '</dd>');
            } else {
                console.log('request fail. ajax status (' + status + ')');
            }
        });
    });

    // Ajax 에러 및 처리 기본값 설정
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            xhr.url = settings.url;
            xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function () {
            gd_trigger_checkbox_ui();
            gd_select_remodeling();
            gd_checkbox_all();

            //이미지 로딩 향상 사용 시 호출.
            if ($.isFunction($.fn.lazyload) === true) {
                setTimeout(function() {
                    $("img.gd_image_lazy").lazyload({threshold:200});
                }, 1);
            }
        },
        error: function (xhr, textStatus, error) {
            if (xhr.status == '404' && error == 'Not Found') {
                console.log('[404 Not Found]\nThe requested URL was not found.');
            }
            else if (xhr.status == '500' && error == 'Internal Server Error' && xhr.responseText != '') {
                console.log(xhr.responseText);
            }
            gd_close_layer();
            console.log('Ajax Error URL: ' + xhr.url, xhr.responseText);
        }
    });

    $(document).on('click', '.btn_alert_login', function (e) {
        var target = $(this).attr('id');
        e.preventDefault();
        alert(__('로그인하셔야 본 서비스를 이용하실 수 있습니다.'));
        document.location.href = target == undefined ?  "../member/login.php" : "../member/login.php?id=" + target;
        return false;
    });

    // 미확인 입금자 팝업
    $('#ghostDepositorBanner').click(function () {
        var url = '/service/popup_ghost_depositor.php';
        var win = gd_popup({
            url: url
            , target: 'ghostDepositorPopup'
            , width: 630
            , height: 560
            , resizable: 'no'
            , scrollbars: 'no'
        });
        win.focus();
        return win;
    });

    if ($('.btn_prev').length > 0) {
        $('.btn_prev').click(function (e) {
            e.preventDefault();
            history.go(-1);
        });
    }

    // 복사 기능
    // https://github.com/zeroclipboard/zeroclipboard/blob/master/docs/api/ZeroClipboard.md
    if ($('.gd_clipboard').length) {
        if (navigator.userAgent.match(/MSIE 8/) !== null) {
            $('.gd_clipboard').each(function () {
                $(this).click(function () {
                    alert(__("주소를 드래그 해서 복사해주세요"));
                    return false;
                });
            });
        } else {
            var clipboard = new Clipboard('.gd_clipboard');
            clipboard.on('success', function (e) {
                var title = $(e.trigger).attr('title') == undefined ? '' : $(e.trigger).attr('title');
                alert('[' + title + '] '+__('정보를 클립보드에 복사했습니다.\nCtrl+V를 이용해서 사용하세요.'));
                e.clearSelection();
            });
            clipboard.on('error', function (e) {
                console.error('Action:', e.action);
                console.error('Trigger:', e.trigger);
            });
        }
    }
});

// @qnibus bugfix. toFixed 사용시 무조건 반올림 처리하는 부분으로 인해 고도몰5의 정책과 맞지 않아서 toFixed 대신 사용해야 함
if (typeof Number.prototype.toRealFixed !== 'function') {
    Number.prototype.toRealFixed = function (digits, format) {
        if (typeof digits === 'undefined') {
            digits = gdCurrencyDecimal;
        }
        if (typeof format === 'undefined') {
            format = gdCurrencyDecimalFormat;
        }

        return numeral(Math.floor(this.valueOf() * Math.pow(10, digits)) / Math.pow(10, digits)).format('0,' + format);
    };
}

/**
 * 기준화폐 환율변환
 *
 * @param price 금액
 * @param isFormat 포맷여부
 * @returns {*}
 */
function gd_money_format(price, isFormat) {
    var convertPrice = fx.convert(price).toRealFixed();
    if (typeof isFormat !== 'undefined') {
        if (isFormat) {
            return numeral().unformat(convertPrice);
        }
    }

    return convertPrice;
}

/**
 * 추가화폐 환율변환
 *
 * @param price 금액
 * @param isFormat 포맷여부
 * @returns {*}
 */
function gd_add_money_format(price, isFormat) {
    var convertPrice = fx.convert(price, {to: gdCurrencyAddCode}).toRealFixed(gdCurrencyAddDecimal, gdCurrencyAddDecimalFormat);
    if (typeof isFormat !== 'undefined') {
        if (isFormat) {
            return numeral().unformat(convertPrice);
        }
    }

    return convertPrice;
}

/**
 * 윈도우팝업 호출
 * @param array options 창정보
 * @return object Window 개체
 */
function gd_popup(options) {
    if (!options.width) options.width = 500;
    if (!options.height) options.height = 415;
    var status = new Array();
    $.each(options, function (i, v) {
        if ($.inArray(i, ['url', 'target']) == '-1') {
            status.push(i + '=' + v);
        }
    });
    var status = status.join(',');
    var win = window.open(options.url, options.target, status);
    return win;
}

/**
 * 통신판매사업자 상세조회창
 * @param string businessNo 사업자 번호
 * @return
 */
function gd_popup_bizInfo(businessNo) {
    var url = 'http://www.ftc.go.kr/info/bizinfo/communicationViewPopup.jsp?wrkr_no=' + businessNo;
    var win = gd_popup({
        url: url
        , target: 'communicationViewPopup'
        , width: 750
        , height: 700
        , resizable: 'no'
        , scrollbars: 'yes'
    });
    win.focus();
    return win;
}
/**
 * 쿠키 생성
 */
function createCookie(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value  + "; path=/; expires=" + expires + ";";
}
/**
 * 팝업창 Cookie 컨트롤
 * @param string name 팝업창 이름 (코드_창종류)
 * @param object elemnt elemnt
 * @return
 */
function gd_popup_cookie(name, elemnt) {
    if (elemnt.checked === true) {
        createCookie(name,'true',1);
        var popupKind = name.split('_');
        if (popupKind[1] == 'window') {
            setTimeout('self.close()');
        } else {
            setTimeout("$('#" + name + "').hide()");
        }
    } else {
       createCookie(name, null);
    }
    return;
}

/**
 * 메일 도메인 선택
 */
function gd_select_email_domain(name,select) {
    if (typeof select === 'undefined') {
        select = 'emailDomain';
    }
    var $email = $(':text[name=' + name + ']');
    var $emailDomain = $('select[id='+select+']');
    $emailDomain.on('change', function (e) {
        var emailValue = $email.val();
        var indexOf = emailValue.indexOf('@');
        if (indexOf == -1) {
            if ($emailDomain.val() === 'self') {
                $email.val(emailValue + '@');
            } else {
                $email.val(emailValue + '@' + $emailDomain.val());
            }
            $email.trigger('focusout');
        } else {
            if ($emailDomain.val() === 'self') {
                $email.val(emailValue.substring(0, indexOf + 1));
                $email.focus();
            } else {
                $email.val(emailValue.substring(0, indexOf + 1) + $emailDomain.val());
                $email.trigger('focusout');
            }
        }
    });
}

/**
 * 도로명 주소 찾기 (팝업)
 *
 * @author artherot
 * @param string zoneCodeID zonecode input ID
 * @param string addrID address input ID
 * @param string zipCodeID zipcode input ID
 */
function gd_postcode_search(zoneCodeID, addrID, zipCodeID) {
    var win = gd_popup({
        url: '../share/postcode_search.php?zoneCodeID=' + zoneCodeID + '&addrID=' + addrID + '&zipCodeID=' + zipCodeID,
        target: 'postcode',
        width: 500,
        height: 450,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 비회원 개인정보 수집항목 동의 링크
 */
function gd_redirect_collection_agree() {
    window.open('/service/private.php');
}

/**
 * 파일 업로드 객체
 */
var gdAjaxUpload = {
    upload : function(data) {
        var formData = new FormData();
        for (var k in data.params){
            if (data.params.hasOwnProperty(k)) {
                formData.append(k, data.params[k]);
            }
        }
        if(data.onbeforeunload){
            window.onbeforeunload = data.onbeforeunload;
            data.formObj.on("submit", function () {
                window.onbeforeunload = null;
            });
        }

        if(data.formObj.find('[name=uploadType][value=ajax]').length < 1) {
            data.formObj.append('<input type="hidden"  name="uploadType" value="ajax" >');
        }
        var index = data.thisObj.closest('form').find('input:file').index(data.thisObj);
        formData.append('uploadFile', data.thisObj[0].files[0]);

        $.ajax({
            url: data.actionUrl,
            type: 'POST',
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (returnData) {
                returnData['index'] = index;
                if(returnData.result == 'ok') {
                    if ($('input[name="uploadFileNm[' + index + ']"]').length == 0) {
                        data.formObj.append("<input type='hidden' name='uploadFileNm[" + index + "]' value='" + returnData.uploadFileNm + "'>");
                        data.formObj.append("<input type='hidden' name='saveFileNm[" + index + "]' value='" + returnData.saveFileNm + "'>");
                    }
                    else {
                        $("input[name='uploadFileNm[" + index + "]']").val(returnData.uploadFileNm);
                        $("input[name='saveFileNm[" + index + "]']").val(returnData.saveFileNm);
                    }
                    if(typeof data.successAfter == 'function') {
                        data.successAfter(returnData);
                    }
                }
                else if(returnData.result == 'cancel'){
                    if ($("input[name='uploadFileNm[" + index + "]']").length > 0) {
                        $("input[name='uploadFileNm[" + index + "]']").remove();
                        $("input[name='saveFileNm[" + index + "]']").remove();
                    }
                }
                else {
                    gdAjaxUpload.isSuccess = false;
                    $('label[for=attach' + (index + 1) + '] .text').val('');  //파일시스템 텍스트 빈값 처리
                    alert(returnData.errorMsg);
                    if(typeof data.failAfter == 'function') {
                        data.failAfter(returnData);
                    }
                }
            }
        });
    }
};

/**
 * 프레임 리사이즈
 */
function gd_resize_frame(obj) {
    var iframeHeight = (obj).contentWindow.document.body.clientHeight;
    (obj).height = iframeHeight + 80;
}

/**
 * 회원다운로드 쿠폰 링크 다운 받기
 *
 * @param couponNo
 */
function couponLinkDown(couponNo) {
    var params = {
        mode: "couponDownLink",
        couponCode: couponNo
    };
    $.ajax({
        method: "POST",
        cache: false,
        url: "/mypage/coupon_link_down.php",
        data: params,
        success: function (data) {
            result = JSON.parse(data);
            alert(result['msg']);
        },
        error: function (data) {
            alert(result['msg']);
        }
    });
}

/**
 * PG 관련 영수증 보기
 *
 * @author artherot
 * @param string modeStr 카드, 현금영수증 종류 (card, cash)
 * @param string orderNo 주문 번호
 */
function gd_pg_receipt_view(modeStr, orderNo) {
    // 사이즈를 미리 설정 - 자동으로 창이 커지지 않아 미리 설정함
    var preWidth = 430;
    var preHeight = 700;

    // 미리 팝업창을 띄우기
    var prePopupData = {
        'url': 'about:blank',
        'target': 'show_receipt',
        'width': preWidth,
        'height': preHeight
    };
    var show_receipt = gd_popup(prePopupData);

    // 각 PG별 영수증 팝업창
    $.post('../share/show_receipt.php', {
        mode: modeStr,
        orderNo: orderNo
    }, function (data) {
        var infoData = data;
        if (typeof infoData['error'] == 'undefined') {
            gd_popup(infoData);
        }
        else {
            alert(infoData['error']);
            show_receipt.close();
        }
    }, 'json');
}

/*** IE 버전 체크 ***/
function gd_is_ie () {
    var nav = navigator.userAgent.toLowerCase();
    if (nav.indexOf('msie') != -1) {
        return parseInt(nav.split('msie')[1]);
    } else {
        return false;
    }
}

/**
 * 쿼리스트링값 찾기
 * @param query
 * @param variable
 * @returns {string}
 */
function gd_get_query_variable(query, variable) {
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    console.log('Query variable %s not found', variable);
}

/**
 * 동적 스크립트 바인딩 (스크립트 로딩 후 메서드 실행되도록 처리)
 *
 * @author Jong-tae Ahn
 * @param number
 * @param places
 * @param symbol
 * @param thousand
 * @param decimal
 * @returns {string}
 */
function add_script(url, callback) {
    var done = false; // 스크립트 로딩 여부
    var head = document.getElementsByTagName("head")[0] || document.documentElement;
    var script = document.createElement("script");

    //script.charset = 'UTF-8';
    script.src = url;
    script.onload = script.onreadystatechange = function () {
        if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
            done = true;
            callback();

            // IE에서 메모리 누수 방지를 위한 처리
            script.onload = script.onreadystatechange = null;
            if (head && script.parentNode) {
                head.removeChild(script);
            }
        }
    };

    // Use insertBefore instead of appendChild  to circumvent an IE6 bug.
    // This arises when a base node is used (#2709 and #4378).
    head.insertBefore(script, head.firstChild);
}




/*** 스토리지 지원 여부 ***/
function supports_html5_storage() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}

/*** 세션 스토리지 저장 ***/
function saveSession(control_key, control_value) {
    if (!supports_html5_storage()) {
        createCookie(control_key, control_value, 7);
    } else {
        sessionStorage[control_key] = control_value;
    }
};

/*** 세션 스토리지 로드 ***/
function loadSession(control_key) {
    var control_value;
    if (!supports_html5_storage()) {
        control_value = readCookie(control_key);
    } else {
        control_value = sessionStorage[control_key];
    }
    return control_value;
};

/*** 로컬 스토리지 저장 ***/
function saveVal(control_key, control_value) {
    if (!supports_html5_storage()) {
        createCookie(control_key, control_value, 7);
    } else {
        localStorage.setItem(control_key, control_value);
    }
};

/*** 로컬 스토리지 로드 ***/
function loadVal(control_key) {
    var control_value;
    if (!supports_html5_storage()) {
        control_value = readCookie(control_key);
    } else {
        control_value = localStorage.getItem(control_key);
    }
    return control_value;
};

/*** 애보트 휴대폰 인증번호 받아옴 2019-12-09 Cossia ***/
/*** SetCossiaAjaxController 수정해야함  ***/
function get_pass_num(phone){
	var jqXHR = $.ajax({
		url:'/member/set_cossia_ajax.php',		
		type:'POST',
		dataType: "json",
		async: false,
		data:{'phone':phone},
		success: function(d){},
		error:function(request,status,error){
			console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
       	}
	});
	return jqXHR.responseJSON;
}

/** 회원가입시 text와 검사들 **/
var input_text = {'name':'최소 2 이상 입력해 주세요.', 'email':'이메일 형식이 이상합니다.', 'pass_lenght':'8자리 이상 16자리 이하로 설정해 주세요', 'pass_text':'영문, 숫자, 특수문자의 조합으로 입력해 주세요'};
function test_password(obj){
	if( $(obj).val().length < 8 || $(obj).val().length > 16 ){
		class_text_add( $(obj), 'pass-error', 'pass_lenght');
		return false;
	}
	var check = /(?=.*\d{1,50})(?=.*[~`!@#$%\^&*()-+=]{1,50})(?=.*[a-zA-Z]{1,50}).{8,50}$/;
	if( !check.test($(obj).val()) ){
		class_text_add( $(obj), 'pass-error', 'pass_text');
		return false;
	}
	class_text_remove($(obj), 'pass-error');
}
function name_length(name){
	"use strict";
	if(name.length >= 2) return true;
	else return false;
}
function class_text_remove(obj, id){
	obj.removeClass('member_warning');
	$('#'+id).text('');
}
function class_text_add(obj, id, key){
	obj.addClass('member_warning');
	$('#'+id).text(input_text[key]);
}
function co_email_ck(value){
	var regExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
	if (value.match(regExp) != null) return true;
	else return false;
}
var co_count,
	co_obj,
	pass_num = null,
	is_pass = false;
function getPassNum(obj){
	if( !$('input[name="cellPhone"]').val() ){
		alert('전화번호는 필수 입니다.');
		return;
	}
	pass_num = get_pass_num( $('input[name="cellPhone"]').val() );
	sms_min_count(obj);
}
function submit_this(obj){
	var max_ = Number($(obj).data('ckcount')),
		msg = [];
	if( $(obj).data('ckcount') ){
		for( var i=1; i<=max_; i++  ){
			if( !$('input[name="'+ $(obj).data('ck'+i) +'"]').val() ){
				msg.push( $(obj).data('ck'+i+'text') );
			}
		}
	}
	if( msg.length != 0 ){
		alert( msg.join(", ")+' 은(는) 필수 입니다.' );
		return;
	}
	if(is_pass){
		$('#'+$(obj).data('form')).submit();
	}else{
		alert('휴대폰 인증이 필요합니다.');
	}
}
function checkPass(){
	if(!pass_num){
		alert('인증번호를 요청해 주세요');
		return;
	}
	sms_min_stop();
	if( pass_num != $('input[name="passNum"]').val() ){
		alert('인증번호가 다릅니다.');
		return;
	}
	alert('인증이 완료되었습니다.');
	is_pass = true;
}


function sms_min_count(obj){
	co_obj = obj;
	$(obj).prop('disabled', true);
	$( '#'+$(obj).data('undi') ).prop('disabled', false);
	var minute = 1,
		second = 59;
	co_count = setInterval(function(){
		$(obj).text(co_pad(minute, 2)+':'+co_pad(second, 2));
		if( second == 0 ){
			if( minute == 0 ){
				sms_min_stop();
			}
			minute--;
			second = 60;
		}
		second--;
	}, 1000);
}
function sms_min_stop(){
	clearInterval(co_count);
	$(co_obj).prop('disabled', false).text('인증번호요청');
}

function co_pad(n, width) {
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
}
