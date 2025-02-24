<?php
namespace Component\Subscription;

use App;
use Session;
use Request;
use Globals;
use Component\Database\DBTableField;
use Component\Goods\Goods;
use Framework\Utility\SkinUtils;

class Cart  extends \Bundle\Component\Cart\Cart
{
    /* 정기배송 장바구니 저장 */
   public function saveInfoCart($data = [])
   {

       //if (!gd_is_login())
       //    return false;

       $siteKey = Session::get('siteKey');
       $memNo = Session::get("member.memNo");
       $stamp = time();
       $goods = [];
       if ($data['goodsNo']) {
           $data['memberCouponNo'] = $data['couponApplyNo'];
           unset($data['couponApplyNo']);
           
          // 쿠폰이 적용 되면 쿠폰의 상태를 변경
          if ($data['memberCouponNo']) {
              // 쿠폰 모듈
              $coupon = \App::load('\\Component\\Coupon\\Coupon');
               $memberCouponUsable = $coupon->getMemberCouponUsableCheck($data['memberCouponNo']);
               if ($memberCouponUsable) {
                   $coupon->setMemberCouponState($data['memberCouponNo'], 'cart');
               } else {
                   return false;
               }
           }
           
           $deliveryCollectFl = gd_isset($data['deliveryCollectFl'], 'pre');
           $deliveryMethodFl = gd_isset($data['deliveryMethodFl'], 'delivery');
           
           $period = gd_isset($data['period'], "1_week");
           
            foreach ($data['goodsNo'] as $k => $goodsNo) {
                $optionSno = gd_isset($data['optionSno'][$k], 0);
                $addGoodsNo = $addGoodsCnt = $memberCouponNo = "";
                if ($data['addGoodsNo'][$k])
                    $addGoodsNo = json_encode($data['addGoodsNo'][$k]);
                
                if ($data['addGoodsCnt'][$k])
                    $addGoodsCnt = json_encode($data['addGoodsCnt'][$k]);
                
                $goodsCnt = gd_isset($data['goodsCnt'][$k], 1);
                
                $optionText = json_encode($data['optionText'][$k], JSON_UNESCAPED_UNICODE);
                
                if ($data['memberCouponNo'][$k])
                    $memberCouponNo = $data['memberCouponNo'][$k];
                
                $sql = "DELETE FROM wm_subscription_cart WHERE goodsNo='{$goodsNo}' AND optionSno='{$optionSno}' AND period='{$period}'";
                $this->db->query($sql);
                $sql = "INSERT INTO wm_subscription_cart 
                                SET 
                                    memNo='{$memNo}',
                                    goodsNo='{$goodsNo}',
                                    period='{$period}',
                                    optionSno='{$optionSno}',
                                    goodsCnt='{$goodsCnt}',
                                    addGoodsNo='{$addGoodsNo}',
                                    addGoodsCnt='{$addGoodsCnt}',
                                    optionText='{$optionText}',
                                    deliveryCollectFl='{$deliveryCollectFl}',
                                    deliveryMethodFl='{$deliveryMethodFl}',
                                    memberCouponNo='{$memberCouponNo}',
                                    regStamp='{$stamp}',
									siteKey='{$siteKey}'";
                if ($this->db->query($sql))
                   $goods[] = ['goodsNo' => $goodsNo, 'optionSno' => $optionSno];
             } // endforeach 
        } // endif 
        
        if ($goods) {

			if(empty($memNo))
				return $this->db->insert_id();
            else
				return true;
        }
   }
   
