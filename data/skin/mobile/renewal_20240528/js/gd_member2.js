/**
 *
 *  This is commercial software, only users who have purchased a valid license
 *  and accept to the terms of the License Agreement can install and use this
 *  program.
 *
 *  Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  versions in the future.
 *
 *  @copyright ⓒ 2016, NHN godo: Corp.
 *  @link http://www.godo.co.kr
 *
 */
/**
 * Created by yjwee on 2016-02-19.
 */
var gd_member2 = (function ($, _, window, document, undefined) {
    "use strict";

    var key_code, form, policy, is_my_page, validate, validate_settings, messages, focusout_event, replace_keyup;

    //@formatter:off
    key_code = {
        ALT: 18, BACKSPACE: 8, CAPS_LOCK: 20, COMMA: 188, COMMAND: 91, COMMAND_LEFT: 91, COMMAND_RIGHT: 93, CONTROL: 17, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, INSERT: 45, LEFT: 37, MENU: 93, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108, NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SHIFT: 16, SPACE: 32, TAB: 9, UP: 38, WINDOWS: 91
    };
    //@formatter:on

    // validator 플러그인 직접 선언
    $.validator.addMethod("alphakorean", function (value, element) {
        return this.optional(element) || !/[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/i.test(value);
    }, "Letters, numbers, and underscores only please");
    $.validator.addMethod("alphakoreanGlobal", function (value, element) {
        return this.optional(element) || !/[\/\'\"\\\|]/.test(value);
    }, "Letters, numbers, and underscores only please");
    $.validator.addMethod("alphakornumeric", function (value, element) {
        return this.optional(element) || !/[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣0-9\u119E\u11A2]/i.test(value);
    }, "Letters, numbers, and underscores only please");

    policy = {};
    is_my_page = false;
    validate_settings = {
        defaults: {
            ignore: ".ignore",
            focusCleanup: true,
            rules: {
                memPwRe: {
                    required: true,
                    equalTo: "#newPassword"
                },
                oldMemPw: {
                    required: true
                },
                memNm: {
                    alphakoreanGlobal: true,
                },
                ceo: {
                    alphakorean: true,
                },
                service: {
                    alphakorean: true,
                },
                item: {
                    alphakorean: true,
                },
                addressSub: {
                    required: function () {
                        return policy.address.require == 'y';
                    }
                },
                comAddressSub: {
                    required: function () {
                        return policy.comAddress.require == 'y' && ($(':radio[name=memberFl]:checked').val() == 'business' || is_my_page);
                    }
                },
                busiNo: {
                    rangelength: function () {
                        return [$('input[name="busiNo"]').data('charlen'), $('input[name="busiNo"]').data('charlen')];
                    }
                }
            },
            messages: {
                memPwRe: {
                    equalTo: __("비밀번호가 서로 다릅니다.")
                },
                memNm: {
                    alphakorean: __("이름을 정확하게 입력해주세요.")
                },
                ceo: {
                    alphakorean: __("대표자명을 정확하게 입력해주세요.")
                },
                service: {
                    alphakorean: __("업태를 정확하게 입력해주세요.")
                },
                item: {
                    alphakorean: __("종목을 정확하게 입력해주세요.")
                },
                busiNo: {
                    rangelength: function () {
                        return __("사업자번호는 %i자로 입력해야 합니다.", $('input[name="busiNo"]').data('charlen'));
                    }
                }
            },
            errorElement: 'div',
            errorClass: 'error c_red',
            errorPlacement: function (error, element) {
                try {
                    var elementId = $(error).attr('id');
                    var $error = $('#' + elementId);
                    if ($error.length === 0) {
                        if (elementId == 'calendarFl-error') {
                            $error = $('#birthDt-error');
                        }
                        if (elementId == 'birthDt-error') {
                            $error = $('#calendarFl-error');
                        }
                        if (elementId == 'address-error' || elementId == 'addressSub-error') {
                            $error = $('#address-error');
                        }
                        if (elementId == 'comAddress-error' || elementId == 'comAddressSub-error') {
                            $error = $('#comAddress-error');
                        }
                    }

                    if ($error.length === 0) {
                        var closest = element.closest('td');
                        if (closest.length < 1) {
                            closest = element.closest('.input_content');
                        }
                        if (is_my_page && ( element[0].name === 'memPw' || element[0].name === 'memPwRe')) {
                            closest = element.closest('.input-block');
                        }
                        if (closest.find('div.c_red').length == 0) {
                            error.appendTo(closest);
                        }
                    } else {
                        $error.replaceWith(error);
                    }
                } catch (e) {
                    error.appendTo(element.closest('td'));
                }
            }, showErrors: function (errorMap, errorList) {
                //errorList[0].element.focus();
                this.defaultShowErrors();
            },
            highlight: function (element) {
                this.errorList[0].element.focus();
                if(element.type == 'radio') {
                    $(element).parent().css('border', '3px solid #fa2828');
                } else {
                    $(element).css('border', '3px solid #fa2828');
                }
            },
            unhighlight: function (element) {
                if(element.type == 'radio') {
                    $(element).parent().css('border', '0');
                } else {
                    $(element).css('border', '1px solid #d0d0d0');
                }
            },
            onkeyup: onkeyup,
            onfocusout: onfocusout,
            success: function (label, element) {
                var $label = $(label);
                $label.removeClass('error c_red').addClass('c_blue').css('display', '');
            }
        }
    };

    /**
     * validator 메시지
     * @type {{required: string, email: string, number: string, digits: string, equalTo, maxlength, minlength, rangelength}}
     */
    messages = {
        required: __("필수항목 입니다."),
        number: "Please enter a valid number.",
        digits: "Please enter only digits.",
        equalTo: $.validator.format(__("입력 값이 서로 다릅니다.")),
        maxlength: $.validator.format(__("최대 %1$s 이하로 입력해 주세요.", "{0}")),
        minlength: $.validator.format(__("최소 %1$s 이상 입력해 주세요.", "{0}")),
        rangelength: $.validator.format(__("허용 범위를 초과한 값입니다. %1$s 까지 입력해주세요.", "{0} ~ {1}"))
    };

    /**
     * 입력 값 비동기 검증
     * @type {{memId: focusout_event.memId, memPw: focusout_event.memPw, email: focusout_event.email, nickNm: focusout_event.nickNm, recommId: focusout_event.recommId}}
     */
    focusout_event = {
        memId: function (element) {
            ajax_validate(element, {
                memId: element.value, mode: "overlapMemId"
            });
        },
        memPw: function (element) {
            ajax_validate(element, {
                memPw: element.value, mode: "validateMemberPassword"
            });
        },
        email: function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), email: element.value, mode: "overlapEmail"
            });
        },
        nickNm: function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), nickNm: element.value, mode: "overlapNickNm"
            });
        },
        recommId: function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), recommId: element.value, mode: "checkRecommendId"
            });
        },
        busiNo: function (element) {
            if ($(element).data('overlap-businofl') === 'y' && parseInt(element.value) !== $(element).data('oldbusino')) {
                ajax_validate(element, {
                    memId: $('input[name=memId]').val(), busiNo: element.value, charlen: $(element).data('charlen'), mode: "overlapBusiNo"
                });
            }
        }
    };

    /*
     * 비동기 처리 함수
     * @param element
     * @param data
     */
    function ajax_validate(element, data) {
        var $target = $(element);
        var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
        $ajax.done(function (data, textStatus, jqXHR) {
            var code = data.code;
            var message = data.message;
            var closest = $target.closest('td');
            if (closest.length < 1) {
                closest = $target.closest('.input_content');
            }

            var blue = closest.find('.c_blue');
            if (blue.length < 1) {
                blue = $('<div class="c_blue"></div>');
                closest.find('.c_red, .c_blue').remove();
                closest.append(blue);
            }
            if (_.isUndefined(code) && _.isUndefined(message)) {
                blue.text(data);
            } else {
                blue.addClass('c_red').text(message);
                blue.removeClass('c_blue');
            }
        });
    }

    /**
     * validator onkeyup 함수
     * @param element
     * @param event
     * @returns {boolean}
     */
    function onkeyup(element, event) {
        // 2016.11.16 bugfix 모바일인 경우 자판이 천지인일때 한글입력이 안되는 오류로 인해 해당 함수 태우지 않도록 수정
        // 패턴에 Kor가 들어간 경우 체크하지 않음
        var pattern = $(element).data('pattern');
        var check_kor_pattern = false;
        if (pattern) {
            check_kor_pattern = pattern.indexOf('Kor') != -1;
        }
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && check_kor_pattern) {
            return false;
        }
        if (check_key_code2(event)) {
            return true;
        }
        if ($.isFunction(replace_keyup[$(element).data('pattern')])) {
            replace_keyup[$(element).data('pattern')](element);
        }
    }

    replace_keyup = {
        gdEngNum: function (element) {
            element.value = replace_pattern(element.value, /[^\da-zA-Z]/g, '');
        },
        gdEngKor: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/g, '');
            }
        },
        gdEngKorNum: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣0-9\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/g, '');
            }

        },
        gdNum: function (element) {
            element.value = replace_pattern(element.value, /[^\d]/g, '');
        },
        gdEng: function (element) {
            element.value = replace_pattern(element.value, /[^a-zA-Z]/g, '');
        },
        gdKor: function (element) {
            if (regexp_test(element.value, /[^가-힣ㄱ-ㅎㅏ-ㅣ\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^가-힣ㄱ-ㅎㅏ-ㅣ\u119E\u11A2]/g, '');
            }
        },
        gdMemberId: function (element) {
            element.value = replace_pattern(element.value, /[^\da-zA-Z\.\-_@]/g, '');
        },
        gdMemberNmGlobal: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[\/\'\"\\\|]/)) {
                element.value = replace_pattern(element.value, /[\/\'\"\\\|]/g, '');
            }
        }
    };

    /**
     * validator onfocusout 함수
     * @param element
     */
    function onfocusout(element) {
        if (element.value.length && $(element).valid()) {
            if ($.isFunction(focusout_event[element.name])) {
                focusout_event[element.name](element);
            }
        }
    }

    /**
     * jquery validation의 키 체크 함수
     * @param event
     * @returns {boolean}
     */
    function check_key_code2(event) {
        // Avoid revalidate the field when pressing one of the following keys
        /* Shift       => 16 Ctrl        => 17 Alt         => 18
         Caps lock   => 20 End         => 35 Home        => 36
         Left arrow  => 37 Up arrow    => 38 Right arrow => 39
         Down arrow  => 40 Insert      => 45 Num lock    => 144 AltGr key   => 225*/
        var excludedKeys = [
            16, 17, 18, 20, 35, 36, 37,
            38, 39, 40, 45, 144, 225
        ];

        return event.which === 9 || $.inArray(event.keyCode, excludedKeys) !== -1;
    }

    /**
     * validator 설정
     * @param settings
     */
    function set_defaults_validator(settings) {
        $.extend($.validator.messages, messages);
        $.extend(true, settings, validate_settings.defaults);
    }

    /**
     * validator 기본 설정 값 제거
     */
    function remove_default_settings() {
        delete $.validator.defaults.invalidHandler;
        delete $.validator.defaults.onkeyup;
        delete $.validator.defaults.onfocusout;
    }

    /**
     * 회원가입 정책 불러오기
     */
    function load() {
        var params = [];
        params.push({name: "mode", value: "getJoinPolicy"});
        var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: params, async: false});
        $ajax.done(function (data) {
            policy = data;
        });
    }

    /**
     * 회원가입 정책 validator rules 에 추가
     */
    function add_rules() {
        var settings = validate_settings.defaults;
        $.each(policy, function (idx, item) {
            if (settings.rules[idx] === undefined) {
                settings.rules[idx] = {};
            }
            if (item.require === 'y') {
                settings.rules[idx].required = true;
                var $markTarget = $('input[name="' + idx + '"]');
                if ($markTarget.length < 1) {
                    $markTarget = $('select[name="' + idx + '"]');
                }
                if ($markTarget.length < 1) {
                    $markTarget = $('textarea[name="' + idx + '"]');
                }
                if ($markTarget.length < 1) {
                    $markTarget = $('input[name="' + idx + '[]"]');
                    settings.rules[idx + '[]'] = {required: true};
                }
                $markTarget.removeClass('ignore');
                $markTarget.closest('div.input_content').prev('div').addClass('required');
            }
            if (item.minlen > 0) {
                settings.rules[idx].minlength = item.minlen;
            }
            if (item.maxlen > 0) {
                settings.rules[idx].maxlength = item.maxlen;
            }
            if (item.minlen > 0 && item.maxlen > 0) {
                settings.rules[idx].rangelength = [item.minlen, item.maxlen];
            }
        });
    }

    /**
     * 회원가입
     * @param func
     */
    function validation_submit(func) {
        validate = form.validate();
        set_defaults_validator(validate.settings);
        if (is_my_page) {
            $('input[name="memId"]').rules("remove");
            if ($('div.password-reset').has('dn')) {
                $('input[name="oldMemPw"]').rules("remove");
                $('input[name="memPw"]').rules("remove");
                $('input[name="memPwRe"]').rules("remove");
            }
        }
        var $marriYear = $('select[name="marriYear"]');
        var $marriMonth = $('select[name="marriMonth"]');
        var $marriDay = $('select[name="marriDay"]');
        if ($('.marridateinfo').hasClass('dn') && $marriYear.length > 0 && $marriMonth.length > 0 && $marriDay.length > 0) {
            $marriYear.rules("remove");
            $marriMonth.rules("remove");
            $marriDay.rules("remove");
        }
        if ($('input[name="memberFl"]:checked').val() !== 'business') {
            $('.businessinfo').find('input').addClass("ignore").val('');
        }
        validate.settings.submitHandler = func;

        if (validate.form()) {
            $('.join_ok_btn').attr("disabled", true);
            form.submit();
        } else {
            go_to_by_scroll($('div.c_red:first', form));
        }
    }

    function go_to_by_scroll(object) {
        var offset = $(object).offset();
        if(offset){
            $('html, body').animate({
                scrollTop: offset.top - 150
            }, 'fast');
        }
    }

    /**
     * 비동기 처리 후 새로고침
     * @param url
     * @param data
     */
    function ajax_reload(url, data) {
        var $ajax = $.ajax(url, {type: "post", data: data});
        $ajax.done(function (data, textStatus, jqXHR) {
            var code = data.code;
            var message = data.message;
            if (_.isUndefined(code) && _.isUndefined(message)) {
                var location = data.location;
                if (_.isUndefined(location)) {
                    alert(data);
                    top.location.reload(true);
                } else {
                    top.location.href = location;
                }
            } else {
                alert(message);
            }
        });
    }

    var regexp_test = function (string, pattern) {
        if (string === undefined || string.length < 1) {
            return false;
        }
        return pattern.test(string);
    };

    var regexp_match = function (string, pattern) {
        if (string === undefined || string.length < 1) {
            return;
        }
        var result = string.match(pattern);
        return result ? result.join('') : '';
    };

    var replace_pattern = function (string, pattern, c) {
        if (string === undefined || string.length < 1) {
            return '';
        }
        return string.replace(pattern, c);
    };

    /**
     * 검증
     */
    function validate_information($target, data) {
        if (!$target.hasClass('valid')) {
            var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
            $ajax.done(function (data, textStatus, jqXHR) {
                var code = data.code;
                var message = data.message;
                if (_.isUndefined(code) && _.isUndefined(message)) {
                    $target.data('valid-message', data);
                    display_validation($target);
                } else {
                    $target.data('valid-message', message);
                    display_invalidation($target);
                }
            });
        }
    }

    /**
     * 입력값 검증 통과 메시지 표시
     */
    function display_validation($target) {
        $target.addClass('valid');
        var $closest = $target.closest('td');
        if ($closest.length === 0) {
            $closest = $target.closest('div.input_pw');
        }
        $closest.find('div.txt-field').removeClass('error');
        $closest.find('div.msg').remove();
        $closest.find('div:first').after('<div class="msg c_blue">' + $target.data('valid-message') + '</div>');
    }

    /**
     * 입력값 검증 실패 메시지 표시
     */
    function display_invalidation($target) {
        $target.removeClass('valid');
        var $closest = $target.closest('td');
        if ($closest.length === 0) {
            $closest = $target.closest('div.input_pw');
        }
        var $txtField = $closest.find('div.txt-field');

        $closest.find('div.msg').remove();
        $txtField.removeClass('error');
        $txtField.addClass('error');
        $closest.find('div:first').after('<div class="msg c_red error">' + $target.data('valid-message') + '</div>');
    }

    return {
        init: function (form) {
            remove_default_settings();
            validate = form.validate(validate_settings);
            load();
            add_rules();

            set_defaults_validator(validate.settings);

            this.set_form(form);

            /*var $email = $('input[name=email]');
            if ($email.length > 0) {
                if ($email.val().length > 0) {
                    $email.focusout();
                }
            }*/
            var $memId = $('#memId');
            if ($memId.length > 0) {
                if ($memId.val().length > 0) {
                    $memId.valid();
                }
            }
        }, save: function (e) {
            var $this = $(e.target);
            if ($this.data('action') === 'click') {
                // $this.html($this.data('button-html'));
                alert(__('진행중입니다'));
                return false;
            } else {
                form = e.data.form;
                validation_submit(function (form) {
                    //var params = $(form).serializeArray();
                    //ajax_reload(form.action, params);
                    $('iframe[name=ifrmProcess]').load(function () {
                        var $button = $(parent.document.getElementsByClassName('join_ok_btn')[0]);
                        $button.html('<em>' + $button.val() + '</em>');
                        $button.data('action', '');
                    });
                    $this.data('action', 'click');
                    $this.html('<em>' + __('진행 중') + '</em>');
                    form.target = 'ifrmProcess';
                    form.submit();
                });
            }
        }, set_form: function (form) {
            this.form = form;
        }, set_my_page: function (b) {
            is_my_page = b;
        }, validate_password: function () {
            var $target = $(':password[name=memPw]');
            $target.on('keyup focusout', function (e) {
                e.preventDefault();
                var minLength = 10, maxLength = 16;
                var targetLength = $target.val().length;
                if (targetLength >= minLength && targetLength <= maxLength) {
                    validate_information($target, {
                        memPw: $target.val(),
                        mode: "validateMemberPassword"
                    });
                } else {
                    $target.data('valid-message', __('비밀번호는 %1$i자 이상 %2$i자 이하여야 합니다.', [minLength, maxLength]));
                    display_invalidation($target);
                }
            }).on('focusin', function (e) {
                e.preventDefault();
                $target.removeClass('valid error');
            });
        }, validate_same_password: function () {
            var $target = $(':password[name=memPwRe]');
            var $memPw = $(':password[name=memPw]');
            $target.on('focusout', function (e) {
                e.preventDefault();
                if (_.isEmpty($target.val())) {
                    $target.data('valid-message', __("비밀번호를 입력해주세요."));
                    display_invalidation($target);
                } else {
                    if ($memPw.val() === $target.val()) {
                        $target.data('valid-message', __("비밀번호가 일치합니다."));
                        display_validation($target);
                    } else {
                        $target.data('valid-message', __("비밀번호가 일치하지 않습니다."));
                        display_invalidation($target);
                    }
                }
            }).on('focusin', function (e) {
                e.preventDefault();
                $target.removeClass('valid error');
            });
        }
    };
})(jQuery, _, window, document);
