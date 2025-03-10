<?php
namespace App\Configs;

class Session {
    private static $instance = null;
    private $started = false;

    private function __construct() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Set session name
        session_name('RANK_TRACKER_SESSION');
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
            
            // Regenerate session ID periodically to prevent session fixation
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerateSession();
            } else if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                $this->regenerateSession();
            }
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

    public function clear() {
        session_unset();
        session_destroy();
        $this->started = false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    private function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    public function setFlashMessage($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }

    public function getFlashMessage($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
} 