<?php
namespace App\Configs;

class Session {
    private static $instance = null;
    private $started = false;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1); // Only send cookie over HTTPS
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
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
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}