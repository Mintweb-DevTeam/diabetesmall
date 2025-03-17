<?php
namespace Component\Cossia;
use Globals;
use Request;
use Session;
use Framework\Security\Digester;
use Framework\Utility\GodoUtils;
class Cossia {
	private $db, $total_rows, $Page, $showRow, $pageBlock, $nowBlock, $b_start_page, $b_end_page, $total_page, $total_block, $LimtNo, $SNo, $page, $urlTail, $basicNut;
	public function __construct(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
	}
	public function getCharsang()
	{
		$is_charsang = false;
		$memNo = Session::get('member.memNo') ? Session::get('member.memNo') : 0;
		$sql = 'SELECT COUNT(*) AS `cnt` FROM `co_mandate` WHERE `memNo` = '.$memNo;
		$cnt = $this->db->query_fetch($sql, true);
		if( $cnt[0]['cnt'] ) $is_charsang = true;
		return $is_charsang;
	}
	public function getListAbboteOrder($param, $type='')
	{
		if( $param['sDate'] || $param['eDate'] ){
			$date = $type ? 'modDt' : 'regDt';
			if( $param['sDate'] ) $where[] = ' DATE(`o`.`'.$date.'`) >= \''.$param['sDate'].'\' ';
			if( $param['eDate'] ) $where[] = ' DATE(`o`.`'.$date.'`) <= \''.$param['eDate'].'\' ';
		}
		if( isset( $param['status'] ) && $param['status'] ){
			$where[] = ' `o`.`orderStatus` LIKE \''.$param['status'].'%\' ';
		}
		$select = [
			'`o`.`orderNo`',
			'`o`.`regDt`',
			'`o`.`modDt`',
			'`m`.`abbott_sno`',
			'`m`.`memNo`',
			'`g`.`sno` AS `goods_order_no`',
			'`g`.`goodsCnt`',
			'`g`.`orderStatus`',
		];
		$status = [
            'o' => __('주문'),
            'p' => __('입금'),
            'g' => __('상품'),
            'd' => __('배송'),
            's' => __('확정'),
            'c' => __('취소'),
            'f' => __('실패'),
            'b' => __('반품'),
            'e' => __('교환'),
            'z' => __('교환추가'),
            'r' => __('환불'),
        ];
		$sql = 'SELECT '.implode(',', $select).' FROM `es_orderGoods` AS `g` LEFT JOIN `es_order` AS `o` ON `o`.`orderNo` = `g`.`orderNo` LEFT JOIN `es_member` AS `m` ON `m`.`memNo` = `o`.`memNo` WHERE '.implode(' AND ', $where);
		$result = $this->db->query_fetch($sql, true);
		$data = [];
		if($result)
			foreach($result as $key => $val){
				$data[$key] = $val;
				$data[$key]['orderStatus'] = $status[ substr( $val['orderStatus'], 0, 1 ) ];
				$data[$key]['Status'] = $val['orderStatus'];

				//2021.12.03민트웹 추가시작
				//2022.05.30민트웹 추가


				//사은품 존재 여부체크시작
				$giftSQL="select count(orderNo) as cnt from ".DB_ORDER_GIFT." where orderNo=?";
				$giftROW=$this->db->query_fetch($giftSQL,['i',$val['orderNo']],false);

				if($giftROW['cnt']>0)
					$data[$key]['gift']="추가증정대상";
				

				//사은품 존재 여부체크종료

				$tmp="";
				$sql="select a.idx,b.period,b.deliveryEa,b.autoExtend from wm_subscription_schedule_list a INNER JOIN wm_subscription_apply b ON a.uid=b.uid where a.orderNo='{$val['orderNo']}'";
				$row = $this->db->fetch($sql);
				if(!empty($row['period'])){
					$period = explode("_",$row['period']);

					$tmp=$period[0]; 

					if($period[1]=="week")
						$tmp.="주";
					if($period[1]=="month")
						$tmp.="달";
				}
				$data[$key]['period']=$tmp;

				$order_cnt=0;
				$sql="select * from wm_subscription_schedule_list where orderNo='{$val['orderNo']}'";
				$krow = $this->db->fetch($sql);
		
				if(!empty($krow['uid'])){
					$sql="select count(idx) as cnt from wm_subscription_schedule_list where uid='{$krow['uid']}'";
					$crow = $this->db->fetch($sql);
					$tcount =$crow['cnt'];

					$sql="select count(idx) as cnt from wm_subscription_schedule_list where uid='{$krow['uid']}' and orderNo<>'' and idx<='{$krow['idx']}'";
					$rows = $this->db->fetch($sql);
					$order_cnt =$rows['cnt'];

					$schedule_count_mod = $order_cnt % $row['deliveryEa'];
					if($schedule_count_mod==0){
						$order_cnt =$row['deliveryEa'];
					}else{
						$order_cnt =$schedule_count_mod;
					}


				}

				$data[$key]['deliveryEa']=$row['deliveryEa'];
				$data[$key]['schedule']=$order_cnt;
				$data[$key]['schedule_tcount']=$tcount;

				
				
				if(!empty($krow['uid'])){
					
					//if($tcount>$row['deliveryEa'])
					//	$data[$key]['autoExtendCount']=$tcount/$row['deliveryEa']-1;
					//else
					//	$data[$key]['autoExtendCount']="";
					
					 $logSQL="select count(idx) as cnt from wm_subscription_extension_num where uid='{$krow['uid']}'";
					 $logRow=$this->db->fetch($logSQL);

					 $data[$key]['autoExtendCount']=$logRow['cnt'];

					if($row['autoExtend']==1)
						$data[$key]['autoExtend']="자동연장";
					else
						$data[$key]['autoExtend']="연장안함";
				}else{
				
				}

				//2021.12.03민트웹 추가종료
				


			}
		return $data;
	}
	
