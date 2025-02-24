<?php
namespace Component\Cossia;

use Globals;
use Request;
use Session;
use Component\Mail\MailLog;
use Component\Mail\MailMime;
use Component\Mail\MailUtil;

class Mail
{
	private $fromMail, $fromNm;
	const body_li_join = 'body_LIBREJOIN.php';			// 리브레케어 멤버십 등록 완료 메일
	const subj_li_join = 'subject_LIBREJOIN.php';		// 리브레케어 멤버십 등록 완료 메일
	
	const body_li_in = 'body_LIBREIN.php';			// 리브레케어 멤버십 가입자 인증 확인 완료 메일
	const subj_li_in = 'subject_LIBREIN.php';		// 리브레케어 멤버십 가입자 인증 확인 완료 메일
	
	const body_ad_join = 'body_ADELAJOIN.php';			// 아델라쇼핑몰 회원가입 완료 메일
	const subj_ad_join = 'subject_ADELAJOIN.php';		// 아델라쇼핑몰 회원가입 완료 메일
	
	const body_li_in_qr = 'body_LIBREIN_QR.php';			// 리브레케어 멤버십 가입자 인증 확인 완료 메일 _QR
	const subj_li_in_qr = 'subject_LIBREIN_QR.php';		// 리브레케어 멤버십 가입자 인증 확인 완료 메일_QR
	
	const body_li_join_qr = 'body_ADELAJOIN_QR.php';			// 아델라쇼핑몰 회원가입 완료 메일_QR
	const subj_li_join_qr = 'subject_ADELAJOIN_QR.php';		// 아델라쇼핑몰 회원가입 완료 메일_QR

	const body_li_agree = 'body_LIBREAGREE.php';		// 리브레케어 동의하기 완료 메일
	const subj_li_agree = 'subject_LIBREAGREE.php';		// 리브레케어 동의하기 완료 메일
	
	const sms_li_agree = 'sms_LIBREAGREE.php';			// 기브레케어 동의하기  등록완료 문자

	const sms_li_comp = 'sms_LIBRECOMP.php';			// 기브레케어 멤버십 등록완료 문자
	const sms_li_chek = 'sms_LIBRECHEK.php';			// 리브레케어 멤버십 가입자가 인증 확인 완료할경우 문자
	const sms_ad_join = 'sms_ADELAJOIN.php';			// 아델라 쇼핑몰 회원가입 완료 문자	
	
	const sms_namdate = 'sms_MANDATE.php';				// 대리청구 신청완료 sms
	
	const ar_url = 'https://play.google.com/store/apps/details?id=com.freestylelibre.app.kr';					// 안드로이드 url
	const i_url = 'https://itunes.apple.com/app/freestyle-librelink-kr/id1354596227?ls=1&mt=8';					// 아이폰 url
	
	
	private $libre_qr =0;
	
	private $join_btn = '<div style="text-align: center;margin-bottom: 40px;"><a href="{join_url}" style="background:#001489;padding:14px 60px;color:#fff;font-size:18px;text-decoration: none;">쇼핑몰 회원가입 바로가기</a></div>';
	
