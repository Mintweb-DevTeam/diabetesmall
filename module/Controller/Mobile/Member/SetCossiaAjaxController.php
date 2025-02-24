<?php
namespace Controller\Mobile\Member;
use Request;
class SetCossiaAjaxController extends \Controller\Mobile\Controller {
    public function index(){
		$post = Request::post()->all();
		$cossia = new \Component\Cossia\Cossia;
		$no = $this->random(0, 9, 5);
		$cossia->customSms($post['phone'], $no);
		$this->json($no);
		exit;
	}
	function random($min, $max, $num) {
		$arr = array();
		while ($num > count($arr)) {
			$i = rand($min, $max);
			$arr[$i] = $i; 
		}
		foreach($arr as $row){
			$data .= $row;
		}
		return $data;
	}
}