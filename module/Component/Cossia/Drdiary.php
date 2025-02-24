<?php
/*
# 2023-05-18 Cossia
# 닥다몰 회원 정보를 입력하기 위한 API
*/
namespace Component\Cossia;
use Exception;
class Drdiary
{
	const RESULT_CODE_SUCCESS = 200;
	const RESULT_MSG_SUCCESS = '성공';
	
	const RESULT_CODE_BAD_REQUEST = 400;
	const RESULT_MSG_BAD_REQUEST = '잘못된 요청';
	
	const FILDS_CODE_BAD_REQUEST = 410;
	const FILDS_MSG_BAD_REQUEST = '필요한 데이터가 없습니다 : ';
	
	const OVERLAP_CODE_REQUEST = 420;
	const OVERLAP_MSG_REQUEST = '중복된 데이타 입니다.';
	
	const RESULT_CODE_BAD_SERVER = 500;
	const RESULT_MSG_BAD_SERVER = '서버에러';
	const AUTHORIZATION = 'Bare MUI3MjM0RUExQTgyRDA1ODZGRDUyOEM4OTY2QTVCN0Y=';
	
	private $db;
	private $header;
	private $data;
	private $result = [];
	private $table = 'co_abbottMember';
	private $validateFilds = [
		'memNm' => '회원명',
		'cellPhone' => '휴대폰번호',
		'email' => '이메일',
		'privateConsignFl20' => '마케팅용 정보수집 동의',
		'privateConsignFl23' => '마케팅/광고 수신',
		'privateOfferFl25' => '3자 개인정보제공',
		'privateOfferFl26' => '3자 민감정보제공',
	];
	private $insertFilds = [
		['fild' => 'memNm', 'function' => ''],
		['fild' => 'cellPhone', 'function' => 'formatPhone'],
		['fild' => 'email', 'function' => ''],
		['fild' => 'privateConsignFl', 'function' => 'makePrivate'],
		['fild' => 'privateOfferFl', 'function' => 'makePrivate'],
		['fild' => 'device', 'function' => 'makeDevice'],
	];
	
	public function __construct()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		
//		$this->headers = getallheaders();
		$this->data = json_decode( file_get_contents("php://input"), true );
//		if( $this->headers['Authorization'] != self::AUTHORIZATION )
//			throw new Exception(self::RESULT_MSG_BAD_REQUEST, self::RESULT_CODE_BAD_REQUEST);
		if( !is_array( $this->data ) )
			throw new Exception(self::RESULT_MSG_BAD_REQUEST, self::RESULT_CODE_BAD_REQUEST);
		$non = $this->validateDatas();
		if( $non != '' )
			throw new Exception(self::FILDS_MSG_BAD_REQUEST.$non, self::FILDS_CODE_BAD_REQUEST);
	}
	
	public function validateDatas() : string
	{
		$keys = array_keys( $this->data );
		foreach( $this->validateFilds as $key => $val ){
			if( !in_array( $key, $keys ) || !$this->data[$key] ){
				return $val;
				exit;
			}
		}
		return '';
	}
	
	function formatPhone( string $phone = '' ) : string
	{
		$phone = preg_replace("/[^0-9]/", "", $phone);
		$length = strlen($phone);
		$phone_ = '';
		switch($length){
			case 11 :
				$phone_ = preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
			break;
			case 10:
				$phone_ = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
			break;
		}
		return $phone_;
	}
	
	public function makePrivate( $code ) : string
	{
		return $code == 'privateConsignFl'
			?
		json_encode( ['20'=> $this->data['privateConsignFl20'], '23'=> $this->data['privateConsignFl23'] ] )
			:
		json_encode( ['25'=> $this->data['privateOfferFl25'], '26'=> $this->data['privateOfferFl26'] ] );
	}
	
	public function makeDevice() : string
	{
		return 'dr';
	}
	
	public function saveData() : array
	{
		if( $this->isOverlap() ){
			$data = [];
			foreach( $this->insertFilds as $row )
			{
				switch( $row['function'] ){
					case 'formatPhone' :
						$value = $this->formatPhone( $this->data['cellPhone'] );
					break;
					case 'makePrivate' :
						$value = $this->makePrivate( $row['fild'] );
					break;
					case 'makeDevice' :
						$value = $this->makeDevice('makeDevice');
					break;
					default :
						$value = $this->data[$row['fild']];
				}
				$data[] = '`'.$row['fild'].'` = \''. $value .'\'';
			}
			$sql = 'INSERT INTO `'.$this->table.'` SET '.implode(', ', $data);
			return $this->db->query($sql)
				?
			['code'=>self::RESULT_CODE_SUCCESS, 'msg'=>self::RESULT_MSG_SUCCESS]
				:
			['code'=>self::RESULT_CODE_BAD_SERVER, 'msg'=>self::RESULT_MSG_BAD_SERVER];
		}else return ['code'=>self::OVERLAP_CODE_REQUEST, 'msg'=>self::OVERLAP_MSG_REQUEST];
	}
	
	public function isOverlap() : bool
	{
		$sql = 'SELECT COUNT(*) AS `cnt` FROM `'.$this->table.'` WHERE `memNm` = \''.$this->data['memNm'].'\' AND `cellPhone` = \''.$this->formatPhone( $this->data['cellPhone'] ).'\'';
		return $this->db->query_fetch($sql, true)[0]['cnt']?false:true;
	}
}