	public function getListAbboteMember($param)
	{
		if( $param['sDate'] || $param['eDate'] ){
			if( $param['sDate'] ) $where[] = ' DATE(`m`.`entryDt`) >= \''.$param['sDate'].'\' ';
			if( $param['eDate'] ) $where[] = ' DATE(`m`.`entryDt`) <= \''.$param['eDate'].'\' ';
		}
		$select = [
			'`m`.`memNo`',
			'`m`.`abbott_sno`',
			'`m`.`memNm`',
			'`m`.`cellPhone`',
			'`m`.`email`',
			'`m`.`pharmacy_code`',
			'`m`.`is_print`',
			'`m`.`entryDt`',
			'`c`.`device`',
			// 명세표출력 넣어야 함
			'`c`.`privateConsignFl`',
			'`c`.`privateOfferFl`',
		];
/*
privateConsignFl[20] 마케팅용 정보수집 동의 Info_collection
privateConsignFl[23] 마케팅/광고 수신 marketing_reception
privateOfferFl[25] 3자 개인정보제공  third_party_privacy
privateOfferFl[26] 3자 민감정보제공  third_party_sensitive
*/
		$sql = 'SELECT '.implode(',', $select).' FROM `es_member` AS `m` JOIN `co_abbottMember` AS `c` WHERE `c`.`sno` = `m`.`abbott_sno` AND '.implode(' AND ', $where);
		// $sql = 'SELECT '.implode(',', $select).' FROM `es_member` AS `m` LEFT JOIN `co_abbottMember` AS `c` ON `c`.`sno` = `m`.`abbott_sno` WHERE '.implode(' AND ', $where);
		$result = $this->db->query_fetch($sql, true);
		$data = [];
		if($result)
			foreach($result as $key => $val){
				foreach( ['memNo','abbott_sno','memNm','cellPhone','email','pharmacy_code','device','is_print','entryDt','privateConsignFl','privateOfferFl'] as $v ){
					switch($v){
						case 'privateConsignFl' :
							$arr = json_decode($val[$v], true);
							$data[$key]['Info_collection'] = $arr[20];
							$data[$key]['marketing_reception'] = $arr[23];
							
						break;
						case 'privateOfferFl' :
							$arr = json_decode($val[$v], true);
							$data[$key]['third_party_privacy'] = $arr[25];
							$data[$key]['third_party_sensitive'] = $arr[26];
						break;
						default:
							$data[$key][$v] = $val[$v];
					}
				}
			}
		return $data;
	}
	
