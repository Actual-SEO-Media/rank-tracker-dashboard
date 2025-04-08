<?php
namespace App\Configs;

/**
 * Session Management Class - Simplified for stable session ID
 */
class Session {
    private static $instance = null;

    /**
     * Private constructor
     */
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
            'lifetime' => 86400,  // 1 day
            'path' => '/',
            'domain' => '', // Use default domain for local development
            'secure' => false,  // Set to false for non-HTTPS
            'httponly' => true,  // Prevents JavaScript from accessing the session cookie
            'samesite' => 'Lax'  // Ensures cookie is sent with cross-site requests
        ]);


            // Start session
            session_start();

            // Initialize last activity if not set
            if (!isset($_SESSION['last_activity'])) {
                $_SESSION['last_activity'] = time();
            }

            // Check for session timeout (e.g., 30 minutes)
            if (time() - $_SESSION['last_activity'] > 1800) {
                $this->destroy(); // Session expired, destroy it
            }

            $_SESSION['last_activity'] = time(); // Update last activity timestamp
        }

        // Initialize flash messages array if not set
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set a session variable
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     */
    public function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if a session variable exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Clear all session data
     */
    public function clear() {
        session_unset();
    }

    /**
     * Destroy the session
     */
    public function destroy() {
        session_unset();
        session_destroy();
    }

    /**
     * Regenerate the session ID - use only during login
     */
    public function regenerateId() {
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Set a flash message
     */
    public function setFlash($key, $message) {
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Get a flash message
     */
    public function getFlash($key, $default = null) {
        if (isset($_SESSION['_flash'][$key])) {
            $message = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $message;
        }
        return $default;
    }

    /**
     * Check if a flash message exists
     */
    public function hasFlash($key) {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_role']) && 
               in_array($_SESSION['user_role'], ['admin', 'user']);
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Login a user
     */
    public function login($userId, $username, $role) {
        $this->set('user_id', $userId);
        $this->set('username', $username);
        $this->set('user_role', $role);
        $this->set('logged_in', true);
        $this->set('last_activity', time());

        // Only regenerate ID during login
        $this->regenerateId();
    }

    /**
     * Logout a user
     */
    public function logout() {
        $this->destroy();
    }
}