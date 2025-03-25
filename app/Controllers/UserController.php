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

    /**
     * Show the login form
     */
    public function showLogin() {
        // Check if user is already logged in
        if ($this->session->isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Include the login view
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Process login form submission
     */
    public function login() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Get login credentials
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
        $password = $_POST['password'] ?? '';
        
        error_log("Login attempt for username: $username");
        
        // Basic validation
        if (empty($username) || empty($password)) {
            error_log("Login validation failed: Empty username or password");
            $this->session->setFlash('login_error', "Username and password are required");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if ($user && $userModel->verifyPassword($user, $password)) {
                error_log("Password verification successful for user: $username");
                
                // Set login session
                $this->session->login($user['id'], $user['username'], $user['role']);
                
                // Log session information for debugging
                error_log("User logged in successfully. Session data: " . print_r($_SESSION, true));
                
                // Redirect to dashboard or requested page
                $redirectUrl = $this->session->get('redirect_url') ?: BASE_URL;
                $this->session->remove('redirect_url'); // Clear the redirect URL
                
                error_log("Redirecting to: $redirectUrl");
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Login failed
                error_log("Login failed for username: $username - Invalid username or password");
                $this->session->setFlash('login_error', "Invalid username or password");
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->session->setFlash('login_error', "An error occurred during login. Please try again later.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Process user logout
     */
    public function logout() {
        error_log("Logout requested. Session before logout: " . print_r($_SESSION, true));
        $this->session->logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    /**
     * Static method to check if a user is logged in
     */
    public static function isLoggedIn() {
        $session = Session::getInstance();
        return $session->isLoggedIn();
    }
    
    /**
     * Static method to check if user is admin
     */
    public static function isAdmin() {
        $session = Session::getInstance();
        return $session->isAdmin();
    }
    
    /**
     * Static method to require admin role
     * Redirects to login page if not admin
     */
    public static function requireAdmin() {
        $session = Session::getInstance();
        if (!$session->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}