	public function getEduAuth( $orerNo = '' )
	{
		if(!Session::get('member.memNo')){
			$data = ['msg'=>'로그인이 필요합니다.'];
		}else{
			$cnt = 0;
			$sql = 'SELECT `goodsNo` FROM `es_goods` WHERE `coViewOrder` = \'y\'';
			$goodsNo = $this->db->query_fetch($sql, true);
			if( $goodsNo[0]['goodsNo'] ){
				foreach( $goodsNo as $row ){
					$sql_[] = $row['goodsNo'];
				}
				$sql = 'SELECT COUNT(*) AS `cnt` FROM `es_order` AS `o` LEFT JOIN `es_orderGoods` AS `g` ON `g`.`orderNo` = `o`.`orderNo` WHERE `o`.`orderNo` = '.$orerNo.' AND `g`.`goodsNo` IN ('.implode(', ', $sql_).')';
				$cnt = $this->db->query_fetch($sql, true)[0]['cnt'];
			}
			if($cnt != 0) $data = Session::get('member');
			else $data = ['msg'=>'리브레 센서 구매자만 신청 가능합니다.\n제품 구매 후 신청해 주시기 바랍니다.'];
		}
		return $data;
	}
	/*
	* 고도몰 sms 기능을 이용한 문자 보내기
	*/
	public function customSms($cellPhone, $no){
		$text = '요청하신 인증번호는 '.$no.' 입니다.';
		$smsAdmin = new \Component\Sms\SmsAdmin;
        $smsUtil = \App::load(\Component\Sms\SmsUtil::class);
		$param = array(
			'mode' => 'smsSend',
			'sendFl' => 'sms',
			'directReceiverNumbers' => array($cellPhone),
			'password' => $smsUtil->getPassword(),
			'smsSendType' => 'now',
			'receiverType' => 'direct',
			'smsContents' => $text,
		);
		return $smsAdmin->sendSms($param);
	}
	
	public function doubleCheck($param){
		//20230808 휴대폰으로만 중복체크 웹앤모바일$sql = 'SELECT `sno`, `memNm`, `isJoin` FROM `co_abbottMember` WHERE `cellPhone` = \''.$this->getCellPhone($param['cellPhone']).'\' AND  `email` = \''.$param['email'].'\'';
		$sql = "select sno,memNm,isJoin from co_abbottMember where cellPhone='".$this->getCellPhone($param['cellPhone'])."'";

		$result = $this->db->query_fetch($sql, true);
		if( $result[0]['isJoin'] == 'n' ) Session::set('join_view', '2');
		return $result[0];
	}
	
