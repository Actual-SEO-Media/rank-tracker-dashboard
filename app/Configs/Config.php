<?php
namespace App\Configs;
/**
 * Application Configuration Manager
 * 
 * Manages loading and accessing environment-specific configuration
 */
class Config {
    private static $instance = null;
    private $config = [];
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Load environment variables if not already loaded
        if (!getenv('APP_ENV')) {
            if (class_exists('\Dotenv\Dotenv') && file_exists(BASE_PATH . '/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
                $dotenv->load();
            }
        }
        
        // Set application configuration with environment variables
        $this->config = [
            // Base application settings
            'app_env' => getenv('APP_ENV') ?: 'development',
            'app_debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
            
            // URL configuration
            'base_url' => getenv('BASE_URL') ?: '/rank-tracker-dashboard',
            'site_url' => getenv('SITE_URL') ?: 'http://localhost/rank-tracker-dashboard',
            
            // Database settings
            'db_host' => getenv('DB_HOST') ?: 'localhost',
            'db_name' => getenv('DB_NAME') ?: 'asm_seo_reports',
            'db_user' => getenv('DB_USER') ?: 'root',
            'db_pass' => getenv('DB_PASS') ?: '',
            'db_persistent' => filter_var(getenv('DB_PERSISTENT') ?: 'false', FILTER_VALIDATE_BOOLEAN),
            
            // Session configuration
            'session_secure' => filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN),
            'session_domain' => getenv('SESSION_DOMAIN') ?: '',
            'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
            'session_timeout' => (int)(getenv('SESSION_TIMEOUT') ?: 1800),
            'auth_timeout' => (int)(getenv('AUTH_TIMEOUT') ?: 3600),
            
            // File uploads
            'upload_max_size' => (int)(getenv('UPLOAD_MAX_SIZE') ?: 2097152), // 2MB default
            'upload_allowed_types' => explode(',', getenv('UPLOAD_ALLOWED_TYPES') ?: 'csv,xlsx,xls'),
            
            // Error handling
            'error_log_path' => BASE_PATH . '/logs/app-error.log',
        ];
        
        if (!defined('DB_HOST')) define('DB_HOST', $this->config['db_host']);
        if (!defined('DB_NAME')) define('DB_NAME', $this->config['db_name']);
        if (!defined('DB_USER')) define('DB_USER', $this->config['db_user']);
        if (!defined('DB_PASS')) define('DB_PASS', $this->config['db_pass']);
        if (!defined('BASE_URL')) define('BASE_URL', $this->config['base_url']);
        if (!defined('SITE_URL')) define('SITE_URL', $this->config['site_url']);
        if (!defined('AUTH_TIMEOUT')) define('AUTH_TIMEOUT', $this->config['auth_timeout']);
        
        $this->configureErrorHandling();
    }
    
    /**
     * Get the singleton instance
     * 
     * @return Config The configuration instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key The configuration key
     * @param mixed $default Default value if key not found
     * @return mixed The configuration value
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key The configuration key
     * @param mixed $value The configuration value
     * @return Config The configuration instance for chaining
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * Get all configuration values
     * 
     * @return array All configuration values
     */
    public function all() {
        return $this->config;
    }
    
    /**
     * Check if configuration key exists
     * 
     * @param string $key The configuration key
     * @return bool True if key exists
     */
    public function has($key) {
        return isset($this->config[$key]);
    }
    
    /**
     * Configure error handling based on environment
     */
    private function configureErrorHandling() {
        if ($this->config['app_env'] === 'production') {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ALL);
            ini_set('log_errors', 1);
            ini_set('error_log', $this->config['error_log_path']);
            
            // Create logs directory if it doesn't exist
            $logDir = dirname($this->config['error_log_path']);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
        } else {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    }
    
    /**
     * Check if application is in production environment
     * 
     * @return bool True if in production
     */
    public function isProduction() {
        return $this->config['app_env'] === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug is enabled
     */
    public function isDebug() {
        return $this->config['app_debug'];
    }
}