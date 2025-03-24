<?php

namespace App\Controllers;

use App\Configs\Database;
use App\Configs\Session;
use App\Models\User;

class UserController {
    private $session;

    public function __construct() {
        $this->session = Session::getInstance();
    }

    public function showLogin() {
        include __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Get login credentials
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($username) || empty($password)) {
            $this->session->setFlash('login_error', "Username and password are required");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if ($user && $userModel->verifyPassword($user, $password)) {
                // Set login session
                $this->session->login($user['id'], $user['username'], $user['role']);
                
                // Redirect to dashboard or requested page
                $redirectUrl = $this->session->get('redirect_url') ?: BASE_URL;
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Login failed
                $this->session->setFlash('login_error', "Invalid username or password");
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        } catch (\Exception $e) {
            $this->session->setFlash('login_error', "An error occurred during login. Please try again later.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function logout() {
        $this->session->logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    // Static method to check if a user is logged in
    public static function isLoggedIn() {
        $session = Session::getInstance();
        return $session->isLoggedIn();
    }
}