	public function doubleCheck2($param){
		$sql = 'SELECT COUNT(`sno`) AS `cnt` FROM `co_aboottAgree` WHERE `phone` = \''.$this->getCellPhone($param['cellPhone']).'\' AND  `email` = \''.$param['email'].'\'';
		$result = $this->db->query_fetch($sql, true);
		return $result[0];
	}
	public function getCellPhone($cellPhone){
		$cellPhone = preg_replace('/[^0-9]*/s', '', $cellPhone);
		if( strlen($cellPhone) == 11 ){
			$data = substr($cellPhone, 0, 3).'-'.substr($cellPhone, 3, 4).'-'.substr($cellPhone, 7, 4);
		}else if(  strlen($cellPhone) == 10  ){
			$data = substr($cellPhone, 0, 3).'-'.substr($cellPhone, 3, 3).'-'.substr($cellPhone, 6, 4);
		}else{
			$data = false;
		}
		return $data;
	}
	public function abbottRealMemberCheck($param = null, $sno = null){
		$sql = $sno ? 'SELECT * FROM `co_abbottMember` WHERE `sno` = \''.$sno.'\'' : 'SELECT * FROM `co_abbottMember` WHERE `memNm` = \''.$param['memNm'].'\' AND `cellPhone` = \''.$param['cellPhone'].'\' AND `email` = \''.$param['memId'].'\'';
		$result = $this->db->query_fetch($sql);
		if( $result ){
			$privateApprovalOptionFl = json_decode($result[0]['privateApprovalOptionFl'], true);
			$privateOfferFl = json_decode($result[0]['privateOfferFl'], true);
			if( $privateApprovalOptionFl[19] == 'y' &&  $privateApprovalOptionFl[21] == 'y' &&  $privateOfferFl[5] == 'y' &&  $privateOfferFl[22] == 'y' ){
				$data = $result[0];
				$data['code'] = true;
			} else {
				$data = $result[0];
				$data['code'] = 'false';
			}
		}else {
			$data['code'] = false;
		}
		return $data;
	}
	public function getAbbottMember($param){ // Array ( [sno] => 3 [name] => 홍길동 )
		$sql = 'SELECT * FROM `co_abbottMember` WHERE `sno` = \''.$param['sno'].'\' AND `memNm` = \''.$param['name'].'\' LIMIT 1';

		$result = $this->db->query_fetch($sql);
		if($result[0]){
			$data = $result[0];
			$data['privateApprovalOptionFl'] = json_decode($result[0]['privateApprovalOptionFl'], true);
			$data['privateConsignFl'] = json_decode($result[0]['privateConsignFl'], true);
			$data['privateOfferFl'] = json_decode($result[0]['privateOfferFl'], true);
		}
		return $data;
	}
	public function insertCoAbbottMember($param){
		$pharmacy_code = isset($param['pharmacy_code']) ? ', `pharmacy_code` = \''.$param['pharmacy_code'].'\' ' : '';
		$device = ($param['device']) ? ', `device` = \''.$param['device'].'\' ' : ', `device` = \'mo\' ';
		$sql = 'INSERT INTO `co_abbottMember` SET `memNm` = \''.$param['memNm'].'\', `email` = \''.$param['memId'].'\', `cellPhone` = \''.$param['cellPhone'].'\', `privateApprovalOptionFl` = \''.json_encode($param['privateApprovalOptionFl']).'\', `privateOfferFl` = \''.json_encode($param['privateOfferFl']).'\', `privateConsignFl` = \''.json_encode($param['privateConsignFl']).'\''.$pharmacy_code.$device;
		$this->db->query($sql);
		return $this->last_insert_id();
	}
	public function inCoMember($param){	// 회원가입 넣어줌
		foreach($param as $key => $val){
			if( $key == 'privateApprovalOptionFl' ||  $key == 'privateConsignFl' ||  $key == 'privateOfferFl' ){
				$sql_[] = ' `'.$key.'` = \''.json_encode($val).'\' ';
			} else if ( $key == 'memPw' ){

                if(GodoUtils::sha256Fl()) {
                    $sql_[] = ' `'.$key.'` = \''.Digester::digest($val).'\' ';
                } else {
                    $sql_[] = ' `'.$key.'` = \''.\App::getInstance('password')->hash($val).'\' ';
                }

			} else {
				$sql_[] = ' `'.$key.'` = \''.$val.'\' ';
			}
		}
		$mail = new \Component\Cossia\Mail;
		$mail->adela_join( ['memNm'=>$param['memNm'],'email'=>$param['memId'],'cellPhone'=>$param['cellPhone']] );
		
		$sql = 'UPDATE `co_abbottMember` SET `isJoin` = \'y\', joinDt = now() WHERE `sno` = \''.$param['abbott_sno'].'\'';
		$this->db->query($sql);
		$sql = 'INSERT INTO `es_member` SET '.implode(', ', $sql_);
		return $this->db->query($sql);
	}
	
	
	public function abbottCheck($cellPhone){
		$sql = 'SELECT * FROM `co_abbottMember` WHERE `cellPhone` = \''.$cellPhone.'\' LIMIT 1';
		$result = $this->db->query_fetch($sql);
		if( $result[0]['isJoin'] == 'y' ){	// 이미 회원가입을 했음
			$data = $result[0];
			$data['code'] = 'isMem';
		}else if( $result[0]['isJoin'] == 'n' ){	// 정보는 있고 회원가입은 하지 않았음
			$data = $result[0];
			$data['code'] = 'isOk';
		}else{	// 정보 자체가 없음
			$data['code'] = 'isNot';
		}
		return $data;
	}
	
	public function last_insert_id(){
		$result = $this->db->query_fetch('SELECT LAST_INSERT_ID()', true);
		return $result[0]['LAST_INSERT_ID()'];
	}
	
