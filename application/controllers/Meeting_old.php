<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Meeting extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->helper(array('string', 'text', 'tanggal'));
        $this->load->model('Account_model');
        $this->load->model('Meeting_model');
        $this->load->model('Type_model');
    }

    public function index()
    {
        $data['title'] = 'Master Data Rapat';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting_by_sesi($this->session->userdata('email'));

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
        $data['title'] = 'Master Data Rapat';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting_by_sesi($this->session->userdata('email'));
        $data['alltype'] = $this->Type_model->get_all_type();
        $data['types'] = $this->Type_model->getSubType();


        $this->form_validation->set_rules('agenda', 'Agenda', 'required|trim|xss_clean');
        $this->form_validation->set_rules('participants_name', 'Pimpinan Rapat', 'required|trim|xss_clean');
        $this->form_validation->set_rules('start_date', 'Tanggal Rapat', 'required|is_unique[meeting.start_date]');
        // $this->form_validation->set_rules('start_time', 'Jam Awal Rapat', 'callback_startime_exists|required');
        // $this->form_validation->set_rules('end_time', 'Jam Akhir Rapat', 'callback_startime_exists|required');

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

            $sub_type_id = $this->input->post('meeting_subtype', true);
            $datenow = strtotime(date('Y-m-d'));
            $timenow = strtotime(date("H:i:s"));
            $end_date = strtotime($this->input->post('end_date', true));
            $end_time = strtotime($this->input->post('end_time', true));

            if ($datenow >= $end_date && $timenow >= $end_time) {
                $request_status = 3;
                if ($sub_type_id != '1') {
                    $data = [
                        'user_id' => $data['user']['id'],
                        'sub_type_id' => $sub_type_id,
                        'other_online_id' => htmlspecialchars($this->input->post('other_online_id', true)),
                        'speakers_name' => $speakers,
                        'members_name' => $participants,
                        'unique_code' => uniqid(),
                        'agenda' => htmlspecialchars($this->input->post('agenda', true)),
                        'start_date' => $this->input->post('start_date', true),
                        'end_date' => $this->input->post('start_date', true),
                        'date_requested' =>  date('Y-m-d'),
                        'start_time' => $this->input->post('start_time', true),
                        'end_time' => $this->input->post('end_time', true),
                        'request_status' => $request_status
                    ];
                } else {
                    $data = [
                        'user_id' => $data['user']['id'],
                        'sub_type_id' => $sub_type_id,
                        'speakers_name' => $speakers,
                        'members_name' => $participants,
                        'unique_code' => uniqid(),
                        'agenda' => htmlspecialchars($this->input->post('agenda', true)),
                        'start_date' => $this->input->post('start_date', true),
                        'end_date' => $this->input->post('start_date', true),
                        'date_requested' =>  date('Y-m-d'),
                        'start_time' => $this->input->post('start_time', true),
                        'end_time' => $this->input->post('end_time', true),
                        'request_status' => $request_status
                    ];
                }
            } else {
                $request_status = 0;
                if ($sub_type_id != '1') {
                    $data = [
                        'user_id' => $data['user']['id'],
                        'sub_type_id' => $sub_type_id,
                        'other_online_id' => htmlspecialchars($this->input->post('other_online_id', true)),
                        'speakers_name' => $speakers,
                        'members_name' => $participants,
                        'unique_code' => uniqid(),
                        'agenda' => htmlspecialchars($this->input->post('agenda', true)),
                        'start_date' => $this->input->post('start_date', true),
                        'end_date' => $this->input->post('start_date', true),
                        'date_requested' =>  date('Y-m-d'),
                        'start_time' => $this->input->post('start_time', true),
                        'end_time' => $this->input->post('end_time', true),
                        'request_status' => $request_status
                    ];
                } else {
                    $data = [
                        'user_id' => $data['user']['id'],
                        'sub_type_id' => $sub_type_id,
                        'speakers_name' => $speakers,
                        'members_name' => $participants,
                        'unique_code' => uniqid(),
                        'agenda' => htmlspecialchars($this->input->post('agenda', true)),
                        'start_date' => $this->input->post('start_date', true),
                        'end_date' => $this->input->post('start_date', true),
                        'date_requested' =>  date('Y-m-d'),
                        'start_time' => $this->input->post('start_time', true),
                        'end_time' => $this->input->post('end_time', true),
                        'request_status' => $request_status
                    ];
                }
            }

            if (empty($_FILES['file']['name'])) {
                $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Maaf!</strong> Anda belum mengunggah Undangan Rapat!</div>');
                redirect('meeting', 'refresh');
            } else {


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
                $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Selamat!</strong> Anda berhasil membuat rapat!</div>');
                redirect('meeting', 'refresh');
            }
        }
    }

    public function detailsmeeting($unique)
    {
        $data['title'] = 'Master Data Rapat';
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
            'speakers_name' => $speakers,
            'members_name' => $participants,
            'agenda' => htmlspecialchars($this->input->post('agenda', true)),
        );

        $this->Meeting_model->update_meeting($id, $data);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> Menu has been Updated!</div>');
        redirect('meeting');
    }

    public function uploadnotulen()
    {
        $data['title'] = 'Master Data Rapat';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting();

        $config = array(
            'upload_path' => "uploads/",
            'allowed_types' => "gif|jpg|png|jpeg|pdf",
            'encrypt_name' => false,
            'overwrite' => TRUE,
            'max_size' => "2048000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
        );

        $id = $this->input->post('id');

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('notulen')) {
            $error = array('error' => $this->upload->display_errors());
            $this->load->view('layout/header', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('layout/topbar', $error);
            $this->load->view('meeting/userindex', $data);
            $this->load->view('layout/footer');
        } else {
            $notulensi = $this->upload->data('file_name');
            $this->db->set('files_upload1', $notulensi);

            $this->db->where('id', $id);
            $this->db->update('meeting');
            $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Selamat!</strong> File Notulensi Rapat berhasil di unggah!</div>');
            redirect('meeting');
        }
    }

    public function uploadabsensi()
    {
        $data['title'] = 'Master Data Rapat';
        $data['user'] = $this->Account_model->get_admin($this->session->userdata('email'));
        $data['meeting'] = $this->Meeting_model->get_all_meeting();

        $config = array(
            'upload_path' => "uploads/",
            'allowed_types' => "gif|jpg|png|jpeg|pdf",
            'encrypt_name' => false,
            'overwrite' => TRUE,
            'max_size' => "2048000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
        );

        $id = $this->input->post('id');

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('absensi')) {
            $error = array('error' => $this->upload->display_errors());
            $this->load->view('layout/header', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('layout/topbar', $error);
            $this->load->view('meeting/userindex', $data);
            $this->load->view('layout/footer');
        } else {
            $absensi = $this->upload->data('file_name');
            $this->db->set('files_upload2', $absensi);

            $this->db->where('id', $id);
            $this->db->update('meeting');
            $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Selamat!</strong> File Absensi Rapat berhasil di unggah!</div>');
            redirect('meeting');
        }
    }

    public function undangandownload($id)
    {
        $this->load->helper('download');
        $data = $this->Meeting_model->get_undangan_download($id);
        force_download('uploads/' . $data->files_upload, NULL);
    }

    public function notulendownload($id)
    {
        $this->load->helper('download');
        $data = $this->Meeting_model->get_notulen_download($id);
        force_download('uploads/' . $data->files_upload1, NULL);
    }

    public function absensidownload($id)
    {
        $this->load->helper('download');
        $data = $this->Meeting_model->get_absensi_download($id);
        force_download('uploads/' . $data->files_upload2, NULL);
    }

    public function updatestatus()
    {
        $id = $this->input->post('id');
        $datenow = strtotime(date('Y-m-d'));
        $timenow = strtotime(date("H:i:s"));
        $end_date = strtotime($this->input->post('end_date', true));
        $end_time = strtotime($this->input->post('end_time', true));
        $request_status = '3';

        if ($datenow >= $end_date && $timenow >= $end_time) {
            $data = array(
                'request_status' => $request_status,
                'start_date' => $this->input->post('start_date', true),
                'end_date' => $this->input->post('end_date', true),
                'start_time' => $this->input->post('start_time', true),
                'end_time' => $this->input->post('end_time', true),
                'remark_status' => htmlspecialchars($this->input->post('remark_status', true)),
            );
        } else {
            $data = array(
                'request_status' => $this->input->post('request_status'),
                'start_date' => $this->input->post('start_date', true),
                'end_date' => $this->input->post('end_date', true),
                'start_time' => $this->input->post('start_time', true),
                'end_time' => $this->input->post('end_time', true),
                'remark_status' => htmlspecialchars($this->input->post('remark_status', true)),
            );
        }

        $this->Meeting_model->update_meeting_status($id, $data);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Selamat!</strong> Anda berhasil merubah Status!</div>');
        redirect('meeting');
    }

    public function delete()
    {
        $id = $this->input->post('id');

        $this->Meeting_model->delete_meeting($id);
        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Congradulation!</strong> Data Meeting has been Deleted!</div>');
        redirect('meeting');
    }

    function get_media_meeting()
    {
        $id_type = $this->input->post('id_type');
        $data = $this->Type_model->get_id_type($id_type);
        echo json_encode($data);
    }

    function checkupload()
    {
        $data['meeting'] = $this->Meeting_model->get_all_meeting();
        echo $data['meeting'];
    }

    function startime_exists($key)
    {
        $this->Meeting_model->start_exists($key);
    }
}