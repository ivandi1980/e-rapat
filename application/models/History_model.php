<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_model extends CI_Model
{
    protected $table   = 'view_user_meeting';
    protected $user   = 'view_user_department';

    public function get_all_history_meeting($where)
    {
        return $this->db->get_where($this->table, ['email' => $where])->result_array();
    }

    public function get_all_history_meeting_by_daterange($where, $role)
    {

        $condition = "date_issues BETWEEN " . "'" . $where['from_date'] . "'" . " AND " . "'" . $where['to_date'] . "'" . " AND " . "role_id = " . $role;
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where($condition);
        return $this->db->get()->result_array();
    }

    public function get_all_history_meeting_by_daterange_admin($where)
    {

        $condition = "date_issues BETWEEN " . "'" . $where['from_date'] . "'" . " AND " . "'" . $where['to_date'] . "'";
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where($condition);
        return $this->db->get()->result_array();
    }
}
