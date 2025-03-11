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
        // Just include the login template directly for now
        include __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        // Direct debug output
        echo "<div style='padding: 20px; font-family: monospace; background: #f5f5f5;'>";
        echo "<h2>Login Debug Information</h2>";
        echo "<p>Login method called</p>";
        echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
        echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "<p style='color: red;'>Not a POST request</p>";
            return;
        }
        
        // Log received data
        echo "<h3>POST Data:</h3>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        echo "<h3>BASE_URL:</h3>";
        echo "<p>" . BASE_URL . "</p>";
        
        $username = trim(htmlspecialchars($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        
        try {
            echo "<h3>Database Connection:</h3>";
            try {
                $database = Database::getInstance();
                echo "<p style='color: green;'>✓ Database connection successful</p>";
            } catch (\Exception $e) {
                echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
                throw $e;
            }
            
            echo "<h3>User Lookup:</h3>";
            $userModel = new User();
            echo "<p>Looking up user: " . $username . "</p>";
            
            try {
                $user = $userModel->findByUsername($username);
                if ($user) {
                    echo "<p style='color: green;'>✓ User found</p>";
                    echo "<p>User data (excluding password):</p>";
                    $userDataToShow = $user;
                    unset($userDataToShow['password']); // Don't show password hash
                    echo "<pre>" . print_r($userDataToShow, true) . "</pre>";
                    
                    if ($userModel->verifyPassword($user, $password)) {
                        echo "<p style='color: green;'>✓ Password verified successfully</p>";
                        
                        // Set session variables using Session class
                        $this->session->set('user_id', $user['id']);
                        $this->session->set('username', $user['username']);
                        $this->session->set('user_role', $user['role']);
                        $this->session->set('logged_in', true);
                        $this->session->set('last_activity', time());
                        
                        // Regenerate session ID for security
                        $this->session->regenerateId();
                        
                        echo "<h3>Session Variables Set</h3>";
                        echo "<p>Click to continue: <a href='" . BASE_URL . "' style='color: blue;'>Go to Dashboard</a></p>";
                        return;
                    } else {
                        echo "<p style='color: red;'>✗ Password verification failed</p>";
                        echo "<p>Debug: Password received length: " . strlen($password) . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ User not found: " . $username . "</p>";
                }
            } catch (\Exception $e) {
                echo "<p style='color: red;'>✗ Error during user lookup: " . $e->getMessage() . "</p>";
                throw $e;
            }
            
            // Login failed
            $this->session->setFlash('login_error', "Invalid username or password");
            echo "<h3 style='color: red;'>Login Failed</h3>";
            echo "<p>Click to try again: <a href='" . BASE_URL . "/login' style='color: blue;'>Back to Login</a></p>";
            
        } catch (\Exception $e) {
            echo "<h3 style='color: red;'>Error Details:</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            
            $this->session->setFlash('login_error', "An error occurred during login. Please try again later.");
            echo "<p>Click to try again: <a href='" . BASE_URL . "/login' style='color: blue;'>Back to Login</a></p>";
        }
        echo "</div>";
    }

    public function logout() {
        $this->session->destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    // Static method to check if a user is logged in
    public static function isLoggedIn() {
        $session = Session::getInstance();
        return $session->isLoggedIn();
    }
    
    // Static method to check if a user is an admin
    public static function isAdmin() {
        $session = Session::getInstance();
        return $session->isAdmin();
    }
    
    // Middleware to require admin access
    public static function requireAdmin() {
        $session = Session::getInstance();
        if (!$session->isAdmin()) {
            $session->setFlash('login_error', 'You must be logged in as an admin to access this page');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}