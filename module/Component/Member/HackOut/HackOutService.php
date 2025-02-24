<?php
namespace Component\Member\HackOut;
class HackOutService extends \Bundle\Component\Member\HackOut\HackOutService
{
	public function __construct($config = [])
    {
		parent::__construct($config);
		if (!is_object($this->db)) $this->db = \App::load('DB');
    }
	public function hackOutByMemberList(array $memNos)
    {
		foreach ($memNos as $index => $memNo) {
			$sql = 'SELECT `abbott_sno` FROM `es_member` WHERE `memNo` = '.$memNo;
			$sno = $this->db->query_fetch($sql, true);
			if($sno[0]['abbott_sno']){
				$sql = 'UPDATE `co_abbottMember` SET `isJoin` = \'n\' WHERE `sno` = '.$sno[0]['abbott_sno'];
				$this->db->query($sql);
			}
		}
		parent::hackOutByMemberList($memNos);
	}
	
}