   public function updateInfoCart($data = [])
   {
       if (!gd_is_login())
           return false;
       
       $server = \Request::server()->toArray();
       if ($data['cartSno']) {
           $deliveryCollectFl = $this->db->escape(gd_isset($data['deliveryCollectFl'], 'pre'));
           $deliveryMethodFl = $this->db->escape(gd_isset($data['deliveryMethodFl'], 'delivery'));
            $stamp = time();
            $memNo = Session::get("member.memNo");
            foreach ($data['goodsNo'] as $k => $goodsNo) {
                $optionSno = $this->db->escape(gd_isset($data['optionSno'][$k], 0));
                $addGoodsNo = $addGoodsCnt = $memberCouponNo = "";
                if ($data['addGoodsNo'][$k])
                    $addGoodsNo = $this->db->escape(json_encode($data['addGoodsNo'][$k]));
                
                if ($data['addGoodsCnt'][$k])
                    $addGoodsCnt = $this->db->escape(json_encode($data['addGoodsCnt'][$k]));
                
                $goodsCnt = $this->db->escape(gd_isset($data['goodsCnt'][$k], 1));
                
                $optionText = json_encode($data['optionText'][$k], JSON_UNESCAPED_UNICODE);
                $common = "       
                                        memNo='{$memNo}',
                                        goodsNo='{$goodsNo}',
                                        optionSno='{$optionSno}',
                                        goodsCnt='{$goodsCnt}',
                                        addGoodsNo='{$addGoodsNo}',
                                        addGoodsCnt='{$addGoodsCnt}',
                                        optionText='{$optionText}',
                                        deliveryCollectFl='{$deliveryCollectFl}',
                                        deliveryMethodFl='{$deliveryMethodFl}'";
                if ($k == 0) {                  
                    $sql = "UPDATE wm_subscription_cart 
                                    SET 
                                        {$common},
                                        modStamp='{$stamp}'
                                 WHERE idx='{$data['cartSno']}'";
                } else {
                     $sql = "INSERT INTO wm_subscription_cart 
                                SET 
                                    memNo='{$memNo}',
                                    {$common},
                                    regStamp='{$stamp}'";
                }
                
                return $this->db->query($sql);
            }
       }
   }
   
   
   public function getCartList($idx = null, $address = null, $postValue = [], $isTemp = false, $tmpMemNo = 0, $tmpDiscount = 0, $isAdmin = false)
   {
      
       $getCart = [];       
       if (gd_is_login() || $tmpMemNo) {
           $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
           
          // 절사 정책 가져오기
          $truncGoods = Globals::get('gTrunc.goods');
           
           $arrExclude['option'] = [
                'goodsNo',
                'optionNo',
            ];
            $arrExclude['addOptionName'] = [
                'goodsNo',
                'optionCd',
                'mustFl',
            ];
            $arrExclude['addOptionValue'] = [
                'goodsNo',
                'optionCd',
            ];
            $arrInclude['goods'] = [
                'goodsNm',
                'commission',
                'scmNo',
                'purchaseNo',
                'goodsCd',
                'cateCd',
                'goodsOpenDt',
                'goodsState',
                'imageStorage',
                'imagePath',
                'brandCd',
                'makerNm',
                'originNm',
                'goodsModelNo',
                'goodsPermission',
                'goodsPermissionGroup',
                'goodsPermissionPriceStringFl',
                'goodsPermissionPriceString',
                'onlyAdultFl',
                'onlyAdultImageFl',
                'goodsAccess',
                'goodsAccessGroup',
                'taxFreeFl',
                'taxPercent',
                'goodsWeight',
                'totalStock',
                'stockFl',
                'soldOutFl',
                'salesUnit',
                'minOrderCnt',
                'maxOrderCnt',
                'salesStartYmd',
                'salesEndYmd',
                'mileageFl',
                'mileageGoods',
                'mileageGoodsUnit',
                'goodsDiscountFl',
                'goodsDiscount',
                'goodsDiscountUnit',
                'payLimitFl',
                'payLimit',
                'goodsPriceString',
                'goodsPrice',
                'fixedPrice',
                'costPrice',
                'optionFl',
                'optionName',
                'optionTextFl',
                'addGoodsFl',
                'addGoods',
                'deliverySno',
                'delFl',
                'hscode',
                'goodsSellFl',
                'goodsSellMobileFl',
                'goodsDisplayFl',
                'goodsDisplayMobileFl',
                'mileageGroup',
                'mileageGroupInfo',
                'mileageGroupMemberInfo',
                'fixedGoodsDiscount',
                'goodsDiscountGroup',
                'goodsDiscountGroupMemberInfo',
                'exceptBenefit',
                'exceptBenefitGroup',
                'exceptBenefitGroupInfo',
                'fixedSales',
                'fixedOrderCnt',
                'goodsBenefitSetFl',
                'benefitUseType',
                'newGoodsRegFl',
                'newGoodsDate',
                'newGoodsDateFl',
                'periodDiscountStart',
                'periodDiscountEnd',
                'regDt',
                'modDt'
            ];
            $arrInclude['image'] = [
                'imageSize',
                'imageName',
            ];
            
           $arrFieldGoods = DBTableField::setTableField('tableGoods', $arrInclude['goods'], null, 'g');
           $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, $arrExclude['option'], 'go');
           $arrFieldImage = DBTableField::setTableField('tableGoodsImage', $arrInclude['image'], null, 'gi');
           unset($arrExclude);
           
           if ($tmpMemNo)
               $memNo = $tmpMemNo;
           else
               $memNo = Session::get("member.memNo");
           
           $sql = "SELECT c.*,  
                        " . implode(', ', $arrFieldGoods) . ",
                        " . implode(', ', $arrFieldOption) . ",
                        " . implode(', ', $arrFieldImage) . "
                        FROM wm_subscription_cart AS c 
                            INNER JOIN " . DB_GOODS . " g ON c.goodsNo = g.goodsNo
                            LEFT JOIN " . DB_GOODS_OPTION . " go ON c.optionSno = go.sno AND c.goodsNo = go.goodsNo
                            LEFT JOIN " . DB_GOODS_IMAGE . " as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = 'list'
                    WHERE c.memNo='{$memNo}'";
            if ($isTemp) {
                if ($isAdmin) {
                    $sql .= " AND c.isTemp='2'";
                } else {
                    $sql .= " AND c.isTemp='1'";
                }
            }
            else {
                $sql .= " AND c.isTemp='0'";
            }
             if ($idx)  {
                 if (is_array($idx))
                    $sql .= " AND c.idx IN (".implode(",", $idx).")";
                 else
                     $sql .= " AND c.idx='{$idx}'";
             }

            $result = $this->db->query($sql);
            
            //매입처 관련 정보
            if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n"';
                $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
                $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
            }
            