	public function pharmacy_perce_list($param){
		if( $param['sDate'] || $param['eDate'] ){
			$sql_[] = ' `m`.`pharmacy_code` IS NOT NULL ';
			$sql_[] = ' `m`.`pharmacy_code` != \'\' ';
			if ( $param['sDate'] ) $sql_[] = ' DATE(`m`.`regDt`) >= \''.$param['sDate'].'\' ';
			if ( $param['eDate'] ) $sql_[] = ' DATE(`m`.`regDt`) <= \''.$param['eDate'].'\' ';
			$sql = 'SELECT `m`.`pharmacy_code` FROM `co_abbottMember` AS `m` WHERE '.implode( ' AND ', $sql_ ).' GROUP BY  `m`.`pharmacy_code` HAVING COUNT( `m`.`pharmacy_code` ) >= 1';
			$result = $this->db->query_fetch($sql, true);
			if($result)
				foreach($result as $row){
					$sql = 'SELECT `p`.*, COUNT( `m`.`sno` ) AS `cnt`, SUM( `m`.`coKakaoChannel` ) AS `kakao` FROM `co_pharmacy` AS `p` LEFT JOIN `co_abbottMember` AS `m` ON `m`.`pharmacy_code` = `p`.`code` AND '.implode( ' AND ', $sql_ ).' WHERE `p`.`code` = \''.$row['pharmacy_code'].'\' ';
					$res_ = $this->db->query_fetch($sql, true);
					if( isset($res_[0]['code']) ) $data[] = $res_[0];
				}
			return array('list'=>$data, 'total'=>count($data));
		}
	}
	
	public function pharmacy_list($param){
		$sql_ = 'FROM `co_pharmacy` ';
		$sql_ .= ($param['search']) ? ' WHERE `'.$param['filde'].'` LIKE \'%'.$param['search'].'%\' ORDER BY `regDt` DESC ' : ' ORDER BY `regDt` DESC ';
		$page = $this->beforPageing($param, $sql_);
		$sql = 'SELECT * '.$sql_.' LIMIT '.$this->LimtNo.', '.$this->showRow;
		$data = $this->db->query_fetch($sql, true);
		$max_ = count($data);
		if($max_ == 0) $data = null;
		else{
			for ($i=0, $this->SNo; $i<$max_; $i++, $this->SNo--){
				$data[$i]['No'] = $this->SNo;
			}
		}
		return array( 'page' => $page, 'data'=>$data, 'total'=>$this->total_rows );
	}
	public function mandate_list($param){
		$wheres = ($param['search']) ? 'WHERE `m`.`'.$param['filde'].'` LIKE \'%'.$param['search'].'%\'' : '';
		$sql_ = ' FROM `co_mandate` AS `c` LEFT JOIN `es_member` AS `m` ON `m`.`memNo` = `c`.`memNo` '.$wheres.' ORDER BY `c`.`regDt` DESC';
		$page = $this->beforPageing($param, $sql_);
		$sql = 'SELECT `c`.*, `m`.`memNm`, `m`.`memId`, `m`.`cellPhone` '.$sql_.' LIMIT '.$this->LimtNo.', '.$this->showRow;
		$data = $this->db->query_fetch($sql, true);
		$max_ = count($data);
		if($max_ == 0) $data = null;
		else{
			for ($i=0, $this->SNo; $i<$max_; $i++, $this->SNo--){
				if( !$data[$i]['memNm'] ) $data[$i] = ['memNm'=>'탈퇴회원', 'cellPhone'=>'탈퇴회원', 'memId'=>'탈퇴회원', 'relation'=>$data[$i]['relation'],'regDt'=>$data[$i]['regDt']];
				$data[$i]['No'] = $this->SNo;
			}
		}
		return array( 'page' => $page, 'data'=>$data, 'total'=>$this->total_rows );
	}
	
