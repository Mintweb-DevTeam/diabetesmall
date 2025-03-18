<?php
namespace Component\Excel;
class ExcelDataConvert extends \Bundle\Component\Excel\ExcelDataConvert
{
	public function __construct(){
		parent::__construct();
		if (!is_object($this->db))
			$this->db = \App::load('DB');
        
	}
	public function abbotMember(){
		$head = ['sno', '이름', '연락처', '이메일', '마케팅용 정보수집 동의', '마케팅/광고 수신', '3자 개인정보제공', '3자 민감정보제공', '약국코드', '접속경로', '가입일시','업데이트일시','카카오계정 여부'];
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		foreach ($head as $row) {
			$this->excelHeader .= '<td class="title">' . $row . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		$sql = 'SELECT * FROM `co_abbottMember` ORDER BY `sno` DESC';
		$body = $this->db->query_fetch($sql, true);
		if($body)
			foreach ($body as $row) {
				if( $row['device'] == 'dr' ){
					$row['pharmacy_code'] = 'DDM';
					$row['device'] = 'qr';
				}
				$this->excelBody[] = '<tr>' . chr(10);
				$this->excelBody[] = '<td>'.$row['sno'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['memNm'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['cellPhone'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['email'].'</td>' . chr(10);
				// $privateApprovalOptionFl = json_decode($row['privateApprovalOptionFl'], true);
				$privateConsignFl = json_decode($row['privateConsignFl'], true);
				$privateOfferFl = json_decode($row['privateOfferFl'], true);
				$this->excelBody[] = '<td>'.strtoupper($privateConsignFl[20]).'</td>' . chr(10);
				$this->excelBody[] = '<td>'.strtoupper($privateConsignFl[23]).'</td>' . chr(10);
				$this->excelBody[] = '<td>'.strtoupper($privateOfferFl[25]).'</td>' . chr(10);
				$this->excelBody[] = '<td>'.strtoupper($privateOfferFl[26]).'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['pharmacy_code'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.strtoupper($row['device']).'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['regDt'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['modDt'].'</td>' . chr(10);//2024.02.08웹앤모바일추가
                $this->excelBody[] = '<td>'.$row['kakaoFl'].'</td>' . chr(10);//2025.03.18웹앤모바일추가
				$this->excelBody[] = '</tr>' . chr(10);
			}
		$this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	public function coExcel($dbname){
		$sql = 'SHOW FULL COLUMNS FROM `co_'.$dbname.'`';
		$head = $this->db->query_fetch($sql, true);
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		foreach ($head as $row) {
			$name = $row['Comment'] ? $row['Comment'] : $row['Field'];
			$this->excelHeader .= '<td class="title">' . $name . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		$sql = 'SELECT * FROM `co_'.$dbname.'`';
		$body = $this->db->query_fetch($sql, true);
		foreach ($body as $row) {
			$this->excelBody[] = '<tr>' . chr(10);
			foreach($row as $key => $row_) {
				if($dbname == 'feed' && $key == 'mates' ){
					$exp = explode(',', $row_);
					$val = null;
					foreach($exp as $v){
						$exp_ = explode(':', $v);
						$val[] = (string)$exp_[0].':'.(string)($exp_[1]+1);
					}
					$this->excelBody[] = '<td>'.implode(',', $val).'</td>' . chr(10);
				}else{
					$this->excelBody[] = '<td>'.$row_.'</td>' . chr(10);
				}
			}
			$this->excelBody[] = '</tr>' . chr(10);
		}
        $this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	/*
	* 약국 실적 다운로드
	*/
	public function coExcel_2($param){
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		$head = ['코드','상호명','전화번호','우편번호','주소','상세주소','카카오','실적'];
		foreach ($head as $row) {
			$this->excelHeader .= '<td class="title" style="text-align:center">' . $row . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->pharmacy_perce_list($param);
		foreach ($data['list'] as $row) {
			$this->excelBody[] = '<tr>' . chr(10);
			$this->excelBody[] = '<td>'.$row['code'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['name'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['phone'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['post'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['address1'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['address2'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['kakao'].'</td>' . chr(10);
			$this->excelBody[] = '<td>'.$row['cnt'].'</td>' . chr(10);
			$this->excelBody[] = '</tr>' . chr(10);
		}
        $this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	/*
	* 약국 실적 다운로드 디테일
	*/
	public function coExcel_3($param){
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		$head = ['이름','전화번호','이메일','가입일'];
		foreach ($head as $row) {
			$this->excelHeader .= '<td class="title" style="text-align:center">' . $row . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		
		$sql = 'SELECT `memNm`, `cellPhone`, `email`, `regDt` FROM `co_abbottMember` WHERE `pharmacy_code` = \''.$param['code'].'\' AND DATE(`regDt`) >= \''.$param['sDate'].'\' AND DATE(`regDt`) <= \''.$param['eDate'].'\'';
		$data = $this->db->query_fetch($sql, true);
		if($data)
			foreach ($data as $row) {
				$this->excelBody[] = '<tr>' . chr(10);
				$this->excelBody[] = '<td>'. $row['memNm'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['cellPhone'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['email'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['regDt'].'</td>' . chr(10);
				$this->excelBody[] = '</tr>' . chr(10);
			}
        $this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	
	public function abbotMember2($param)
	{
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		$head = ['리브레 회원번호','아델라 회원번호','이름','연락처','이메일','약국코드','접속경로','가입일시','명세표출력','정보수집','마케팅/광고','3자 개인정보','3자 민감정보'];
		foreach ($head as $row) {
			$this->excelHeader .= '<td class="title" style="text-align:center">' . $row . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->getListAbboteMember($param);
		if($data)
			foreach ($data as $row) {
				$this->excelBody[] = '<tr>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['abbott_sno'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['memNo'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['memNm'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['cellPhone'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['email'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['pharmacy_code'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['device'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['entryDt'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['is_print'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['Info_collection'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['marketing_reception'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['third_party_privacy'].'</td>' . chr(10);
				$this->excelBody[] = '<td>'.$row['third_party_sensitive'].'</td>' . chr(10);
				$this->excelBody[] = '</tr>' . chr(10);
			}
        $this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	public function abbotOrder($param)
	{
		$this->excelHeader = '<table border="1"><tr>' . chr(10);
		$head = ['주문번호','아델라 회원번호','리브레케어 회원번호','주문일시','수정일시','상품 주문번호','수량','처리 상태','배송횟수','배송주기','배송회차','자동연장','연장횟수','센서추가증정'];
		foreach ($head as $row) {
			$this->excelHeader .= '<td class="title" style="text-align:center">' . $row . '</td>' . chr(10);
		}
		$this->excelHeader .= '</tr>' . chr(10);
		$cossia = new \Component\Cossia\Cossia;
		$data = $cossia->getListAbboteOrder($param);
		if($data)
			foreach ($data as $row) {

				//2021.12.03민트웹 추가시작
				$period="단건";
				if(!empty($row['period']))
					$period=$row['period'];
					

				$schedule="";

				if($row['schedule']>0)
					$schedule = "&nbsp;".$row['schedule']."/".$row['schedule_tcount'];
				//2021.12.03민트웹 추가종료
					

				$this->excelBody[] = '<tr>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['orderNo'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['memNo'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['abbott_sno'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['regDt'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['modDt'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['goods_order_no'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="mso-number-format:\'\@\'; text-align:right">'.$row['goodsCnt'].'</td>' . chr(10);
				$this->excelBody[] = '<td style="text-align:center">'.$row['orderStatus'].'</td>' . chr(10);

				$this->excelBody[] = '<td style="text-align:center">'.$row['schedule_tcount'].'</td>' . chr(10);//2022.06.10민트웹추가
				$this->excelBody[] = '<td style="text-align:center">'.$period.'</td>' . chr(10);//2021.12.03민트웹 추가
				
				$ex='';
				if($row['schedule']>0){
					$ex = $row['schedule']."/".$row['deliveryEa'];
				}
				$this->excelBody[] = "<td style='text-align:center;mso-number-format:\@'>".$ex.'</td>' . chr(10);//2022.06.10민트웹추가
				
				$this->excelBody[] = '<td style="text-align:center">'.$row['autoExtend'].'</td>' . chr(10);//2022.06.10민트웹추가
				$this->excelBody[] = '<td style="text-align:center">'.$row['autoExtendCount'].'</td>' . chr(10);//2022.06.10민트웹추가
				$this->excelBody[] = '<td style="text-align:center">'.$row['gift'].'</td>' . chr(10);//2022.06.10민트웹추가
				
				$this->excelBody[] = '</tr>' . chr(10);
			}
        $this->excelFooter = '</table>' . chr(10);
		$this->printExcel();
	}
	
	
	
}