            //상품 가격 노출 관련
            $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl'];
        
             //품절상품 설정
             if(Request::isMobile()) {
                $soldoutDisplay = gd_policy('soldout.mobile');
              } else {
                $soldoutDisplay = gd_policy('soldout.pc');
              }
              
              // 제외 혜택 쿠폰 번호
              $exceptCouponNo = [];
              $goodsKey = [];
              $goods = new Goods();
              //상품 혜택 모듈
              $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
               
              /* 정기배송 모듈 */
              $obj = App::load(\Component\Subscription\Subscription::class);
               while ($data = $this->db->fetch($result)) {
                    $cfg = $obj->setGoods($data['goodsNo'])->setMode("getGoodsCfg")->get();
                     if ($tmpDiscount)
                        $cfg['discount'][0] = $tmpDiscount;

                    if ($cfg['discount'][0] > 0) {
                       $data['goodsDiscountFl'] = 'y';
                       $data['goodsDiscountUnit'] = 'percent';
                       $data['goodsDiscount'] = $cfg['discount'][0];
                       $data['goodsDiscountGroup'] = 'all';
                    }
                    
                    //상품혜택 사용시 해당 변수 재설정
                    $data = $goodsBenefit->goodsDataFrontConvert($data);
                    
                    // stripcslashes 처리
                    // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
                    $aCheckKey = array('optionText');
                    foreach ($data as $k => $v) {
                        if (!in_array($k, $aCheckKey)) {
                            $data[$k] = gd_htmlspecialchars_stripslashes($v);
                        }
                    }
                    
                    // 전체상품 수량
                    $this->cartGoodsCnt += $data['goodsCnt'];
                    // 쿠폰사용이면
                    if (!empty($data['memberCouponNo']) && $data['memberCouponNo'] != '') {
                        // 쿠폰 기본설정값을 가져와서 회원등급만 사용설정이면 쿠폰정보를 제거 처리 & changePrice false처리
                        $couponConfig = gd_policy('coupon.config');
                        if ($couponConfig['chooseCouponMemberUseType'] == 'member') {
                            $this->setMemberCouponDelete($data['sno']);
                            $data['memberCouponNo'] = '';
                            $this->changePrice = false;
                        }

                        // 쿠폰 사용정보를 가져와서 쿠폰사용정보가 있으면 쿠폰설정에 따른 결제 방식 제한을 처리해준다
                        $aTempMemberCouponNo = explode(INT_DIVISION, $data['memberCouponNo']);
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        foreach ($aTempMemberCouponNo as $val) {
                            $aTempCouponInfo = $coupon->getMemberCouponInfo($val);
                            if ($aTempCouponInfo['couponUseAblePaymentType'] == 'bank') {
                                $data['payLimitFl'] = 'y';
                                if ($data['payLimit'] == '') {
                                    $data['payLimit'] = 'gb';
                                } else {
                                    $aTempPayLimit = explode(STR_DIVISION, $data['payLimit']);
                                    $bankCheck = 'n';
                                    foreach ($aTempPayLimit as $limitVal) {
                                        if ($limitVal == 'gb') {
                                            $bankCheck = 'y';
                                        }
                                    }
                                    if ($bankCheck == 'n') {
                                        //$data['payLimit'] = STR_DIVISION . 'gb';
                                        $data['payLimit'] = array(false);
                                    }
                                }
                            }
                        }
                    }
                    
                    // 기준몰 상품명 저장 (무조건 기준몰 상품명이 저장되도록)
                    $data['goodsNmStandard'] = $data['goodsNm'];
                    if($mallBySession && $globalData[$data['goodsNo']]) {
                        $data = array_replace_recursive($data, array_filter(array_map('trim',$globalData[$data['goodsNo']])));
                    }
                    
                    // 상품 카테고리 정보
                    $goods = \App::load(\Component\Goods\Goods::class);
                    $data['cateAllCd'] = $goods->getGoodsLinkCategory($data['goodsNo']);
                    
                    //매입처 관련 정보
                    if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === false || (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && !in_array($data['purchaseNo'],array_keys($purchaseData))))  {
                        unset($data['purchaseNo']);
                    }
                    
                    // 상품 삭제 여부에 따른 처리
                    if ($data['delFl'] === 'y') {
                        $_delCartSno[] = $data['sno'];
                        unset($data);
                        continue;
                    } else {
                        unset($data['delFl']);
                    }
                    
