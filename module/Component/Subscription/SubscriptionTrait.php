<?php
namespace Component\Subscription;
use App;
use Request;

trait SubscriptionTrait 
{
    private $crypt_pass = "webnmobileisbest";
    private $crypt_iv = "webnmobileisbest";
    private $crypt_type = "AES-256-CBC";
    
    private $cfg;
    public $isMobile;
    
    public function getCfg()
    {
        return $this->cfg;
    }
    
    public function setCfg()
    {
       if (!is_object($this->db))
          $this->db  = App::load('DB');
     
       if ($tmp = $this->db->fetch("SELECT * FROM wm_subscription_config")) {
           $info = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
           $tmp['mallNm'] = $info['mallNm'];
           
           $server = Request::server()->toArray();
           if (strtoupper($server['HTTPS']) == 'ON')
              $domain = "https://";
           else
              $domain = "http://";

           $domain .= $server['HTTP_HOST'];
           $tmp['domain'] = $domain;
           $tmp['uid'] = $this->getUid();

		   $tmp['mid']="GODOdiabil";
		   $tmp['signKey']="NCtqZklZU0NOcEp4aHlvaWJCK1ZOQT09";
		   $tmp['lightKey']="VUJJNEtsNHRFeVVzSWd1V1JQT204QT09";
			
		  // if(\Request::getRemoteAddress()=="182.216.219.50" || \Request::getRemoteAddress()=="211.49.123.117"){
		//	   $tmp['deliveryEa']="6,12,24";//임시코드
		  // }else{
		//	  $tmp['deliveryEa']="6";//임시코드
		 //  }
           
           $tmp['period'] = explode(",", $tmp['period']);
           $tmp['discount'] = explode(",", $tmp['discount']);
           $tmp['deliveryEa'] = explode(",", $tmp['deliveryEa']);
           $this->cfg = $tmp;
       }
    }
    
    public function getUid()
    {
        return round(microtime(true) * 1000);
    }
    
    /* 암호화 */
   public function encrypt($data = null)
   {
       if ($data) {
          $endata = openssl_encrypt($data , $this->crypt_type, $this->crypt_pass, true, $this->crypt_iv);
          $endata = base64_encode($endata);
          return $endata;
       }
   }
   
   /* 복호화 */
   public function decrypt($data = null)
   {
       if ($data) {
           $data = base64_decode($data);
           $data = openssl_decrypt($data, $this->crypt_type, $this->crypt_pass, true, $this->crypt_iv);
           
           return $data;
       }
   }
   
   public function addMileage($memNo, $amount, $msg = "")
   {
     if ($memNo && $amount) {
       $mileage = App::load("\Component\Mileage\Mileage");
       $data = array(
           'chk' => $memNo,
           'mileageCheckFl' => 'add',
           'mileageValue' => $amount,
           'reasonCd' => '01005003',
           'contents' => $msg
       );

       return $mileage->addMileage($data);
     }
   }

   public function delMileage($memNo, $amount, $msg = "")
   {
     if ($memNo && $amount) {
       $mileage = App::load("\Component\Mileage\Mileage");
       $data = array(
           'chk' => $memNo,
           'mileageCheckFl' => 'remove',
           'mileageValue' => $amount,
           'reasonCd' => '01005001',
           'contents' => $msg
       );

       return $mileage->removeMileage($data);
     }
   }

   public function addDeposit($memNo, $amount, $msg = "")
   {
     if ($memNo && $amount) {
       $deposit = App::load("\Component\Deposit\Deposit");

        $data = array(
            'chk' => $memNo,
            'depositCheckFl' => 'add',
            'depositValue' => $amount,
            'reasonCd' => '01006001',
            'contents' => $msg
        );

        return $deposit->addDeposit($data);
      }
    }

    public function delDeposit($memNo, $amount, $msg = "")
    {
      if ($memNo && $amount) {
          $deposit = App::load("\Component\Deposit\Deposit");

          $data = array(
              'chk' => $memNo,
              'depositCheckFl' => 'remove',
              'removeMethodFl' => "minus",
              'depositValue' => $amount,
              'reasonCd' => '01006003',
              'contents' => $msg
          );

          return $deposit->removeDeposit($data);
        }
    }
   
   /* 비밀번호 해시 */
   public function getPasswordHash($password = null)
   {
       if ($password)
           return password_hash($password, PASSWORD_DEFAULT, ['cost' => 5]);
   }
   
   
   
