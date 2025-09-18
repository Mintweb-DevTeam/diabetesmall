<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */
namespace Component\Member;

/**
 * 회원 테이블 데이터 처리 클래스
 * @package Bundle\Component\Member
 * @author  yjwee
 * @method static MemberDAO getInstance
 */
class MemberDAO extends \Bundle\Component\Member\MemberDAO
{

    public function selectListBySearch(array $params)
	{
		//2021.09.16민트웹 수정 -상속처리안됨
		$this->setQuerySearch($params, $arrBind);
		if ($params['offset'] !== null && $params['limit'] !== null) {
			$this->db->strLimit = ($params['offset'] - 1) * $params['limit'] . ', ' . $params['limit'];
		}
		$arrQuery = $this->db->query_complete();

		if($params['subscription']==1){
			$now_date=date("Y-m-d");
			$sql=" and (SELECT count(a.memNo) as cnt FROM wm_subscription_apply a INNER JOIN `wm_subscription_schedule_list` b ON a.uid=b.uid WHERE b.isStop='0' and a.memNo=m.memNo and from_unixtime(b.schedule_stamp,'%Y-%m-%d')>='$now_date')>='1' ";

			$arrQuery['where'].=$sql;//" and (select count(ss.memNo) from wm_subscription_apply ss where ss.memNo=m.memNo)>='1'";
		}

        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== START
        if(!empty($params['linkFl'])) {
            $wmSalesforce = new \Component\Wm\WmSalesforce();
            if ($wmSalesforce->applyFl) {
                $searchData = $wmSalesforce->getSearchData($params, 'member');
                if(!empty($searchData)) {
                    $arrQuery['field'] .= $searchData['field'];
                    $arrQuery['left'] .= $searchData['left'];
                    $arrQuery['where'] .= ' and ' . $searchData['where'];
                }
            }
        }
        // 웹앤모바일 세일즈포스 데이터 연동 ================================================== END

		$strSQL = 'SELECT  ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);
		$data =  $this->db->query_fetch($strSQL, $arrBind);
		return $data;
		
	}
}