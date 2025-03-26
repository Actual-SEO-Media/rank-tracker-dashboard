<?php
namespace App\Configs;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Get configuration from Config singleton
        $config = Config::getInstance();
        
        $host = $config->get('db_host');
        $db = $config->get('db_name');
        $user = $config->get('db_user');
        $pass = $config->get('db_pass');
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            error_log("Attempting database connection to {$host}:{$db} with user {$user}");
            $this->connection = new PDO($dsn, $user, $pass, $options);
            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            // In production, log this error rather than displaying it
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}