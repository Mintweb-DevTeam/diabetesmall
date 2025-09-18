<?php
namespace Controller\Admin\Member;
class MemberListController extends \Bundle\Controller\Admin\Member\MemberListController
{
	public function index(){
		parent::index();
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$data = $this->getData('data');
		if( is_array($data) )
			foreach( $data as $key => $val ){
				$data_[$key] = $val;
				$sql = 'SELECT `coKakaoChannel` FROM `es_member` WHERE `memNo` = '.$val['memNo'];
				$cuno = $this->db->query_fetch($sql, true)[0];
				$data_[$key]['coKakaoChannel'] = $cuno['coKakaoChannel'];
			}
        $this->setData('data', $data_);

        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== START
        $wmSalesforce = new \Component\Wm\WmSalesforce();
        if ($wmSalesforce->applyFl) {
            $this->setData('wmSalesforce', true);
        }
        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== END
	}
}