                    // 텍스트옵션 상품 정보
                    $goodsOptionText = $goods->getGoodsOptionText($data['goodsNo']);
                    if (empty($data['optionText']) === false && gd_isset($goodsOptionText)) {
                        $optionTextKey = array_keys(json_decode($data['optionText'], true));
                        foreach ($goodsOptionText as $goodsOptionTextInfo) {
                            if (in_array($goodsOptionTextInfo['sno'], $optionTextKey) === true) {
                                $data['optionTextInfo'][$goodsOptionTextInfo['sno']] = [
                                    'optionSno' => $goodsOptionTextInfo['sno'],
                                    'optionName' => $goodsOptionTextInfo['optionName'],
                                    'baseOptionTextPrice' => $goodsOptionTextInfo['addPrice'],
                                ];
                            }
                        }

                    }

                    // 추가 상품 정보
                    $data['addGoodsMustFl'] = $mustFl = json_decode(gd_htmlspecialchars_stripslashes($data['addGoods']),true);
                    if ($data['addGoodsFl'] === 'y' && empty($data['addGoodsNo']) === false) {
                        $data['addGoodsNo'] = json_decode($data['addGoodsNo']);
                        $data['addGoodsCnt'] = json_decode($data['addGoodsCnt']);
                    } else {
                        $data['addGoodsNo'] = '';
                        $data['addGoodsCnt'] = '';
                    }

                    // 추가 상품 필수 여부
                    if ($data['addGoodsFl'] === 'y' && empty($data['addGoods']) === false) {
                        foreach ($mustFl as $k => $v) {
                            if ($v['mustFl'] == 'y') {
                                if (is_array($data['addGoodsNo']) === false) {
                                    $data['addGoodsSelectedFl'] = 'n';
                                    break;
                                } else {
                                    $addGoodsResult = array_intersect($v['addGoods'], $data['addGoodsNo']);
                                    if (empty($addGoodsResult) === true) {
                                        $data['addGoodsSelectedFl'] = 'n';
                                        break;
                                    }
                                }
                            }
                        }
                        unset($mustFl);
                    }

                    // 텍스트 옵션 정보 (sno, value)
                    $data['optionTextSno'] = [];
                    $data['optionTextStr'] = [];
                    if ($data['optionTextFl'] === 'y' && empty($data['optionText']) === false) {
                        $arrText = json_decode($data['optionText']);
                        foreach ($arrText as $key => $val) {
                            $data['optionTextSno'][] = $key;
                            $data['optionTextStr'][$key] = $val;
                            unset($tmp);
                        }
                    }
                    unset($data['optionText']);

                    // 텍스트옵션 필수 사용 여부
                    if ($data['optionTextFl'] === 'y') {
                        if (gd_isset($goodsOptionText)) {
                            foreach ($goodsOptionText as $k => $v) {
                                if ($v['mustFl'] == 'y' && !in_array($v['sno'], $data['optionTextSno'])) {
                                    $data['optionTextEnteredFl'] = 'n';
                                }
                            }
                        }
                    }
                    unset($optionText);

                    // 상품 구매 가능 여부
                    $data = $this->checkOrderPossible($data);
          
                    //구매불가 대체 문구 관련
                    if($data['goodsPermissionPriceStringFl'] =='y' && $data['goodsPermission'] !='all' && (($data['goodsPermission'] =='member'  && $this->isLogin === false) || ($data['goodsPermission'] =='group'  && !in_array($this->members['groupSno'],explode(INT_DIVISION,$data['goodsPermissionGroup']))))) {
                        $data['goodsPriceString'] = $data['goodsPermissionPriceString'];
                    }

                    //품절일경우 가격대체 문구 설정
                    if (($data['soldOutFl'] === 'y' || ($data['soldOutFl'] === 'n' && $data['stockFl'] === 'y' && ($data['totalStock'] <= 0 || $data['totalStock'] < $data['goodsCnt']))) && $soldoutDisplay['soldout_price'] !='price'){
                        if($soldoutDisplay['soldout_price'] =='text' ) {
                            $data['goodsPriceString'] = $soldoutDisplay['soldout_price_text'];
                        } else if($soldoutDisplay['soldout_price'] =='custom' ) {
                            $data['goodsPriceString'] = "<img src='".$soldoutDisplay['soldout_price_img']."'>";
                        }
                    }

                    $data['goodsPriceDisplayFl'] = 'y';
                    if (empty($data['goodsPriceString']) === false && $goodsPriceDisplayFl =='n') {
                        $data['goodsPriceDisplayFl'] = 'n';
                    }

                    // 정책설정에서 품절상품 보관설정의 보관상품 품절시 자동삭제로 설정한 경우
                    if ($this->cartPolicy['soldOutFl'] == 'n' && $data['orderPossibleCode'] == self::POSSIBLE_SOLD_OUT) {
                        $_delCartSno[] = $data['sno'];
                        unset($data);
                        continue;
                    }

                    // 상품결제 수단에 따른 주문페이지 결제수단 표기용 데이터
                    if ($data['payLimitFl'] == 'y' && gd_isset($data['payLimit'])) {
                        $payLimit = explode(STR_DIVISION, $data['payLimit']);
                        $data['payLimit'] = $payLimit;

                        if (is_array($payLimit) && $this->payLimit) {
                            $this->payLimit = array_intersect($this->payLimit, $payLimit);
                            if (empty($this->payLimit) === true) {
                                $this->payLimit = ['false'];
                            }
                        } else {
                            $this->payLimit = $payLimit;
                        }
                    }

