<?php
namespace Controller\Front\Proxyorder;

use App;
use Request;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\LmsMessage;
use Framework\Security\Otp;
class SmsSendController extends \Controller\Front\Controller
{

	public function index()
	{

		exit;
	
		$db=App::load(\DB::class);
		//escape
		$in = Request::request()->all();

		if(empty($in['sendName']) || empty($in['sendTel']) || empty($in['sendMsg']) ){
			$data['result']=0;

			$this->json($data);
			
			exit;
		}

		$in['sendMsg']=$in['sendName'].$in['sendTel'].$in['sendMsg'];

		$in['sendTel']="";

		$data=[];
		if (Request::isAjax()) {
			
			$result = $this->sendSms('',$in['sendMsg']);

			if($result==1){
				$data['result']=1;
			}else{
				$data['result']=0;
			}
			
		}else{
			$data['result']=0;

			
		}
		$this->json($data);

		exit;
	
	}

   /* SMS 전송 처리 */
   public function sendSms($mobile, $contents)
   {
      
       $bool = false;
       $smsPoint = Sms::getPoint();

	  

		$proxy = new \Component\Proxyservice\ProxyService();
		$cfg = $proxy->getCfg();

		$mobile=$cfg['cellPhone'];

		$smsUtil = \App::load(\Component\Sms\SmsUtil::class);

       if ($smsPoint >= 1) {


            $adminSecuritySmsAuthNumber = Otp::getOtp(8);
            $receiver[0]['cellPhone'] = $mobile;
            $smsSender = \App::load('\Component\\Sms\\SmsSender');
            $smsSender->setSmsPoint($smsPoint);

			
            if(mb_strlen($contents, 'euc-kr')>90){
              $smsSender->setMessage(new LmsMessage($contents));
            }else{
              $smsSender->setMessage(new SmsMessage($contents));
            }

            $smsSender->validPassword($cfg['smsPassword']);
            $smsSender->setSmsType('user');
            $smsSender->setReceiver($receiver);
            $smsSender->setLogData(['disableResend' => false]);
            $smsSender->setContentsMask([$adminSecuritySmsAuthNumber]);
            $smsResult = $smsSender->send();
            $smsResult['smsAuthNumber'] = $adminSecuritySmsAuthNumber;
			

            if ($smsResult['success'] === 1)
              $bool = true;
       }
	   

       return $bool;
   }

}