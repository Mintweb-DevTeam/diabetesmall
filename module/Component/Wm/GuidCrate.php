<?php

namespace Component\Wm;

use Request;
use App;
use Session;

class GuidCrate
{
	private function generate_uuid()
	{
		$data = openssl_random_pseudo_bytes(16);
		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}	
	
	
	public function getuuid($mode="")
	{	
		
		$uuid = $this->generate_uuid();
		$siteKey = \Session::get("siteKey");
		$ip = \Request::getRemoteAddress();
		
		$db = \App::load(\DB::class);
		
		$where=" where 1=1";
		$memNo = \Session::get("member.memNo");
		
		if(!empty($memNo)){
		
			$where.=" and memNo='$memNo' or ip='$ip'";	
		
		}else{
		
			$where.=" and ip='$ip'";	
		}
		
		
		$sql = "select * from wm_uuid ".$where." order by regDt DESC limit 0,1";
		$row = $db->fetch($sql);
		
		if(!empty($row['uuid'])){
			
			if(empty($mode)){
				
				if(empty($row['memNo'])&& !empty($memNo)){
					$upSQL = "update wm_uuid set siteKey='$siteKey',memNo='$memNo',modDt=sysdate() where ip='{$ip}'";
					$db->query($upSQL);
				}else if(empty($row['memNo'])&& empty($memNo)){
					$upSQL = "update wm_uuid set siteKey='$siteKey',modDt=sysdate() where ip='{$ip}'";
					$db->query($upSQL);
				}else if(!empty($row['memNo'])&& !empty($memNo)){
					$upSQL = "update wm_uuid set siteKey='$siteKey',ip='$ip',modDt=sysdate() where memNo='{$memNo}'";
					$db->query($upSQL);
				}
				
				return $row['uuid'];
			}else{
			
				$delSQL="delete from wm_uuid".$where;
				$db->query($delSQL);
			}
		
		}else{
			if(empty($mode)){
				$inSQL ="insert into wm_uuid set uuid='$uuid',memNo='$memNo',siteKey='$siteKey',ip='{$ip}',regDt=sysdate()";
				$db->query($inSQL);
				return $uuid;
			}
			
		}
		
	}
	
	public function simpleGetuuid()
	{
	
		return $this->generate_uuid();
		
	}
}