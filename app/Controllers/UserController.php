<?php
namespace App\Controllers;

use App\Configs\Config;
use App\Configs\Session;
use App\Models\User;

class UserController {
    private $session;
    private $config;

    public function __construct() {
        $this->session = Session::getInstance();
        $this->config = Config::getInstance();
    }

    /**
     * Show the login form
     */
    public function showLogin() {
        // Check if user is already logged in
        if ($this->session->isLoggedIn()) {
            header('Location: ' . $this->config->get('base_url'));
            exit;
        }
        
        // Generate CSRF token for the form
        $csrfToken = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrfToken);
        
        // Include the login view
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Process login form submission
     */
    public function login() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->config->get('base_url') . '/login');
            exit;
        }
        
        // Verify CSRF token
        $submittedToken = $_POST['csrf_token'] ?? '';
        $storedToken = $this->session->get('csrf_token');
        
        if (empty($submittedToken) || $submittedToken !== $storedToken) {
            if ($this->config->isDebug()) {
                error_log("CSRF token validation failed. Submitted: $submittedToken, Stored: $storedToken");
            }
            $this->session->setFlash('login_error', "Invalid request. Please try again.");
            header('Location: ' . $this->config->get('base_url') . '/login');
            exit;
        }
        
        // Clear the token after use
        $this->session->remove('csrf_token');
        
        // Implement rate limiting
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $loginAttempts = $this->session->get('login_attempts_' . $ipAddress, 0);
        $loginTimeout = $this->session->get('login_timeout_' . $ipAddress, 0);
        
        // If too many attempts, block for a period
        if ($loginTimeout > time()) {
            $waitTime = $loginTimeout - time();
            $this->session->setFlash('login_error', "Too many login attempts. Please try again in {$waitTime} seconds.");
            header('Location: ' . $this->config->get('base_url') . '/login');
            exit;
        }
        
        // Get login credentials
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
        $password = $_POST['password'] ?? '';
        
        if ($this->config->isDebug()) {
            error_log("Login attempt for username: $username");
        }
        
        // Basic validation
        if (empty($username) || empty($password)) {
            if ($this->config->isDebug()) {
                error_log("Login validation failed: Empty username or password");
            }
            $this->trackLoginAttempt($ipAddress, false);
            $this->session->setFlash('login_error', "Username and password are required");
            header('Location: ' . $this->config->get('base_url') . '/login');
            exit;
        }
        
        try {
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if ($user && $userModel->verifyPassword($user, $password)) {
                if ($this->config->isDebug()) {
                    error_log("Password verification successful for user: $username");
                }
                
                // Reset login attempts on success
                $this->trackLoginAttempt($ipAddress, true);
                
                // Set login session
                $this->session->login($user['id'], $user['username'], $user['role']);
                
                if ($this->config->isDebug()) {
                    error_log("User logged in successfully. Session data: " . print_r($_SESSION, true));
                }
                
                // Redirect to dashboard or requested page
                $redirectUrl = $this->session->get('redirect_url') ?: $this->config->get('base_url');
                $this->session->remove('redirect_url'); // Clear the redirect URL
                
                // Validate redirect URL to prevent open redirect vulnerabilities
                if (!$this->isValidRedirectUrl($redirectUrl)) {
                    $redirectUrl = $this->config->get('base_url');
                }
                
                if ($this->config->isDebug()) {
                    error_log("Redirecting to: $redirectUrl");
                }
                
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Login failed
                if ($this->config->isDebug()) {
                    error_log("Login failed for username: $username - Invalid username or password");
                }
                $this->trackLoginAttempt($ipAddress, false);
                $this->session->setFlash('login_error', "Invalid username or password");
                header('Location: ' . $this->config->get('base_url') . '/login');
                exit;
            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->session->setFlash('login_error', "An error occurred during login. Please try again later.");
            header('Location: ' . $this->config->get('base_url') . '/login');
            exit;
        }
    }

    /**
     * Process user logout
     */
    public function logout() {
        if ($this->config->isDebug()) {
            error_log("Logout requested. Session before logout: " . print_r($_SESSION, true));
        }
        $this->session->logout();
        header('Location: ' . $this->config->get('base_url') . '/login');
        exit;
    }
    
    /**
     * Track login attempts for rate limiting
     */
    private function trackLoginAttempt($ipAddress, $success) {
        if ($success) {
            $this->session->remove('login_attempts_' . $ipAddress);
            $this->session->remove('login_timeout_' . $ipAddress);
            return;
        }
        
        $attempts = $this->session->get('login_attempts_' . $ipAddress, 0) + 1;
        $this->session->set('login_attempts_' . $ipAddress, $attempts);
        
        // Set timeout after too many failed attempts
        if ($attempts >= 5) {
            $timeout = time() + 300; // 5 minutes lockout
            $this->session->set('login_timeout_' . $ipAddress, $timeout);
        }
    }
    
    /**
     * Validate redirect URL to prevent open redirect vulnerabilities
     */
    private function isValidRedirectUrl($url) {
        // Make sure it's a local URL
        if (substr($url, 0, 1) !== '/') {
            return false;
        }
        
        // No protocol-relative URLs
        if (substr($url, 0, 2) === '//') {
            return false;
        }
        
        // Make sure there are no encoded characters that could be part of a protocol handler
        if (strpos($url, '%') !== false) {
            return false;
        }
        
        return true;
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
        $config = Config::getInstance();
        $session = Session::getInstance();
        
        if (!$session->isAdmin()) {
            header('Location: ' . $config->get('base_url') . '/login');
            exit;
        }
    }
}