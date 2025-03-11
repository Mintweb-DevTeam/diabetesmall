<?php

namespace Component\Wm;

/**
 * Class KakaoJoin
 * @package Component\Wm
 *
 * 웹앤모바일 튜닝 21-03-16 카카오 싱크 회원가입 이메일 중복 우선적으로 확인
 */
class KakaoJoin
{
    protected $db = null;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    // 카카오 싱크 회원가입 이메일 검색
    public function getEmail($email)
    {
        $this->db->strWhere = "email = '" . $email . "'";
        $query = $this->db->query_complete();
        $sql = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER . implode(' ', $query);
        return $this->db->fetch($sql);
    }
}