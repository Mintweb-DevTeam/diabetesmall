<?php
namespace Controller\Front\Service;
use Request;
class AbotteApiController extends \Controller\Front\Controller
{
	public function index()
	{
		$get = Request::get()->all();
		$data = [];
		$cossia = new \Component\Cossia\Cossia;
		if (!is_object($this->db)) $this->db = \App::load('DB');
		switch($get['type']){
			case 'member' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=member&sDate=2021-01-01&eDate=2021-01-02
				$data = $cossia->getListAbboteMember($get);
			break;
			case 'order' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=order&sDate=2021-01-01&eDate=2021-01-02
				$data = $cossia->getListAbboteOrder($get);
			break;
			case 'status' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=order&sDate=2021-01-01&eDate=2021-01-02
				$data = $cossia->getListAbboteOrder($get, 'status');
			break;
			case 'agree' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=agree&sDate=2021-07-08&eDate=2021-07-08
				$sql = 'SELECT * FROM `co_aboottAgree` WHERE DATE(`regDt`) >= \''.$get['sDate'].'\' AND DATE(`regDt`) <= \''.$get['eDate'].'\'';
				$data = $this->db->query_fetch($sql, true);
			break;
			case 'membership' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=membership&sDate=2021-07-08&eDate=2021-07-08
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=membership&cellPhone=01055615300
				$data = $cossia->getListAbboteMembership($get);
			break;
			case 'hcp' :
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=hcp&sDate=2021-07-08&eDate=2021-07-08
				// https://www.diabetesmall.co.kr/service/abotte_api.php?type=hcp&cellPhone=01055615300
				$data = $cossia->getListHcp($get);
			break;
		}
		echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK );
		exit;
	}
}