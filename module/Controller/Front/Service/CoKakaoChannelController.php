<?php
namespace Controller\Front\Service;
use Request;
class CoKakaoChannelController extends \Controller\Front\Controller
{
	public function index()
	{
		if (!is_object($this->db)) $this->db = \App::load('DB');
		$post = Request::post()->all();
		$table = ( $post['type'] == 'shop' ) ? 'es_member' : 'co_abbottMember';
		foreach( $post as $key => $val ){
			if( $key != 'type' ){
				if( $val ) $where[] = ' `'.$key.'` = \''.$val.'\' ';
			}
		}
		$sql = 'UPDATE `'.$table.'` SET `coKakaoChannel` = 1 WHERE'.implode('AND', $where);
		$result = $this->db->query($sql);
		$this->json($result);
	}
}
