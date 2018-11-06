<?php
  class Users extends Controller{

    public function __construct() {
      $this->userModel = $this->model('User');
    }

    public function register(){
      // Check for post
      if($_SERVER['REQUEST_METHOD'] == 'POST') {
        //Process Form

        // Sanitize Post Data

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data =[
          'name' => trim($_POST['name']),
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),
          'confirm_password' => trim($_POST['confirm_password']),
          'name_err' => '',
          'email_err' => '',
          'password_err' => '',
          'confirm_password_err' => ''
        ];

        // Validate Email
        if(empty($data['email'])) {
          $data['email_err'] = 'Please enter email';
        } else {
          // Check email
          if($this->userModel->findUserByEmail($data['email'])){
            $data['email_err'] = 'Email is already taken';
          }
        }

        // Validate Name
        if(empty($data['name'])){
          $data['name_err'] =  'Please enter your name';
        }

        // Validate Password
        if(empty($data['password'])){
          $data['password_err'] = 'Please enter your password';
        } else if(strlen($data['password']) < 6){
          $data['password_err'] = 'Password must be at least 6 characters';
        }

        if(empty($data['confirm_password'])){
          $data['confirm_password_err'] = 'Please confirm password';
        } else {
          if ($data['password'] != $data['confirm_password']){
            $data['confirm_password_err'] = 'Passwords do not match';
          }
        }

        // Make sure errors are empty

        if(empty($data['email_err'])
          && empty($data['name_err'])
            && empty($data['password_err'])
              && empty($data['confirm_password_err'])) {
                // Validated
          $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

          // Register User
          if($this->userModel->register($data)){
            flash('register_success', 'You have registered and can login');
           redirect('users/login');
          } else {
            die('something went wrong');
          }
        } else {
          // Load views
          $this->view('users/register', $data);
        }

      } else {
        // Init Data
        $data =[
          'name' => '',
          'email' => '',
          'password' => '',
          'confirm_password' => '',
          'name_err' => '',
          'email_err' => '',
          'password_err' => '',
          'confirm_password_err'
        ];
        // Load View
        $this->view('users/register', $data);
      }
    }
    public function login(){
      // Check for post
      if($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Sanitize Post Data

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data =[
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),
          'email_err' => '',
          'password_err' => '',
        ];


        // Validate Email
        if(empty($data['email'])) {
          $data['email_err'] = 'Please enter email';
        }

        // Validate Password
        if(empty($data['password'])){
          $data['password_err'] = 'Please enter your password';
        }

        // Check for User/email

        if($this->userModel->findUserByEmail($data['email'])){
          // User Found
        } else {
          // Error
          $data['email_err'] = 'No User Found';
        }


        if(empty($data['email_err'])
          && empty($data['password_err'])) {
          // Validated
          // Check and set logged in user
          $loggedInUser = $this->userModel->login($data['email'], $data['password']);

          if($loggedInUser){
            // Create Session
            $this->createUserSession($loggedInUser);
          } else {
            $data['password_err'] = 'password incorrect';
            $this->view('users/login', $data);
          }
        } else {
          // Load views
          $this->view('users/login', $data);
        }

        //Process Form
      } else {
        // Init Data
        $data =[
          'email' => '',
          'password' => '',
          'email_err' => '',
          'password_err' => '',
        ];

        // Load View

        $this->view('users/login', $data);
      }
    }

    public function createUserSession($user) {
      $_SESSION['user_id'] = $user->id;
      $_SESSION['user_email'] = $user->email;
      $_SESSION['user_name'] = $user->name;
      redirect('posts');
    }

    public function logout() {
      unset($_SESSION['user_id']);
      unset($_SESSION['user_email']);
      unset($_SESSION['user_name']);
      session_destroy();
      redirect('users/login');
    }

  }