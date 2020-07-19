<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->library('form_validation');
  }

  public function index()
  {
    if ($this->session->userdata('email')) {
      redirect('user');
    }

    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
    $this->form_validation->set_rules('password', 'Password', 'required|trim');

    if ($this->form_validation->run() == false) {
      $data['title'] = 'E-RAPAT | Login';

      $this->load->view('layout/auth_header', $data);
      $this->load->view('auth/login');
      $this->load->view('layout/auth_footer');
    } else {
      // Give an underscore to indicate that the method is private
      $this->_login();
    }
  }

  // login function goes here
  private function _login()
  {
    $email = $this->input->post('email');
    $password = $this->input->post('password');

    $user = $this->db->get_where('meeting_users', ['email' => $email])->row_array();
    // var_dump($user);
    // die;
    if ($user) {
      if ($user['is_active'] == 1) {
        if (password_verify($password, $user['password'])) {
          $data = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
          ];
          // var_dump($data);
          // die;
          $this->session->set_userdata($data);
          if ($user['role_id'] == 1) {

            redirect('admin');
          } else {

            redirect('user');
          }
        } else {
          $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Login Failed!</strong> You entered a wrong password!.</div>');
          redirect('auth');
        }
      } else {
        $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Login Failed!</strong> Your account has been blocked!.</div>');
        redirect('auth');
      }
    } else {
      $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Login Failed!</strong> Your account isn\'t registered!.</div>');
      redirect('auth');
    }
  }

  // public function registration()
  // {
  //   $this->form_validation->set_rules('name', 'Name', 'required|trim');
  //   $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[meeting_users.email]', [
  //     "is_unique" => "This Email already registered!"
  //   ]);
  //   $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2], [
  //           "matches" => "Passwords do not match!",
  //           "min_length" => "Password too short"
  //       ]');
  //   $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

  //   if ($this->form_validation->run() == false) {

  //     $data['title'] = 'E-MEETING | Registration';

  //     $this->load->view('layout/auth_header', $data);
  //     $this->load->view('auth/registration');
  //     $this->load->view('layout/auth_footer');
  //   } else {

  //     $data = [
  //       'name' => htmlspecialchars($this->input->post('name', true)),
  //       'email' => htmlspecialchars($this->input->post('email', true)),
  //       'image' => "default-avatar.jpg",
  //       'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
  //       'role_id' => 2,
  //       'is_active' => 1,
  //       'date_created' => time()
  //     ];

  //     $this->db->insert('meeting_users', $data);
  //     $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
  //           <strong>Congradulation!</strong> Your account has been created!.</div>');
  //     redirect('auth/registration');
  //   }
  // }

  private function _sendemail($token, $type)
  {
    $config = [
      'protocol' => 'smtp',
      'smtp_host' => 'ssl://smtp.googlemail.com',
      'smtp_user' => 'your_username',
      'smtp_pass' => 'your_password',
      'smtp_port' => '465',
      'mailtype' => 'html',
      'charset' => 'utf-8',
      'newline' => '\r\n',
    ];

    $this->email->initialize($config);
    $this->email->from('your_username@gmail.com', 'e-meeting Administrator');
    $this->email->to($this->input->post('email'));

    if ($type == 'verify') {
      $this->email->subject('Account Verification');
      $this->email->message('Please click this link to Verify your Account : <a href = "' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate my Account now!</a>');
    } else if ($type == 'forgot') {
      $this->email->subject('Reset your e-meeting Password');
      $this->email->message('Please click this link to Reset your your e-meeting Password : <a href = "' . base_url() . 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset my Password now!</a>');
    }

    if ($this->email->send()) {
      return true;
    } else {
      echo $this->email->print_debugger();
      die;
    }
  }

  public function verify()
  {
    $email = $this->input->get('email');
    $token = $this->input->get('token');

    $user = $this->db->get_where('meeting_users', ['email' => $email])->row_array();

    if ($user) {
      $user_token = $this->db->get_where('meeting_users_token', ['token' => $token])->row_array();
      if ($user_token) {
        if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
          // update using codeigniter query builder
          $this->db->set('is_active', 1);
          $this->db->where('email', $email);
          $this->db->update('meeting_users');
          $this->db->delete('meeting_users_token', ['email' => $email]);

          $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Congradulation!</strong> ' . $email . ' has been Activate!, please login.</div>');
          redirect('auth');
        } else {
          $this->db->delete('meeting_users', ['email' => $email]);
          $this->db->delete('meeting_users_token', ['email' => $email]);
          $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Error!</strong> Account Activation Failed!, token expired!.</div>');
          redirect('auth');
        }
      } else {
        $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>Error!</strong> Account Activation Failed!, invalid token!.</div>');
        redirect('auth');
      }
    } else {
      $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Account Activation Failed!, wrong email!.</div>');
      redirect('auth');
    }
  }

  public function register()
  {
    $data['title'] = 'E-RAPAT | Registration';

    $this->load->view('layout/auth_header', $data);
    $this->load->view('auth/register');
    $this->load->view('layout/auth_footer');
  }

  // function restricted access menu
  public function blocked()
  {
    // echo 'Access Denied!';
    $this->load->view('auth/blocked');
  }

  // function logout 
  public function logout()
  {
    $this->session->unset_userdata('email');
    $this->session->unset_userdata('role_id');

    $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Congradulation!</strong> Logged out Success!.</div>');

    redirect('auth');
  }

  public function forgotPassword()
  {
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    if ($this->form_validation->run() == false) {

      $data['title'] = 'E-RAPAT | Forgot Password';
      $this->load->view('layout/auth_header', $data);
      $this->load->view('auth/forgot-password');
      $this->load->view('layout/auth_footer');
    } else {
      $email = $this->input->post('email');
      $user = $this->db->get_where('meeting_users', ['email' => $email, 'is_active' => 1])->row_array();

      if ($user) {
        $token = base64_encode(random_bytes(32));
        $user_token = [
          'email' => $email,
          'token' => $token,
          'date_created' => time()
        ];

        $this->db->insert('meeting_users_token', $user_token);
        $this->_sendemail($token, 'forgot');

        $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Congradulation!</strong> Please check your email ' . $email . ' to Reset your Password!.</div>');
        redirect('auth/forgotpassword');
      } else {
        $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Email is not Registered or not being Activated!.</div>');
        redirect('auth/forgotpassword');
      }
    }
  }

  public function changePassword()
  {

    // prevent user can access this method directly
    if ($this->session->userdata('reset_email')) {
      redirect('auth');
    }

    $this->form_validation->set_rules('password1', 'Password', 'trim|required|min_length[3]|matches[password2]');
    $this->form_validation->set_rules('password1', 'Repeat Password', 'trim|required|min_length[3]|matches[password1]');

    if ($this->form_validation->run() == false) {

      $data['title'] = 'E-RAPAT | Change Password';
      $this->load->view('layout/auth_header', $data);
      $this->load->view('auth/change-password');
      $this->load->view('layout/auth_footer');
    } else {
      $password = password_hash($this->input->post('password1'), PASSWORD_DEFAULT);
      $session_email = $this->session->userdata('reset_email');

      $this->db->set('password', $password);
      $this->db->where('email', $session_email);
      $this->db->update('meeting_users');

      // reset of delete session
      $this->session->unset_userdata('reset_email');

      $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Congradulation!</strong> Your Password has been changed!, Please login.</div>');
      redirect('auth');
    }
  }

  public function resetpassword()
  {
    $email = $this->input->get('email');
    $token = $this->input->get('token');

    $user = $this->db->get_where('meeting_users', ['email' => $email])->row_array();

    if ($user) {
      $user_token = $this->db->get_where('meeting_users_token', ['token' => $token])->row_array();
      if ($user_token) {
        if (time() - $user_token['date_created'] < (60 * 60 * 24)) {

          // create session for change password
          $this->session->set_userdata('reset_email', $email);
          // call change password method after this session activated
          $this->changePassword();
          $this->db->delete('meeting_users_token', ['email' => $email]);

          $this->session->set_flashdata('messages', '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Congradulation!</strong> ' . $email . ' has been Reset!, please login.</div>');
          redirect('auth');
        } else {
          $this->db->delete('meeting_users', ['email' => $email]);
          $this->db->delete('meeting_users_token', ['email' => $email]);
          $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Error!</strong> Reset password failed!, token expired!.</div>');
          redirect('auth');
        }
      } else {
        $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Reset password failed!, wrong token.</div>');
        redirect('auth');
      }
    } else {
      $this->session->set_flashdata('messages', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Reset password failed!, wrong email.</div>');
      redirect('auth');
    }
  }
}