                    // 비회원시 담은 상품과 회원로그인후 담은 상품이 중복으로 있는경우 재고 체크
                    $data['duplicationGoods'] = 'n';
                    if (isset($tmpStock[$data['goodsNo']][$data['optionSno']]) === false) {
                        $tmpStock[$data['goodsNo']][$data['optionSno']] = $data['goodsCnt'];
                    } else {
                        $data['duplicationGoods'] = 'y';
                        $chkStock = $tmpStock[$data['goodsNo']][$data['optionSno']] + $data['goodsCnt'];
                        if ($data['stockFl'] == 'y' && $data['stockCnt'] < $chkStock) {
                            $this->orderPossible = false;
                            $data['stockOver'] = 'y';
                        }
                    }

                    // 상품구분 초기화 (상품인지 추가상품인지?)
                    $data['goodsType'] = 'goods';

                    // 상품 이미지 처리 @todo 상품 사이즈 설정 값을 가지고 와서 이미지 사이즈 변경을 할것

                    // 세로사이즈고정 체크
                    $imageSize = SkinUtils::getGoodsImageSize('list');
                    $imageConf = gd_policy('goods.image');

                    if (Request::isMobile() || $imageConf['imageType'] != 'fixed') {
                        $imageSize['size1'] = '40'; // 기존 사이즈
                        $imageSize['hsize1'] = '';
                    }

                    // 상품 이미지 처리
                    if ($data['onlyAdultFl'] == 'y' && gd_check_adult() === false && $data['onlyAdultImageFl'] =='n') {
                        if (Request::isMobile()) {
                            $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_mobile.png";
                        } else {
                            $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_pc.png";
                        }

                        $data['goodsImage'] = SkinUtils::makeImageTag($data['goodsImageSrc'], $imageSize['size1']);
                    } else {
                        $data['goodsImage'] = gd_html_preview_image($data['imageName'], $data['imagePath'], $data['imageStorage'], $imageSize['size1'], 'goods', $data['goodsNm'], 'class="imgsize-s"', false, false, $imageSize['hsize1']);
                    }



                    unset($data['imageStorage'], $data['imagePath'], $data['imageName'], $data['imagePath']);

                    $data['goodsMileageExcept'] = 'n';
                    $data['couponBenefitExcept'] = 'n';
                    $data['memberBenefitExcept'] = 'n';