	public function abbott_list($param){
		if($param['search']) $wheres[] =  '`a`.`'.$param['filde'].'` LIKE \'%'.$param['search'].'%\'';
		if($param['agree'] !== 'all'){
			if( is_array($param['privateConsignFl']) ){
				foreach( $param['privateConsignFl'] as $key => $val ){
					$wheres[] =  ' JSON_EXTRACT( `a`.`privateConsignFl`, \'$."'.$key.'"\' ) = \''.$val.'\' ';
				}
			}
			if( is_array($param['privateOfferFl']) ){
				foreach( $param['privateOfferFl'] as $key => $val ){
					$wheres[] =  ' JSON_EXTRACT( `a`.`privateOfferFl`, \'$."'.$key.'"\' ) = \''.$val.'\' ';
				}
			}
		}
		
		$wheres[] = ' `device` != \'dr\' ';
		
		$sql_ = 'FROM `co_abbottMember` AS `a` LEFT JOIN `es_member` AS `m` ON `m`.`abbott_sno` = `a`.`sno` ';
		
		$sql_ .= ( count($wheres) !== 0 ) ? ' WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`regDt` DESC ' : ' ORDER BY `a`.`regDt` DESC ';
		
		$page = $this->beforPageing($param, $sql_);
		$sql = 'SELECT `a`.*, `m`.`memNo` AS `memNo` '.$sql_.' LIMIT '.$this->LimtNo.', '.$this->showRow;
		$data = $this->db->query_fetch($sql, true);
		
		$max_ = count($data);
		if($max_ == 0) $data = null;
		else{
			for ($i=0, $this->SNo; $i<$max_; $i++, $this->SNo--){
				$data[$i]['No'] = $this->SNo;
				$data[$i]['privateApprovalOptionFl'] = json_decode($data[$i]['privateApprovalOptionFl'], true);
				$data[$i]['privateOfferFl'] = json_decode($data[$i]['privateOfferFl'], true);
				$data[$i]['privateConsignFl'] = json_decode($data[$i]['privateConsignFl'], true);
				$sql = 'SELECT `name` FROM `co_pharmacy` WHERE `code` = \''.$data[$i]['pharmacy_code'].'\'';
				$name = $this->db->query_fetch($sql, true);
				$data[$i]['pharmacy_name'] = $name[0]['name'];
			}
		}
		return array( 'page' => $page, 'data'=>$data, 'total'=>$this->total_rows );
	}
	
	
	public function drdiary_list($param){
		if($param['search']) $wheres[] =  '`a`.`'.$param['filde'].'` LIKE \'%'.$param['search'].'%\'';
		if($param['agree'] !== 'all'){
			if( is_array($param['privateConsignFl']) ){
				foreach( $param['privateConsignFl'] as $key => $val ){
					$wheres[] =  ' JSON_EXTRACT( `a`.`privateConsignFl`, \'$."'.$key.'"\' ) = \''.$val.'\' ';
				}
			}
			if( is_array($param['privateOfferFl']) ){
				foreach( $param['privateOfferFl'] as $key => $val ){
					$wheres[] =  ' JSON_EXTRACT( `a`.`privateOfferFl`, \'$."'.$key.'"\' ) = \''.$val.'\' ';
				}
			}
		}
		
		$wheres[] = ' `device` = \'dr\' ';
		
		$sql_ = 'FROM `co_abbottMember` AS `a` LEFT JOIN `es_member` AS `m` ON `m`.`abbott_sno` = `a`.`sno` ';
		
		$sql_ .= ( count($wheres) !== 0 ) ? ' WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`regDt` DESC ' : ' ORDER BY `a`.`regDt` DESC ';
		
		$page = $this->beforPageing($param, $sql_);
		$sql = 'SELECT `a`.*, `m`.`memNo` AS `memNo` '.$sql_.' LIMIT '.$this->LimtNo.', '.$this->showRow;
		$data = $this->db->query_fetch($sql, true);
		$max_ = count($data);
		if($max_ == 0) $data = null;
		else{
			for ($i=0, $this->SNo; $i<$max_; $i++, $this->SNo--){
				$data[$i]['No'] = $this->SNo;
				$data[$i]['privateApprovalOptionFl'] = json_decode($data[$i]['privateApprovalOptionFl'], true);
				$data[$i]['privateOfferFl'] = json_decode($data[$i]['privateOfferFl'], true);
				$data[$i]['privateConsignFl'] = json_decode($data[$i]['privateConsignFl'], true);
				$sql = 'SELECT `name` FROM `co_pharmacy` WHERE `code` = \''.$data[$i]['pharmacy_code'].'\'';
				$name = $this->db->query_fetch($sql, true);
				$data[$i]['pharmacy_name'] = $name[0]['name'];
			}
		}
		return array( 'page' => $page, 'data'=>$data, 'total'=>$this->total_rows );
	}
	
	
	
	
	
	
	public function pagging(){
		$this->pageBlock = 10;
		$this->nowBlock = ceil($this->Page/$this->pageBlock);
		$this->b_start_page = ( ($this->nowBlock - 1) * $this->pageBlock ) + 1;
		$this->b_end_page = $this->b_start_page + $this->pageBlock - 1;
		$this->total_page =  ceil($this->total_rows/$this->showRow);
		$this->total_block = ceil($this->total_page/$this->pageBlock);
		$this->LimtNo = ($this->Page - 1) * $this->showRow;
		$this->SNo = $this->total_rows - $this->showRow * ($this->Page -1) ;
		if($this->b_end_page > $this->total_page)		$this->b_end_page = $this->total_page;
		$pagehtml = '<nav><ul class="pagination pagination-sm">';
		if($this->nowBlock > 1) $pagehtml .= '<li><a href="'.$this->aurl.($this->b_start_page-1).$this->urlTail.'">&lsaquo;</a></li>';
		for ($j = $this->b_start_page; $j <=$this->b_end_page; $j++){
			$class = '';
			$link = '<a href="'.$this->aurl.$j.$this->urlTail.'">'.$j.'</a>';
			if ($j == $this->Page) {
				$class = ' class="active" ';
				$link = '<span>'.$j.'</span>';
			} 
			$pagehtml .= '<li'.$class.'>'.$link.'</li>';
		}
		if($this->nowBlock < $this->total_block) $pagehtml .= '<li><a href="'.$this->aurl.($this->nowBlock*10+1).$this->urlTail.'">&rsaquo;</a></li>';
		$pagehtml .= '</ul></nav>';
		return $pagehtml;
	}
	public function beforPageing($param, $sql_){
		$count = $this->db->query_fetch('SELECT count(*) as cnt '.$sql_, true);	
		$this->total_rows = $count[0]['cnt'];
		$this->Page = $param['page'];
		$this->showRow = $param['pageNum'];
		$this->aurl = $param['aurl'].'?page=';
		foreach($param as $k => $v) {
			if ( is_array($v) ) foreach( $v as $row ) $this->urlTail .= '&'.$k.'[]='.$row;
			else if( $k != 'page' && $k != 'aurl') $this->urlTail .= '&'.$k.'='.$v;
		}
		return $this->pagging();
	}
	
