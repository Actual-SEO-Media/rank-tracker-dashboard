<?php
namespace App\Configs;

class Session {
    private static $instance = null;
    private $started = false;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            
            // Only set secure cookie if using HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', AUTH_TIMEOUT); // Use the timeout from config
            
            session_start();
            
            $this->started = true;
            
            // Check if session has expired
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > AUTH_TIMEOUT)) {
                // Session expired, destroy it
                session_unset();
                session_destroy();
                session_start();
            }
            
            // Update last activity time stamp
            $_SESSION['last_activity'] = time();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'user']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function regenerateId() {
        if ($this->started) {
            session_regenerate_id(true);
        }
    }
    
    public function destroy() {
        if ($this->started) {
            session_unset();
            session_destroy();
            $this->started = false;
        }
    }
    
    public function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }
    
    public function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
    
    public function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }

    public function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
}
}