<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Meeting_model extends CI_Model
{
    protected $table   = 'view_user_meeting';
    protected $meeting   = 'meeting';

    public function get_all_meeting()
    {
        $this->db->from($this->table);
        $this->db->order_by("name", "desc");
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_meeting_by_role($role)
    {
        $condition = "role_id = " . $role;
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where($condition);
        return $this->db->get()->result_array();
    }

    public function get_one_meeting($unique)
    {
        $this->db->where('unique_code', $unique);
        return $this->db->get($this->table)->result_array();
    }

    public function insert_meeting($data)
    {
        return $this->db->insert($this->meeting, $data);
    }

    public function update_meeting($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
    }

    public function delete_meeting($id)
    {
        return $this->db->where('id', $id)->delete($this->meeting);
    }

    public function update_meeting_status($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
    }

    public function get_meeting_download($id)
    {
        return $this->db->get_where($this->table, ['files_upload' => $id])->row();
    }
}