	public function getAgreementItem($memNo)
	{
		$sql = 'SELECT `privateApprovalOptionFl`, `privateOfferFl`, `privateConsignFl` FROM `es_member` WHERE `memNo` = '.$memNo;
		$data = $this->db->query_fetch($sql, true)[0];
		$bool = false;
		$arr = [4, 5, 6, 7, 19, 21, 22, 29];
		/*
		foreach($data as $k => $row){
			foreach(json_decode($row, true) as $key_ => $row_){
				if( in_array( $key_, $arr ) && $row_ != 'y' ) $bool = true;
			}
		}
		*/
		return $bool;
	}
	
	public function getAgreementItemAbbott($param)
	{
		$param['cellPhone'] = $this->getCellPhone($param['cellPhone']);
		$sql = 'SELECT `privateApprovalOptionFl`, `privateOfferFl`, `privateConsignFl` FROM `co_abbottMember` WHERE `memNm` = \''.$param['memNm'].'\' AND `cellPhone` = \''.$param['cellPhone'].'\' AND `email` = \''.$param['memId'].'\'';
		$data = $this->db->query_fetch($sql, true)[0];
		$bool = false;
		$arr = [4, 5, 6, 7, 19, 21, 22, 29];
		/*
		foreach($data as $k => $row){
			foreach(json_decode($row, true) as $key_ => $row_){
				if( in_array( $key_, $arr ) && $row_ != 'y' ) $bool = true;
			}
		}
		*/
		return $bool;
	}
	
