<?php
namespace App\Controllers;

use App\Models\User;
use App\Configs\Session;

class UserController {
    private $userModel;
    private $session;

    public function __construct() {
        $this->userModel = new User();
        $this->session = Session::getInstance();
    }

    /**
     * Display login page
     */
    public function login() {
        // Redirect if already logged in
        if ($this->session->get('user_role')) {
            header('Location: index.php');
            exit;
        }

        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password.';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    $this->session->set('user_role', $user['role']);
                    $this->session->set('user_id', $user['id']);
                    $this->session->set('username', $user['username']);
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Handle user logout
     */
    public function logout() {
        $this->session->logout();
    }

} 