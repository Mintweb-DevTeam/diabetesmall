<?php

namespace Component\Wm;

class Wm
{
    protected $db = null;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    public function getToken($memNo)
    {
        $this->db->strField = "uuid";
        $this->db->strWhere = "memNo = '" . \Encryptor::decrypt($memNo) . "'";
        $query = $this->db->query_complete();
        $sql = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER_SNS . implode(' ', $query);
        return $this->db->fetch($sql);
    }
}