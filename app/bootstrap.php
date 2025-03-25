<!-- <?php
/**
 * Application Bootstrap File
 * 
 * Handles the initial loading of the application environment,
 * configuration, and other foundational components.
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
    
    // $dotenv->required([
    //     'DB_HOST', 
    //     'DB_NAME', 
    //     'DB_USER', 
    //     'DB_PASS'
    // ])->notEmpty();
    
    // $dotenv->required('SESSION_SECURE')->isBoolean();
    // $dotenv->required('SESSION_TIMEOUT')->isInteger();
}

$config = \App\Configs\Config::getInstance();

if (!defined('DB_HOST')) define('DB_HOST', $config->get('db_host'));
if (!defined('DB_NAME')) define('DB_NAME', $config->get('db_name'));
if (!defined('DB_USER')) define('DB_USER', $config->get('db_user'));
if (!defined('DB_PASS')) define('DB_PASS', $config->get('db_pass'));
if (!defined('BASE_URL')) define('BASE_URL', $config->get('base_url'));
if (!defined('SITE_URL')) define('SITE_URL', $config->get('site_url'));
if (!defined('AUTH_TIMEOUT')) define('AUTH_TIMEOUT', $config->get('auth_timeout'));

return $config;