	public function __construct()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$this->mailLog = new MailLog;
		$this->util = new MailUtil;
		$this->smsAdmin = new \Component\Sms\SmsAdmin;
        $this->smsUtil = \App::load(\Component\Sms\SmsUtil::class);
		$util = $this->util->getBasicConfig();
		$this->fromMail = $util['email'];
		$this->fromNm = $util['mallNm'];
		$this->http = ( Request::server()->get('HTTPS') ) ? 'https://' : 'http://';
	}
	/*
	*	sno, email, memNm, cellPhone, type, isQr
	* 	type 이 uze == 확인 되는 사람
	*/
	
	public function libre_qr_send($param){
		$this->receiverMail = $param['email'];
		$this->receiver = $param['memNm'];
		$join_btn = ($param['isQr']) ? '' : str_replace( ['{join_url}'], [$http.'/member/co_join_stepe.php?sno='.$param['sno'].'&name='.$param['memNm']], $this->join_btn);
		$title = ($param['type'] == 'uze') ? self::subj_li_in_qr : self::subj_li_join_qr;
		$body = ($param['type'] == 'uze') ? self::body_li_in_qr : self::body_li_join_qr;
		$sms = ($param['type'] == 'uze') ? self::sms_li_chek : self::sms_li_comp;
		$this->mail_title = $this->replace($param, $title);
		$this->mail_contents = $this->replace($param, $body, $join_btn);
		$this->sendMail();
		$smsparam = [
			'send_content' => $this->replace($param, $sms),
			'send_target' => $param['cellPhone']
		];
		
		$this->libre_qr = 1;
		$this->admin_sms($smsparam);
	}
	
	public function libre_send($param){
		$this->receiverMail = $param['email'];
		$this->receiver = $param['memNm'];
		$join_btn = ($param['isQr']) ? '' : str_replace( ['{join_url}'], [$http.'/member/co_join_stepe.php?sno='.$param['sno'].'&name='.$param['memNm']], $this->join_btn);
		$title = ($param['type'] == 'uze') ? self::subj_li_in : self::subj_li_join;
		$body = ($param['type'] == 'uze') ? self::body_li_in : self::body_li_join;
		$sms = ($param['type'] == 'uze') ? self::sms_li_chek : self::sms_li_comp;
		$this->mail_title = $this->replace($param, $title);
		$this->mail_contents = $this->replace($param, $body, $join_btn);
		$this->sendMail();
		$smsparam = [
			'send_content' => $this->replace($param, $sms),
			'send_target' => $param['cellPhone']
		];
		$this->admin_sms($smsparam);
	}
	
	public function agree_send($param){
		$this->receiverMail = $param['email'];
		$this->receiver = $param['memNm'];
		$this->mail_title = $this->replace($param, self::subj_li_agree);
		$this->mail_contents = $this->replace($param, self::body_li_agree);
		$this->sendMail();
		$smsparam = [
			'send_content' => $this->replace($param, self::sms_li_agree),
			'send_target' => $param['cellPhone']
		];
		$this->admin_sms($smsparam);
	}
	
	public function adela_join($param){
		$this->receiverMail = $param['email'];
		$this->receiver = $param['memNm'];
		$this->mail_title = $this->replace($param, self::subj_ad_join);
		$this->mail_contents = $this->replace($param, self::body_ad_join);
		$this->sendMail();
		$smsparam = [
			'send_content' => $this->replace($param, self::sms_ad_join),
			'send_target' => $param['cellPhone']
		];
		$this->admin_sms($smsparam);
	}
	public function replace($param, $const, $join_btn = ''){
		$ch1 = ['{memNm}','{email}', '{cellPhone}', '{mallNm}', '{ar_url}', '{i_url}', '{join_btn}'];
		$ch2 = [$param['memNm'], $param['email'], $param['cellPhone'], $this->fromNm, self::ar_url, self::i_url, $join_btn];
		return str_replace( $ch1, $ch2, $this->util->loadAutoMailTemplate($const));
	}
	
	public function mam_date_sms($cellPhone){
		$smsparam = [
			'send_content' => $this->util->loadAutoMailTemplate( self::sms_namdate ),
			'send_target' => $cellPhone
		];
		$this->admin_sms($smsparam);
	}
	
	private $qrsms = "리브레케어 멤버 가입이 완료되었습니다\n%name%님, 프리스타일 리브레 제품을 공식 약국 및 지정 의료기기 판매 업소에서 구매하실 수 있습니다.";
	public function admin_sms($param)
	{
		
		
		if($this->libre_qr==1){
			
			$sms_code = \Session::get("sms_code");
			
			if($sms_code==1){
				$db = \App::load(\DB::class);
				$server = \Request::server()->toArray();
				
				$userAgent = $server['HTTP_USER_AGENT'];
				
				$sql="select * from wmJoinSmsConfig";
				$row = $db->query_fetch($sql);
				
				if (strpos($userAgent, 'Android') !== false) {
					$param['send_content'] = str_replace("{memNm}",$this->receiver,$row[0]['androidContent']);
				}else if(strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
					$param['send_content'] = str_replace("{memNm}",$this->receiver,$row[0]['iphoneContent']);
				}
				
				\Session::del('sms_code');
			}
		}
		
		$param_ = array(
			'mode' => 'smsSend',
			'sendFl' => ( strlen($param['send_content']) <= 90 ) ? 'sms' : 'lms',
			'directReceiverNumbers' => [$param['send_target']],
			'password' => $this->smsUtil->getPassword(),
			'smsSendType' => 'now',
			'receiverType' => 'direct',
			'smsContents' => $param['send_content'],
		);
		return $this->smsAdmin->sendSms($param_);
	}
	public function admin_mail($post)
	{
		foreach($post['send_target'] as $row){
			$this->nameFrom	=	"=?UTF-8?B?".base64_encode($this->fromNm)."?=";
			$this->mailFrom	=	$this->fromMail;
			$this->mailTo 	=	"=?UTF-8?B?".base64_encode($row)."?=";
			$this->mailTo	.=	$row;
			$this->header_ 	=	"Content-Type: text/html; charset=utf-8\r\n";
			$this->header_ .=	"MIME-Version: 1.0\r\n";
			$this->header_ .=	"From: ". $this->nameFrom ." <". $this->mailFrom .">\r\n";
			mail( $this->mailTo, "=?UTF-8?B?".base64_encode($post['send_title'])."?=", $post['send_content'], $this->header_, $this->mailFrom );
			$log = [
				'subject'           => $post['send_title'],
				'contents'          => $post['send_content'],
				'receiver'          => $row,
				'receiverCnt'       => 1,
				'receiverCondition' => '',
				'sendType'          => 'manual',
			];
			$this->mailLog->insertMailLogByArray($log);
		}
	}
	/*
	* $param = array('receiver'=>'수취인명','receiverMail'=>'수취인 메일','sno'=>'sno','cellPhone'=>'cellPhone', 'isMobile'=>true);
	*/
	private $newSms = "리브레케어 멤버 가입이 완료되었습니다\n%name%님, 프리스타일 리브레 제품을 공식 약국 및 지정 의료기기 판매 업소에서 구매하실 수 있습니다.\n\n온라인 구매를 위한 회원가입 페이지로 바로가기\n%url%";
	
	private $nSms = "리브레케어 멤버 가입이 완료되었습니다\n%name%님, 프리스타일 리브레 제품을 공식 약국 및 지정 의료기기 판매 업소에서 구매하실 수 있습니다.\n\n온라인 구매를 위한 회원가입 페이지로 바로가기\n%url%";
	
	private $ySms = "리브레케어 멤버 확인이 완료되었습니다\n%name%님, 프리스타일 리브레 제품을 공식 약국 및 지정 의료기기 판매 업소에서 구매하실 수 있습니다.\n\n온라인 구매 바로가기\n%url%";
	
	private $content = '<div style="padding:55px; width:900px;box-sizing:border-box;"><div style="text-align:center;padding-bottom:40px;"><img src="http://ysh.raonweb.com/adela/livre_logo.jpg" alt="freestyle livre" /></div><div style="text-align:center; font-size:30px; color:#000; font-weight:600; padding-bottom:10px;">%title%</div><div style="text-align:center; font-size:16px; font-weight:300;color:#333; padding-bottom:40px;">프리스타일 리브레 제품을 공식 약국 및 지정 의료기기 판매 업소에서 구매하실 수 있습니다.</div><div style="text-align:center;"><ul style="list-style:none;margin:0 auto; display:inline-block;"><li style="font-size:16px;font-weight:300; color:#666;margin-bottom:10px; padding-left:10px;text-align:left;position:relative;"><i style="position:absolute;left:0;top:8px;width:4px;height:4px;background:#295291;border-radius:100%"></i>성함 : %name%</li><li style="font-size:16px;font-weight:300; color:#666;padding-left:10px;margin-bottom:10px; text-align:left;position:relative;"><i style="position:absolute;left:0;top:8px;width:4px;height:4px;background:#295291;border-radius:100%"></i>이메일 : %email%</li><li style="font-size:16px;font-weight:300; color:#666;padding-left:10px; text-align:left;position:relative;"><i style="position:absolute;left:0;top:8px;width:4px;height:4px;background:#295291;border-radius:100%"></i>연락처 : %phone%</li></ul></div><div style="text-align:center;padding-top:40px"><a href="%url%" target="_blank" style="display:inline-block; color:#fff; font-size:16px; background:#295291;padding:20px 40px;text-decoration:none">%button%</a></div></div>';
	
	public function send($param)
	{
		$sql = 'SELECT `sno`, `memNm`, `cellPhone`, `email`, `isJoin`, DATE(`regDt`) AS `regDt` FROM `co_abbottMember` WHERE `sno` = '.$param['sno'];
		$result = $this->db->query_fetch($sql);
		$data = $result[0];
		
		$http = ( Request::server()->get('HTTPS') ) ? 'https://' : 'http://';
		if( $data['isJoin'] == 'y' ){	// ySms
			$this->contentsSms = str_replace( array('%name%', '%url%'), array( $data['memNm'], $http.'m.diabetesmall.co.kr/member/co_join_stepe.php?sno='.$data['sno'].'&name='.$data['memNm'] ), $this->newSms);
			$this->mail_title = '리브레케어 멤버 가입이 완료되었습니다.';
			$this->mail_contents = str_replace( array('%title%','%name%', '%email%', '%phone%', '%url%', '%button%'), array($this->mail_title, $data['memNm'], $data['email'], $data['cellPhone'], $http.'diabetesmall.co.kr/member/co_join_stepe.php?sno='.$data['sno'].'&name='.$data['memNm'],'온라인 구매를 위한 회원가입 페이지로 바로가기' ), $this->content);
		} elseif ( $data['isJoin'] == 'n' && strtotime($data['regDt']) == strtotime(date('Y-m-d')) ){	// nSms
			$this->contentsSms = str_replace( array('%name%', '%url%'), array( $data['memNm'], $http.'m.diabetesmall.co.kr/member/co_join_stepe.php?sno='.$data['sno'].'&name='.$data['memNm'] ), $this->nSms);
			$this->mail_title = '리브레케어 멤버 가입이 완료되었습니다.';
			$this->mail_contents = str_replace( array('%title%','%name%', '%email%', '%phone%', '%url%', '%button%'), array($this->mail_title, $data['memNm'], $data['email'], $data['cellPhone'], $http.'diabetesmall.co.kr/member/co_join_stepe.php?sno='.$data['sno'].'&name='.$data['memNm'],'온라인 구매를 위한 회원가입 페이지로 바로가기' ), $this->content);
		} elseif ( $data['isJoin'] == 'n' && strtotime($data['regDt']) < strtotime(date('Y-m-d')) ){	// newSms
			$this->contentsSms = str_replace( array('%name%', '%url%'), array( $data['memNm'], $http.'m.diabetesmall.co.kr/goods/goods_view.php?goodsNo=1000000000'), $this->ySms);
			$this->mail_title = '리브레케어 멤버 확인이 완료되었습니다.';
			$this->mail_contents = str_replace( array('%title%','%name%', '%email%', '%phone%', '%url%', '%button%'), array($this->mail_title, $data['memNm'], $data['email'], $data['cellPhone'], $http.'diabetesmall.co.kr/goods/goods_view.php?goodsNo=1000000000','온라인 구매 바로가기' ), $this->content);
		}
		$this->cellPhone = $data['cellPhone'];
		$this->receiver = $data['memNm'];
		$this->receiverMail = $data['email'];
		if($data['sno']){
			$this->sendMail();
			$this->customSms();
		}
	}
	/* 2020-02-23 고도몰 mail sand 기능으로 다시 만듬
	* Cossia
	*/
	public function sendMail()
	{
		$mail = new MailMime;
		$sednResult = $mail->setFrom($this->fromMail, $this->fromNm)->setTo($this->receiverMail, $this->receiver)->setSubject($this->mail_title)->setHtmlBody($this->mail_contents)->send();
		if($sednResult){
			$log = [
				'subject'           => $this->mail_title,
				'contents'          => $contents,
				'receiver'          => $this->receiver,
				'receiverCnt'       => 1,
				'receiverCondition' => '',
				'sendType'          => 'manual',
			];
			$this->mailLog->insertMailLogByArray($log);			
		}
	}
	public function old_send_mail()
	{
		$this->nameFrom	=	"=?UTF-8?B?".base64_encode($this->fromNm)."?=";
		$this->mailFrom	=	$this->fromMail;
		$this->mailTo 	=	"=?UTF-8?B?".base64_encode($this->receiver)."?=";
    	$this->mailTo	.=	$this->receiverMail;
		$this->header_ 	=	"Content-Type: text/html; charset=utf-8\r\n";
		$this->header_ .=	"MIME-Version: 1.0\r\n";
		$this->header_ .=	"From: ". $this->nameFrom ." <". $this->mailFrom .">\r\n";
		mail( $this->mailTo, "=?UTF-8?B?".base64_encode($this->mail_title)."?=", $this->mail_contents, $this->header_, $this->mailFrom );
		$log = [
            'subject'           => $this->mail_title,
            'contents'          => $this->mail_contents,
            'receiver'          => $this->receiver,
            'receiverCnt'       => 1,
            'receiverCondition' => '',
            'sendType'          => 'manual',
		];
		$this->mailLog->insertMailLogByArray($log);
	}

	
	public function customSms()
	{
		$param = array(
			'mode' => 'smsSend',
			'sendFl' => 'lms',
			'directReceiverNumbers' => array($this->cellPhone),
			'password' => $this->smsUtil->getPassword(),
			'smsSendType' => 'now',
			'receiverType' => 'direct',
			'smsContents' => $this->contentsSms,
		);
		return $this->smsAdmin->sendSms($param);
	}
	public function qr_send($cellPhone, $name)
	{
		$this->cellPhone = $cellPhone;
		$this->contentsSms = str_replace( array('%name%'), array( $name ), $this->qrsms);
		// $param = ['send_content'=>str_replace( array('%name%'), array( $name ), $this->qrsms), 'send_target'=>$cellPhone];
		$this->customSms();
	}
	
	private $newMemberSms = "아델라 회원가입이 완료되었습니다\n\n이제 프리스타일 리브레 제품을 온라인 아델라 샵에서 구매하실 수 있습니다.\n성함:%memNm%님\n이메일:%email%\n연락처:%cellPhone%";
	/*
	* $param = array('memNm'=>'name','email'=>'email','cellPhone'=>'phone');
	* 회원 가입 후 sms, mail 발송
	*/
	public function in_member_send($param)
	{
		$http = ( Request::server()->get('HTTPS') ) ? 'https://' : 'http://';
		$this->contentsSms = str_replace( array('%memNm%', '%email%', '%cellPhone%'), array( $param['memNm'], $param['email'], $param['cellPhone'] ), $this->newMemberSms);
		$this->mail_title = '아델라 회원가입이 완료되었습니다';
		$this->mail_contents = str_replace( array('%title%','%name%', '%email%', '%phone%', '%url%', '%button%'), array($this->mail_title, $param['memNm'], $param['email'], $param['cellPhone'], $http.'diabetesmall.co.kr/goods/goods_view.php?goodsNo=1000000000','온라인 구매 바로가기' ), $this->content);
		$this->cellPhone = $param['cellPhone'];
		$this->receiver = $param['memNm'];
		$this->receiverMail = $param['email'];
		$this->sendMail();
		$this->customSms();
	}
	
}