                    //타임세일 할인 여부
                    $data['timeSaleFl'] = false;
                    if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true && Request::post()->get('mode') !== 'cartEstimate') {

                        $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                        $timeSaleInfo = $timeSale->getGoodsTimeSale($data['goodsNo']);
                        if ($timeSaleInfo) {
                            $data['timeSaleFl'] = true;
                            if ($timeSaleInfo['mileageFl'] == 'n') {
                                $data['goodsMileageExcept'] = "y";
                            }
                            if ($timeSaleInfo['couponFl'] == 'n') {
                                $data['couponBenefitExcept'] = "y";

                                // 타임세일 상품적용 쿠폰 사용불가 체크
                                if (empty($data['memberCouponNo']) === false) {
                                    $exceptCouponNo[$data['sno']] = $data['memberCouponNo'];
                                }
                            }
                            if ($timeSaleInfo['memberDcFl'] == 'n') {
                                $data['memberBenefitExcept'] = "y";
                            }
                            if ($data['goodsPrice'] > 0) {
                                // 타임세일 할인금액
                                $data['timeSalePrice'] = gd_number_figure((($timeSaleInfo['benefit'] / 100) * $data['goodsPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);

                                $data['goodsPrice'] = gd_number_figure($data['goodsPrice'] - (($timeSaleInfo['benefit'] / 100) * $data['goodsPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);
                            }
                            //상품 옵션가(일체형,분리형) 타임세일 할인율 적용 ( 텍스트 옵션가 / 추가상품가격 제외 )
                            if($data['optionFl'] === 'y'){
                                // 타임세일 할인금액
                                $data['timeSalePrice'] = gd_number_figure($data['timeSalePrice'] + (($timeSaleInfo['benefit'] / 100) * $data['optionPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);

                                $data['optionPrice'] = gd_number_figure($data['optionPrice'] - (($timeSaleInfo['benefit'] / 100) * $data['optionPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);
                            }
                        }
                    }

                    // 혜택제외 체크 (쿠폰)
                    $exceptBenefit = explode(STR_DIVISION, $data['exceptBenefit']);
                    $exceptBenefitGroupInfo = explode(INT_DIVISION, $data['exceptBenefitGroupInfo']);
                    if (in_array('coupon', $exceptBenefit) === true && ($data['exceptBenefitGroup'] == 'all' || ($data['exceptBenefitGroup'] == 'group' && in_array($this->_memInfo['groupSno'], $exceptBenefitGroupInfo) === true))) {
                        if (empty($data['memberCouponNo']) === false) {
                            $exceptCouponNo[$data['sno']] = $data['memberCouponNo'];
                        }
                        $data['couponBenefitExcept'] = "y";
                    }

                    //배송방식에 관한 데이터
                    $data['goodsDeliveryMethodFl'] = $data['deliveryMethodFl'];
                    $data['goodsDeliveryMethodFlText'] = gd_get_delivery_method_display($data['deliveryMethodFl']);
                    
                    $tmpOptionName = [];
                    for ($optionKey = 1; $optionKey <= 5; $optionKey++) {
                        if (empty($data['optionValue' . $optionKey]) === false) {
                            $tmpOptionName[] = $data['optionValue' . $optionKey];
                        }
                    }
                    $data['optionNm'] = @implode('/', $tmpOptionName);
                    unset($tmpOptionName);

                    if (in_array($data['goodsNo'], $goodsKey) === false) {
                        $goodsKey[] = $data['goodsNo'];
                    }
                    $data['goodsKey'] = array_search($data['goodsNo'], $goodsKey);

                    // 현재 주문 중인 장바구니 SNO
                    $this->cartSno[] = $data['sno'];

                    // 쇼핑 계속하기 주소 처리
                    if ($data['cateCd'] && empty($this->shoppingUrl) === true) {
                        $this->shoppingUrl = $data['cateCd'];
                    }
                    
                    $getData[] = $data;
                    unset($data);
                    
               } // endwhile 
                 
                // 장바구니 상품에 대한 계산된 정보
                $getCart = $this->getCartDataInfo($getData, $postValue);
              
                 // 글로벌 해외배송 조건에 따라서 처리
                if (Globals::get('gGlobal.isFront') || $this->isAdminGlobal === true) {
                    if ($address !== null) {
                        $getCart = $this->getOverseasDeliveryDataInfo($getCart, array_column($getData, 'deliverySno'), $address);
                    }
                } else {
                    // 복수배송지 사용시 배송정보 재설정
                    if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && $postValue['multiShippingFl'] == 'y') || $postValue['isAdminMultiShippingFl'] === 'y') {
                        foreach ($postValue['orderInfoCdData'] as $key => $val) {
                            $tmpGetCart = [];
                            $tmpAllGetKey = [];
                            $tmpDeliverySnos = [];
                            foreach ($val as $tVal) {
                                $tmpScmNo = $this->multiShippingOrderInfo[$tVal]['scmNo'];
                                $tmpDeliverySno = $this->multiShippingOrderInfo[$tVal]['deliverySno'];
                                $tmpGetKey = $this->multiShippingOrderInfo[$tVal]['getKey'];
                                $tmpAllGetKey[] = $tmpGetKey;
                                $tmpDeliverySnos[] = $tmpDeliverySno;

                                $tmpGetCart[$tmpScmNo][$tmpDeliverySno][$tmpGetKey] = $getCart[$tmpScmNo][$tmpDeliverySno][$tmpGetKey];
                            }
                            if ($key > 0) {
                                $multiAddress = str_replace(' ', '', $postValue['receiverAddressAdd'][$key] . $postValue['receiverAddressSubAdd'][$key]);
                            } else {
                                $multiAddress = $address;
                            }

                            $tmpGetCart = $this->getDeliveryDataInfo($tmpGetCart, $tmpDeliverySnos, $multiAddress, $postValue['multiShippingFl'], $key);
                            foreach ($tmpGetCart as $sKey => $sVal) {
                                foreach ($sVal as $dKey => $dVal) {
                                    foreach ($dVal as $getKey => $getVal) {
                                        if (empty($tmpGetCart[$sKey][$dKey][$getKey]) === false) {
                                            $getCart[$sKey][$dKey][$getKey] = $tmpGetCart[$sKey][$dKey][$getKey];
                                        }
                                    }

                                }
                            }
                            unset($tmpGetCart);
                        }
                    } else {
                        $getCart = $this->getDeliveryDataInfo($getCart, array_column($getData, 'deliverySno'), $address);
                    }
                }
                
                // 장바구니 SCM 정보
                if (is_array($getCart)) {
                    $scmClass = \App::load(\Component\Scm\Scm::class);
                    $this->cartScmCnt = count($getCart);
                    $this->cartScmInfo = $scmClass->getCartScmInfo(array_keys($getCart));
                }
                
               // 회원 할인 총 금액
               $this->totalSumMemberDcPrice = $this->totalMemberDcPrice + $this->totalMemberOverlapDcPrice;

                // 총 부가세율
                $this->totalVatRate = gd_tax_rate($this->totalGoodsPrice, $this->totalPriceSupply);

                // 비과세 설정에 따른 세금계산서 출력 여부
                if ($this->taxInvoice === true && $this->taxGoodsChk === false) {
                    $this->taxInvoice = false;
                }
                
                // 총 결제 금액 (상품별 금액 + 배송비 - 상품할인 - 회원할인 - 사용마일리지(X) - 상품쿠폰할인 - 주문쿠폰할인(X) : 여기서는 장바구니에 있는 상품에 대해서만 계산하므로 결제 예정금액임)
                // 주문관련 할인금액 및 마일리지/예치금 사용은 setOrderSettleCalculation에서 별도로 계산됨
                $this->totalSettlePrice = $this->totalGoodsPrice + $this->totalDeliveryCharge - $this->totalGoodsDcPrice - $this->totalSumMemberDcPrice - $this->totalCouponGoodsDcPrice;
                if($this->totalSettlePrice < 0 ) $this->totalSettlePrice = 0;

                // 총 적립 마일리지 (상품별 총 상품 마일리지 + 회원 그룹 총 마일리지 + 총 상품 쿠폰 마일리지 + 총 주문 쿠폰 적립 금액 : 여기서는 장바구니에 있는 상품에 대해서만 계산하므로 적립 예정금액임)
                $this->totalMileage = $this->totalGoodsMileage + $this->totalMemberMileage + $this->totalCouponGoodsMileage + $this->totalCouponOrderMileage;
                
                
                unset($getData);
          } // endif 
          
		  
		  
		if(\Request::getRemoteAddress()=="182.216.219.157"){
			//2024.06.21웹앤모바일 장바구상품의 카테고리 및 브랜드 정보시작
			$goods = new \Component\Goods\Goods();
			
			foreach($getCart  as $key => $val){
			
				foreach($val as $key2 => $val2){
				
					foreach($val2 as $key3 => $val3){
						$goodsView = $goods->getGoodsView($val3['goodsNo']);
						
						$getCart[$key][$key2][$key3]['cateNm']=$goodsView['cateNm'];
						
						if(empty($goodsView['brandNm']))
							$goodsView['brandNm']="아델라";
						
						$getCart[$key][$key2][$key3]['brandNm']=$goodsView['brandNm'];
						
						if(empty($val3['optionNm']))
							$variant=$val3['goodsNm'];
						else
							$variant = $val3['optionNm'];
						
						$getCart[$key][$key2][$key3]['variant']=strip_tags($variant);						
						
					}
				}
			}
			//2024.06.21웹앤모바일 장바구상품의 카테고리 및 브랜드 정보종료
		}		  
          return $getCart;
      }
      
      public function getCartInfo($idx = null)
      {
          $info = [];
          if ($idx) {
             if ($tmp = $this->db->fetch("SELECT * FROM wm_subscription_cart WHERE idx='{$idx}'")) {
                 $tmp['optionText'] = json_decode($tmp['optionText'], true);
                 $info = $tmp;
             }
          } // endif 
          
          return $info;
      }
      
      
      
      public function set($idx = null, $goodsCnt = 0)
      {
          $this->idx = $idx;
          if ($goodsCnt)
              $this->goodsCnt = $goodsCnt;
          
          return $this;
      }
      
      public function setMode($mode = null)
      {
          $this->mode = $mode;
          return $this;
      }
      
      public function del()
      {
          if ($this->idx) {
               if ($this->mode == 'coupon_delete') {
                    return $this->db->query("UPDATE wm_subscription_cart SET memberCouponNo='' WHERE idx='".$this->idx."'"); 
                } else {
                    return $this->db->query("DELETE FROM wm_subscription_cart WHERE idx='".$this->idx."'");
                }
          }
      }
      
      public function changeEa()
      {
          if ($this->idx && $this->goodsCnt) {
              $sql = "UPDATE wm_subscription_cart SET goodsCnt='".$this->goodsCnt."' WHERE idx='".$this->idx."'";
              return $this->db->query($sql);
          }
      }
      
      public function cartDelete($idx = null)
      {
            
          if (is_array($idx)) {
              foreach ($idx as $_idx) {
                $this->set($_idx)->del();
              }
          } else {
            $this->set($idx)->del();  
          }
      }
      
     public function setOrderSettlePayCalculation($requestData)
     {
        $this->totalMemberDcPrice = $requestData['totalMemberDcPrice'];
        $this->totalMemberOverlapDcPrice = $requestData['totalMemberOverlapDcPrice'];
        $this->totalSumMemberDcPrice = $this->totalMemberDcPrice + $this->totalMemberOverlapDcPrice;
        
        // 전체 할인금액 초기화 = 총 상품금액 - 총 상품할인 적용된 결제금액
        $this->totalDcPrice = $this->totalGoodsPrice + $this->totalDeliveryCharge - $this->totalSettlePrice;
        $orderPrice['totalOrderDcPrice'] = $this->totalDcPrice;
       
        // 실 상품금액 = 상품금액 + 쿠폰사용금액 (순수 상품 합계금액)
        $orderPrice['totalGoodsPrice'] = $this->totalGoodsPrice;

        // 쿠폰 계산을 위한 실제 할인이 되기전에 적용된 상품판매가
        $orderPrice['totalSumGoodsPrice'] = $this->totalPrice;

        // 배송비 (전체 = 정책배송비 + 지역별배송비)
        $orderPrice['totalDeliveryCharge'] = $this->totalDeliveryCharge;
        $orderPrice['totalGoodsDeliveryPolicyCharge'] = $this->totalGoodsDeliveryPolicyCharge;
        $orderPrice['totalScmGoodsDeliveryCharge'] = $this->totalScmGoodsDeliveryCharge;
        $orderPrice['totalGoodsDeliveryAreaCharge'] = $this->totalGoodsDeliveryAreaPrice;


        // 배송비 착불 금액 넘겨 받기 (collectPrice|wholefreeprice)
        foreach ($this->setDeliveryInfo as $dKey => $dVal) {
            $orderPrice['totalDeliveryCollectPrice'][$dKey] = $dVal['goodsDeliveryCollectPrice'];
            $orderPrice['totalDeliveryWholeFreePrice'][$dKey] = $dVal['goodsDeliveryWholeFreePrice'];
        }

        // 총 상품 할인 금액
        $orderPrice['totalGoodsDcPrice'] = $this->totalGoodsDcPrice;

        // 총 회원 할인 금액
        $orderPrice['totalSumMemberDcPrice'] = $this->totalSumMemberDcPrice;
        $orderPrice['totalMemberDcPrice'] = $this->totalMemberDcPrice;//총 회원할인 금액
        $orderPrice['totalMemberOverlapDcPrice'] = $this->totalMemberOverlapDcPrice;//총 그룹별 회원 중복할인 금액

        // 쿠폰할인액 = 상품쿠폰 + 주문쿠폰 + 배송비쿠폰
        $orderPrice['totalCouponDcPrice'] = ($this->totalCouponGoodsDcPrice + $this->totalCouponOrderDcPrice + $this->totalCouponDeliveryDcPrice);
        $orderPrice['totalCouponGoodsDcPrice'] = $this->totalCouponGoodsDcPrice;
        $orderPrice['totalCouponOrderDcPrice'] = $this->totalCouponOrderDcPrice;
        $orderPrice['totalCouponDeliveryDcPrice'] = $this->totalCouponDeliveryDcPrice;

        // 주문할인금액 안분을 위한 순수상품금액 = 상품금액(옵션/텍스트옵션가 포함) + 추가상품금액 - 상품할인 - 회원할인 - 상품쿠폰할인
        $orderPrice['settleTotalGoodsPrice'] = $this->totalGoodsPrice - $this->totalGoodsDcPrice - $this->totalSumMemberDcPrice - $this->totalCouponGoodsDcPrice;

        // 배송비할인금액 안분을 위한 순수배송비금액 = 정책배송비 + 지역배송비 - 배송비 할인쿠폰 - 회원 배송비 무료
        $orderPrice['settleTotalDeliveryCharge'] = $this->totalDeliveryCharge - $this->totalCouponDeliveryDcPrice - $this->totalDeliveryFreeCharge;

        // 주문할인금액 안분을 위한 순수상품금액 + 실 배송비 - 배송비 할인쿠폰
        $orderPrice['settleTotalGoodsPriceWithDelivery'] = $orderPrice['settleTotalGoodsPrice'] + $orderPrice['settleTotalDeliveryCharge'];// 배송비 포함


        $orderPrice['totalGoodsMileage'] = $this->totalGoodsMileage;// 총 상품 적립 마일리지
        $orderPrice['totalMemberMileage'] = $this->totalMemberMileage;// 총 회원 적립 마일리지
        $orderPrice['totalCouponGoodsMileage'] = $this->totalCouponGoodsMileage;// 총 상품쿠폰 적립 마일리지
        $orderPrice['totalCouponOrderMileage'] = $this->totalCouponOrderMileage;// 총 주문쿠폰 적립 마일리지
        $orderPrice['totalMileage'] = $this->totalMileage;// 총 적립 마일리지 = 총 상품 적립 마일리지 + 총 회원 적립 마일리지 + 총 쿠폰 적립 마일리지
 

        // 총 주문할인 + 상품 할인 금액
        $orderPrice['totalDcPrice'] = $this->totalDcPrice;

        // 총 주문할인 금액 (복합과세용 금액 산출을 위해 배송비는 제외시킴)
        $orderPrice['totalOrderDcPrice'] = $this->totalCouponOrderDcPrice + $this->totalUseMileage + $this->totalUseDeposit;

        // 마일리지 지급예외 정책 저장
        $orderPrice['mileageGiveExclude'] = $this->mileageGiveExclude;
    
        $this->totalSettlePrice = $this->totalGoodsPrice + $this->totalDeliveryCharge - $this->totalGoodsDcPrice - $this->totalSumMemberDcPrice - $this->totalCouponGoodsDcPrice;
    
        // 마일리지/예치금/쿠폰 사용에 따른 실결제 금액 반영
        $orderPrice['settlePrice'] = $this->totalSettlePrice;
        
        // 주문하기에서 요청된 실 결제금액
        $requestSettlePrice = str_replace(',', '', $requestData['settlePrice']);

        return $orderPrice;
    }
 }