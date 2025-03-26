<?php
namespace App\Configs;

/**
 * Session Management Class - Enhanced with configuration integration
 */
class Session {
    private static $instance = null;
    private $config;

    /**
     * Private constructor
     */
    private function __construct() {
        $this->config = Config::getInstance();
        
        // Only start session if not already started and headers haven't been sent
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Get session configuration from Config
            $sessionSecure = $this->config->get('session_secure', false);
            $sessionDomain = $this->config->get('session_domain', '');
            $sessionLifetime = $this->config->get('session_lifetime', 86400);
            $baseUrl = $this->config->get('base_url', '/');
            
            // Configure session parameters
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path' => $baseUrl,
                'domain' => $sessionDomain,
                'secure' => $sessionSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Start session
            session_start();
            
            // Log session ID if in debug mode
            if ($this->config->isDebug()) {
                error_log("Session started/resumed. Session ID: " . session_id());
            }

            // Initialize last activity if not set
            if (!isset($_SESSION['last_activity'])) {
                $_SESSION['last_activity'] = time();
            }

            // Get timeout from configuration
            $authTimeout = $this->config->get('auth_timeout', 3600);
            
            // Check for session timeout
            if (time() - $_SESSION['last_activity'] > $authTimeout) {
                if ($this->config->isDebug()) {
                    error_log("Session expired. Last activity: " . date('Y-m-d H:i:s', $_SESSION['last_activity']));
                }
                $this->destroy(); // Session expired, destroy it
                // Restart session after destroying the old one
                session_start();
            }

            $_SESSION['last_activity'] = time(); // Update last activity timestamp
        } else if ($this->config->isDebug() && headers_sent()) {
            error_log("Cannot start session, headers already sent");
        }

        // Initialize flash messages container if it doesn't exist
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
        if ($this->config->isDebug()) {
            error_log("Destroying session. Session ID: " . session_id());
        }
        session_unset();
        session_destroy();
    }

    /**
     * Regenerate the session ID - use only during login
     */
    public function regenerateId() {
        if (!headers_sent()) {
            $oldSessionId = session_id();
            session_regenerate_id(true);
            if ($this->config->isDebug()) {
                error_log("Session ID regenerated. Old: $oldSessionId, New: " . session_id());
            }
        } else {
            if ($this->config->isDebug()) {
                error_log("Cannot regenerate session ID, headers already sent");
            }
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
        $isLoggedIn = isset($_SESSION['user_id']) && 
                      isset($_SESSION['logged_in']) &&
                      $_SESSION['logged_in'] === true;
        
        if ($this->config->isDebug()) {
            error_log("isLoggedIn check: " . ($isLoggedIn ? 'true' : 'false'));
        }
        return $isLoggedIn;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin() {
        $isAdmin = $this->isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        
        if ($this->config->isDebug()) {
            error_log("isAdmin check: " . ($isAdmin ? 'true' : 'false'));
        }
        return $isAdmin;
    }

    /**
     * Login a user
     */
    public function login($userId, $username, $role) {
        if ($this->config->isDebug()) {
            error_log("Setting up login session for user ID: $userId, username: $username, role: $role");
        }
        
        // Make sure session is started
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        // Regenerate ID first for security
        $this->regenerateId();
        
        // Set session variables
        $this->set('user_id', $userId);
        $this->set('username', $username);
        $this->set('user_role', $role);
        $this->set('logged_in', true);
        $this->set('last_activity', time());
        
        if ($this->config->isDebug()) {
            error_log("Login complete. Session data: " . print_r($_SESSION, true));
        }
        
        return true;
    }

    /**
     * Logout a user
     */
    public function logout() {
        if ($this->config->isDebug()) {
            error_log("Logging out user: " . ($this->has('username') ? $this->get('username') : 'unknown'));
        }
        
        // Make sure session is started before trying to destroy it
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        $this->destroy();
        
        // If we need to start a new session after logout
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}