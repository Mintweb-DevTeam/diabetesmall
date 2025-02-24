<?php
namespace Controller\Admin\Member;
use Request;
class AgreeListController extends \Controller\Admin\Controller {
	public function index()
	{
		$this->callMenu('member', 'adela', 'agree_list');
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$request = \App::getInstance('request');
		if (!$request->get()->has('page')) $request->get()->set('page', 1);
		if (!$request->get()->has('pageNum')) $request->get()->set('pageNum', 10);
		$pageObject = new \Component\Page\Page( $request->get()->get('page'), 0, 0, $request->get()->get('pageNum') );
		$pageObject->setUrl($request->getQueryString());
		$sql_ = '';
		if($request->get()->has('keyword'))
			$sql_ = ' WHERE `'.$request->get()->get('key').'` LIKE \'%'.$request->get()->get('keyword').'%\' ';
		$total = $this->db->query_fetch('SELECT COUNT(*) AS `cnt` FROM `co_aboottAgree` '.$sql_, true)[0]['cnt'];
		$pageObject->setTotal( $total );
		$pageObject->setCache(true);
		$amount = $this->db->query_fetch('SELECT COUNT(*) AS `cnt` FROM `co_aboottAgree`', true)[0]['cnt'];
		$pageObject->setAmount( $amount );
		$limit = ($request->get()->get('page') - 1) * $request->get()->get('pageNum');
		$sql = 'SELECT * FROM `co_aboottAgree` '.$sql_.' ORDER BY `sno` DESC LIMIT '.$limit.', '.$request->get()->get('pageNum');
		$data = $this->db->query_fetch($sql, true);
		$pageObject->setPage();
		$this->setData('get', $request->get()->all());
		$this->setData('data', $data);
		$this->setData('page', $pageObject);
	}
}