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

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
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

    public function validateSessionData() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function requireValidSession() {
        if (!$this->validateSessionData()) {
            $this->destroy();
            header('Location: index.php?action=login');
            exit;
        }
    }

    public function logout() {
        $this->destroy();
        header('Location: index.php?action=login');
        exit;
    }

    private function destroy() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }

    /**
     * Generate and store CSRF token
     * @return string The CSRF token
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validate CSRF token
     * @param string $token The token to validate
     * @return bool Whether the token is valid
     */
    public function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get current CSRF token
     * @return string The current CSRF token
     */
    public function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? '';
    }
} 