<?php
namespace Controller\Admin\Member;
use Request;
use Bundle\Component\Member\HackOut\HackOutService;
class CossiaAjaxController extends \Controller\Admin\Controller {
    public function index(){
		if (!is_object($this->db)) $this->db = \App::load('DB');
        $post = Request::post()->all();
		switch($post['type']){
			case 'check_pharm_code' :
				$sql = 'SELECT COUNT(*) AS `cnt` FROM `co_pharmacy` WHERE `code` = \''.$post['code'].'\'';
				$cnt = $this->db->query_fetch($sql, true);
				$data = $cnt[0];
				
			break;
			case 'mandate_del' :
				$sql = 'DELETE FROM `co_mandate` WHERE `sno` IN ( '.implode(', ', $post['anos']).' )';
				$this->db->query($sql);
			break;
			case 'agree_del' :
				$sql = 'DELETE FROM `co_aboottAgree` WHERE `sno` IN ( '.implode(', ', $post['anos']).' )';
				$this->db->query($sql);
			break;
			case 'del_abotte_mem' :
				$out = new HackOutService;
				foreach($post['snos'] as $row){
					$sql = 'DELETE FROM `co_abbottMember` WHERE `sno` = '.$row;
					$data = $this->db->query($sql);
					$sql = 'DELETE FROM `es_member` WHERE `abbott_sno` = '.$row;
					$this->db->query($sql);
				}
			break;
			case 'send_to_member' :
				$cossia = new \Component\Cossia\Mail;
				$data = ($post['send_methode'] === 'sms') ? $cossia->admin_sms($post) : $cossia->admin_mail($post);
			break;
			case 'pharmacy_mem' :
				$sql = 'SELECT `memNm`, `cellPhone`, `email`, `coKakaoChannel`, `regDt` FROM `co_abbottMember` WHERE `pharmacy_code` = \''.$post['code'].'\' AND DATE(`regDt`) >= \''.$post['sdate'].'\' AND DATE(`regDt`) <= \''.$post['edate'].'\'';
				$data = $this->db->query_fetch($sql, true);
			break;
		}
		$this->json($data);
    }
}