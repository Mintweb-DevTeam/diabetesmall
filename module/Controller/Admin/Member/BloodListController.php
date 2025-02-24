<?php
namespace Controller\Admin\Member;
use Request;
class BloodListController extends \Controller\Admin\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$this->callMenu('member', 'adela', 'blood');
		$request = \App::getInstance('request');
		if (!$request->get()->has('page')) $request->get()->set('page', 1);
		if (!$request->get()->has('pageNum')) $request->get()->set('pageNum', 10);
		$pageObject = new \Component\Page\Page( $request->get()->get('page'), 0, 0, $request->get()->get('pageNum') );
		$pageObject->setUrl($request->getQueryString());
		$sqlarr = [];
		
		if($request->get()->get('keyword'))
			$sqlarr[] = $request->get()->get('key').' LIKE \'%'.$request->get()->get('keyword').'%\' ';
		if($request->get()->get('sDate'))
			$sqlarr[] = ' DATE(`b`.`agreeDt`) >= \''.$request->get()->get('sDate').'\' ';
		if($request->get()->get('eDate'))
			$sqlarr[] = ' DATE(`b`.`agreeDt`) <= \''.$request->get()->get('eDate').'\' ';
		
		$sql_ = ( count($sqlarr) ) ? ' WHERE '.implode(' AND ', $sqlarr) : '';
		$combineSearch = [
			'm.memId'=>'아이디',
			'm.cellPhone'=>'전화번호',
			'b.controlNo'=>'관리번호',
			'b.patientName'=>'환자명',
			'b.agent'=>'법정대리인'
		];
		$this->setData('combineSearch', $combineSearch);
		
		
		$total = $this->db->query_fetch('SELECT COUNT(`b`.`sno`) AS `cnt` FROM `co_bloodInfo` AS `b` LEFT JOIN `es_member` AS `m` ON `m`.`memNo` = `b`.`memNo` '.$sql_, true)[0]['cnt'];
		$pageObject->setTotal( $total );
		$pageObject->setCache(true);
		$amount = $this->db->query_fetch('SELECT COUNT(`sno`) AS `cnt` FROM `co_bloodInfo`', true)[0]['cnt'];
		$pageObject->setAmount( $amount );
		$limit = ($request->get()->get('page') - 1) * $request->get()->get('pageNum');
		$sql = 'SELECT `b`.`sno`, `b`.`agreeDt`, `b`.`controlNo`, `b`.`patientName`, `b`.`patientBirth`, `b`.`agent`, `b`.`gender`, `m`.`memId`, `m`.`cellPhone` FROM `co_bloodInfo` AS `b` LEFT JOIN `es_member` AS `m` ON `m`.`memNo` = `b`.`memNo` '.$sql_.' ORDER BY `b`.`sno` DESC LIMIT '.$limit.', '.$request->get()->get('pageNum');
		$data = $this->db->query_fetch($sql, true);
		
		$pageObject->setPage();
		$this->setData('page', $pageObject);
		$this->setData('data', $data);

		/*
		$info = new \Component\Cossia\BloodInfo;
		$info->fileConverting(1);
		*/
		
	}
}