   /* 스케줄 목록 */
   public function getScheduleList($no = 10, $stamp = 0, $period = null,$hope_date="")
   {


       if (empty($stamp))
           $stamp = time();
       
       $stamp = strtotime(date("Ymd", $stamp));
       $hList = $this->getHolidayList();
       $cfg = $this->getCfg();


		//$deliveryDays = $cfg['deliveryDays']?$cfg['deliveryDays']:0;
		
		$order_time = date("H");
		
		if(\Request::getRemoteAddress()=="112.145.36.156"){

			//$this->mode="getScheduleList";
		
		}

		if($this->mode=="getScheduleList"){
			if(!empty($cfg['am_type']) && $order_time<$cfg['am_type']){
				$deliveryDays = $cfg['am_deliveryDays'];
			}else if(!empty($cfg['af_type']) && $order_time>=$cfg['af_type']){
				$deliveryDays = $cfg['af_deliveryDays'];
			}

		}else
			$deliveryDays = $cfg['deliveryDays']?$cfg['deliveryDays']:0;

       if (empty($no))
           $no = 1;
       

		if(!is_array($period)){
		   $period = $period?$period:"1_week";
		   $period = explode("_", $period);
		}

       $list = [];
       $list[] = ['stamp' => $stamp];
       
	   if(!empty($hope_date))
		 $hope_stamp = strtotime(str_replace("-","",$hope_date));

	   if ($no > 1) {
          for ($i = 1; $i < $no; $i++) {
              
			
			  if(!empty($hope_stamp) && $period[0]>=4){
				  if($i==1){
					$new_stamp = strtotime(date("Y-m-d",$hope_stamp)." +24 day");

					$new_stamp1 = strtotime(date("Y-m-d",$hope_stamp)." +24 day");

					
				  }else{
					$n = ($i -1) * $period[0];

					if($n<=0)
						$n = $period[0];
					$str = "+{$n} {$period[1]}";
					
					if($this->mode=="getScheduleList")
						$new_stamp = strtotime($str, $new_stamp1);
					else
						$new_stamp = strtotime($str, $stamp);
				  }
			  }else if(empty($hope_stamp) && $period[0]>=4){
				 if($i==1){
					 if($this->mode=="getScheduleList"){
					
						$new_stamp = strtotime(date("Y-m-d",$stamp)." +24 day");
						$new_stamp1 = strtotime(date("Y-m-d",$stamp)." +24 day");
					 }else{

						  $n = $i * $period[0];
						  $str = "+{$n} {$period[1]}";
						  $new_stamp = strtotime($str, $stamp);

					 }
				  }else{
					
					if($this->mode=="getScheduleList"){
						$n = ($i -1) * $period[0];

						if($n<=0)
							$n = $period[0];
					}else
						$n = $i * $period[0];

					$str = "+{$n} {$period[1]}";

					if($this->mode=="getScheduleList")
						$new_stamp = strtotime($str, $new_stamp1);
					else
						$new_stamp = strtotime($str, $stamp);
				  }

			  }else{
					$n = $i * $period[0];
					$str = "+{$n} {$period[1]}";

					$new_stamp = strtotime($str, $stamp);
			  
			  }

				
			
              $list[] = ['stamp' => $new_stamp];
          }
       }
       
       foreach ($list as $k => $li) {

          $stamp = $li['stamp'];
			
		  if($k==0){
			  if(empty($hope_stamp)){

				  $delivery_stamp = $stamp + (60 * 60 * 24 * $deliveryDays);
			  }else{
				 $delivery_stamp = $hope_stamp;// + (60 * 60 * 24 * $deliveryDays);
			  }
		  }else
           $delivery_stamp = $stamp + (60 * 60 * 24 * $deliveryDays);
            
          $yoil =date("w", $delivery_stamp);
          if ($yoil == 6)
            $delivery_stamp += 60 * 60 * 24 * 2;
          else if ($yoil == 0)
            $delivery_stamp += 60 * 60 * 24;
              
              
          foreach ($hList as $h) {
            if ($delivery_stamp == $h['stamp'])
                $delivery_stamp += 60 * 60 * 24;
          }
          
          $list[$k]['delivery_stamp'] = $delivery_stamp;
       }
       
       return $list;
   }
   
   public function getHolidayList()
   {
       $list = [];
       $stamp = strtotime(date("Ymd"));
       if ($tmp = $this->db->query_fetch("SELECT * FROM wm_subscription_holiday WHERE stamp >= '{$stamp}' ORDER BY stamp"))
          $list = $tmp;
       
       return $list;
   }
   
   /* 주문단계 목록 */
   public function getOrderStatusList()
   {
         $status = [];
         if ($tmp = gd_policy('order.status')) {
             foreach ($tmp as $_tmp) {
                 foreach ($_tmp as $k => $v) {
                     $status[$k] = $v['user'];
                 }
              }
          }

          return $status;
   }
}