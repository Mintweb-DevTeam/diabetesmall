/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 * @history
 * 		2017-04-24 임명선 | 최초작성
 */


$(function(){

	$(window).on({
		'scroll':function(){
			if(resizeChk != 0){
				var sc = $(this).scrollTop();
				var h_offset = $('.top_area').innerHeight();
				if(sc > h_offset){
					$('#header_wrap header').addClass('h_on');
					$('#header_wrap').css('padding-top',$('#header_wrap header').outerHeight());
					$('.fixed_btn_top').show();
				}else{
					$('#header_wrap header').removeClass('h_on');
					$('#header_wrap').css('padding-top',0);
					$('.fixed_btn_top').hide();
				}
			}
		}
	});

	// 우측 메뉴
	var posY; 
	var myScroll;
	var resizeChk = 0;
	var gd_right_menu = function(){
		if($('#wrap nav').css('display') == 'none'){
			$('#wrap nav').show();
			$('nav .bg').fadeIn();
			$('nav .bg').bind('touchmove', function(e){e.preventDefault()}); //모바일 스크롤 방지
			myScroll = new IScroll('.nav_bg_box .nav_iscroll_box', { mouseWheel: true, click: true });
			gd_menu_resize();
			gd_menu_motion('0');
		}else{
			$('nav .bg').fadeOut(function(){
				$('#wrap nav').hide();
				gd_menu_resize();
			});
			myScroll.destroy();
			gd_menu_motion('-290px');
		}
	}
	var gd_menu_motion = function(width_num){
		$('.nav_bg_box .nav_box').animate({
			'margin-left': width_num
		},'5000',function(){
		
		});
	}
	var gd_iscroll_re = function(){
		myScroll.refresh();
	}

	var gd_menu_resize = function(){
		if($('#wrap nav').css('display') != 'none'){
			if($('body').css('overflow') != 'hidden') posY = $('body').scrollTop();
			//$('nav .nav_bg_box .nav_box .nav_content_box').height($(window).innerHeight()).scrollTop(0);
			$('.nav_content_bbox').height($(window).innerHeight());
			$('#wrap').height($(window).innerHeight());
			$('#wrap').css('overflow-y','scroll');
			if($('body').css('overflow') != 'hidden') $('#wrap').scrollTop(posY);
			$('#wrap, body').css('overflow','hidden');
			resizeChk = 0;
		}else{
			$('#wrap, body').removeAttr('style');
			if(resizeChk == 0){
				$('body').scrollTop(posY);
				resizeChk = 1;
			}
		}
	}
	$(window).on('resize',gd_menu_resize);
	$('.side_menu, nav .bg, .left_close').on({
		'click':function(){
			gd_right_menu();
		}
	});
	gd_menu_resize();
	//gd_right_menu();
	
	/* 카테고리 메뉴 */
	$('.category_side li a').on({
		'click':function(){
			var sidedp = $(this).parent().find(' > ul').css('display');
			if(sidedp == 'none'){
				$(this).parent().find('> ul').slideDown('fast', function(){
					gd_iscroll_re();
				});
				$(this).addClass('on');
			}else{
				$(this).parent().find(' > ul').slideUp('fast', function(){
					gd_iscroll_re();
				});
				$(this).removeClass('on');
			}
		}
	});
	/* 좌측 탭메뉴 */
	var tabmenu_idx = 0;
	$('.nav_tabmenu_box .nav_tabmenu li').on({
		'click':function(){
			var idx = $(this).index();
			if(tabmenu_idx == idx) return; 
			tabmenu_idx = idx;
			$('.nav_tabmenu_box .nav_tabmenu li').removeClass('on');
			$(this).addClass('on');
			$('.nav_tabmenu_box .tab_menu0, .tab_menu1').hide();
			$('.nav_tabmenu_box .tab_menu'+idx).fadeIn('fast', function(){
				gd_iscroll_re();
			});
			
			
		}
	});

	// 장바구니 이동 레이어창 닫기 클릭
    $('body').on('click', '.add_cart_layer .btn_close', function(e) {
        $('#addCartLayer').addClass('dn');
        $('#layerDim').addClass('dn');
    });

    // 찜리스트 레이어창 닫기 클릭
    $('body').on('click', '.add_wish_layer .btn_close', function(e) {
        $('#addWishLayer').addClass('dn');
        $('#layerDim').addClass('dn');
    });

	
	$('.modal_open').on('click',function(){
		var my_modal = $(this).attr('rel');
		console.log(my_modal);
		$('body,#wrap').addClass('modal_on');
		$('body').append('<div class="modal_blind"></div>');
		$('#'+my_modal).fadeIn();
	});
	$(document).on('click','.modal_blind' , function(){
		$('.modal_blind').remove();
		$('body,#wrap').removeClass('modal_on');
		$('.modal_contents_wrap').fadeOut();
	});
	$('.modal_close').on('click',function(){
		$('.modal_blind').remove();
		$('body,#wrap').removeClass('modal_on');
		$('.modal_contents_wrap').fadeOut();
	});
});