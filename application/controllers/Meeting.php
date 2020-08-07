<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Meeting extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->helper(array('string', 'text'));
        $this->load->model('Account_model');
        $this->load->model('Meeting_model');
        $this->load->model('Type_model');
    }

    public function index()
    {
        $data['title'] = 'Meeting';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting_by_role($this->session->userdata('role_id'));
        // $data['place'] = $this->db->get('meeting_place')->result_array();
        $data['types'] = $this->Type_model->getSubType();

        if ($data['user']['role_id'] == '1') {
            $this->load->view('layout/header', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('layout/topbar', $data);
            $this->load->view('meeting/index', $data);
            $this->load->view('layout/footer');
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('layout/topbar', $data);
            $this->load->view('meeting/userindex', $data);
            $this->load->view('layout/footer');
        }
    }

    public function addmeeting()
    {
        $data['title'] = 'Meeting';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting();
        $data['place'] = $this->db->get('meeting_place')->result_array();

        $this->form_validation->set_rules('place_id', 'Place Name', 'required');
        $this->form_validation->set_rules('agenda', 'Agenda', 'required|trim');

        if ($this->form_validation->run() == false) {
            if ($data['user']['role_id'] == '1') {
                $this->load->view('layout/header', $data);
                $this->load->view('layout/sidebar', $data);
                $this->load->view('layout/topbar', $data);
                $this->load->view('meeting/index', $data);
                $this->load->view('layout/footer');
            } else {
                $this->load->view('layout/header', $data);
                $this->load->view('layout/sidebar', $data);
                $this->load->view('layout/topbar', $data);
                $this->load->view('meeting/userindex', $data);
                $this->load->view('layout/footer');
            }
        } else {

            $a = $this->input->post('speakers_name');
            $b = $this->input->post('participants_name');
            $speakers = implode(',', (array) $a);
            $participants = implode(',', (array) $b);

            $data = [
                'user_id' => $data['user']['id'],
                'place_id' => $this->input->post('place_id', true),
                'speakers_name' => $speakers,
                'members_name' => $participants,
                'unique_code' => uniqid(),
                'agenda' => htmlspecialchars($this->input->post('agenda', true)),
                'date_issues' => $this->input->post('date_issues', true),
                'date_requested' => date('Y-m-d'),
                'start_time' => $this->input->post('start_time', true),
                'end_time' => $this->input->post('end_time', true),
                'request_status' => 0
            ];

            $files_name_upload = $_FILES['file']['name'];

            if ($files_name_upload) {
                $config['allowed_types'] = 'gif|jpg|jpeg|png|bmp|pdf';
                $config['max_size'] = '1024';
                $config['upload_path'] = 'uploads/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('file')) {
                    $new_files_name = $this->upload->data('file_name');
                    $this->db->set('files_upload', $new_files_name);
                } else {
                    echo $this->upload->display_errors();
                }
            }
            $this->Meeting_model->insert_meeting($data);
            $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> New subMenu was Added!</div>');
            redirect('meeting');
        }
    }

    public function detailsmeeting($unique)
    {
        $data['title'] = 'Meeting';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_one_meeting($unique);

        foreach ($data['meeting'] as $pecah) {
            $data['speakers'] = explode(",", $pecah['speakers_name']);
            $data['members'] = explode(",", $pecah['members_name']);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('meeting/details', $data);
        $this->load->view('layout/footer');
    }

    public function editmeeting()
    {
        $id = $this->input->post('id');
        $a = $this->input->post('speakers_name');
        $b = $this->input->post('participants_name');
        $speakers = implode(',', (array) $a);
        $participants = implode(',', (array) $b);

        $data = array(
            'place_id' => $this->input->post('place_id', true),
            'speakers_name' => $speakers,
            'members_name' => $participants,
            'agenda' => htmlspecialchars($this->input->post('agenda', true)),
            'date_issues' => $this->input->post('date_issues', true),
            'start_time' => $this->input->post('start_time', true),
            'end_time' => $this->input->post('end_time', true),
        );

        $this->Meeting_model->update_meeting($id, $data);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> Menu has been Updated!</div>');
        redirect('meeting');
    }

    public function meetingdownload($id)
    {
        $this->load->helper('download');
        $data = $this->Meeting_model->get_meeting_download($id);
        force_download('uploads/' . $data->files_upload, NULL);
    }

    public function updatestatus()
    {
        $id = $this->input->post('id');
        $data = array('request_status' => $this->input->post('request_status'));

        $this->Meeting_model->update_meeting_status($id, $data);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> Status Meeting has been Updated!</div>');
        redirect('meeting');
    }

    public function delete()
    {
        $id = $this->input->post('id');

        $this->Meeting_model->delete_meeting($id);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> Data Meeting has been Deleted!</div>');
        redirect('meeting');
    }
}
