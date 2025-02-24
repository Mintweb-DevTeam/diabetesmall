<?php

namespace Component\Subscription;

use Exception;

class WmTest
{


	public function tm($index=0)
	{
		
		if($index==50){
			throw new Exception("out");
		}else{
			return $index;
		}
	
	}

}