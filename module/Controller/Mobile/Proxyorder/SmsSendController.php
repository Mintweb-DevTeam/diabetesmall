<?php
namespace Controller\Mobile\Proxyorder;

use App;
use Request;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\LmsMessage;
use Framework\Security\Otp;
class SmsSendController extends \Controller\Front\Proxyorder\SmsSendController
{

	public function index()
	{
		parent::index();
	}
}