	/* HCP 리브레케어 멤버쉽 api cossia 2022-02-04 */
	public function getListHcp( $param, $sql_ = '' )
	{
		if( isset($param['sDate']) && $param['sDate'] )
			$sql_ .= ' AND DATE(`regDt`) >= \''.$param['sDate'].'\' ';
		if( isset($param['eDate']) && $param['eDate'] )
			$sql_ .= ' AND DATE(`regDt`) <= \''.$param['eDate'].'\' ';
		if( isset($param['cellPhone']) && $param['cellPhone'] )
			$sql_ .= ' AND `phone` = \''.$this->getCellPhone($param['cellPhone']).'\' ';
		$sql = 'SELECT * FROM `co_aboottAgree` WHERE 1 '.$sql_;
		return $this->db->query_fetch($sql, true);
	}
	
	/* 리브레 멤버쉽 api cossia 2022-01-18 */
	public function getListAbboteMembership( $param, $sql_ = '' )
	{
		if( isset($param['sDate']) && $param['sDate'] )
			$sql_ .= ' AND DATE(`regDt`) >= \''.$param['sDate'].'\' ';
		if( isset($param['eDate']) && $param['eDate'] )
			$sql_ .= ' AND DATE(`regDt`) <= \''.$param['eDate'].'\' ';
		
		//2024.03.18웹앤모바일 수정일 검색 추가
		if( isset($param['modDt'])){
			$sql_ .= ' AND DATE(modDt) = \''.$param['modDt'].'\' ';
		}
		
		if( isset($param['cellPhone']) && $param['cellPhone'] )
			$sql_ .= ' AND `cellPhone` = \''.$this->getCellPhone($param['cellPhone']).'\' ';
		
		$sql = 'SELECT * FROM `co_abbottMember` WHERE 1 '.$sql_;
		$data = $this->db->query_fetch($sql, true);
		$result = [];
		if( $data ){
			foreach( $data as $row ){
				$consign = json_decode($row['privateConsignFl'], true);
				$offer = json_decode($row['privateOfferFl'], true);
				
				if( $row['device'] == 'dr' ){
					$row['pharmacy_code'] = 'DDM';
					$row['device'] = 'qr';
				}
				
				//2024.03.18웹앤모바일 추가'modDt'=>$row['modDt'],
				$result[] = [
					'sno' => $row['sno'],
					'name' => $row['memNm'],
					'phone' => $row['cellPhone'],
					'email' => $row['email'],
					'mInfoFl' => strtoupper($consign[20]),
					'adFl' => strtoupper($consign[23]),
					'priFl' => strtoupper($offer[25]),
					'sensFl' => strtoupper($offer[26]),
					'pharmacy' => $row['pharmacy_code'],
					'connect' => strtoupper($row['device']),
					'regDt' => $row['regDt'],
					'modDt'=>$row['modDt'],
				];
			}
		}
		return $result;
	}
	
	
	/*
	* 2022-07-23 이벤트 상품을 구매 했는지?
	* return bool
	* p1: 결제완료, g1: 상품중비중 , d1: 배송중,  d2: 배송완료, s1: 구매확정
	*/
	public function isBuyEventGoods( int $goodsNo )
	{
		$sql = 'SELECT COUNT(`g`.`sno`) AS `cnt` FROM `es_order` AS `o` RIGHT JOIN `es_orderGoods` AS `g` ON `o`.`orderNo` = `g`.`orderNo` WHERE `o`.`memNo` = '.Session::get('member.memNo').' AND `g`.`orderStatus` IN (\'p1\', \'g1\', \'d1\', \'d2\', \'s1\') AND `g`.`goodsNo` = '.$goodsNo;
		$cnt = $this->db->query_fetch($sql, true)[0]['cnt'];
		return $cnt ? true : false;
	}


	public function isJoinData($sno,$memNo){
        $sql = 'UPDATE `co_abbottMember` SET `isJoin` = \'y\', joinDt = now() WHERE `sno` = \''.$sno.'\'';
        $this->db->query($sql);

        $sql = 'UPDATE `es_member` SET `abbott_sno` = \''.$sno.'\' WHERE `memNo` = \''.$memNo.'\'';
        $this->db->query($sql);
    }
}