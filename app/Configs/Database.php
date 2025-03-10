<?php
namespace App\Configs;

class Database {
    private $host;
    private $user;
    private $pass;
    private $name;
    private $conn;
    private static $instance = null;
    
    public function __construct() {
        // Load environment variables
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
        $this->name = getenv('DB_NAME') ?: 'asm_seo_reports';
        
        // Validate required parameters
        if (empty($this->host) || empty($this->name)) {
            throw new \Exception("Database host and name are required");
        }
    }
    
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    \PDO::ATTR_PERSISTENT => false, // Disable persistent connections
                    \PDO::ATTR_TIMEOUT => 5, // Connection timeout in seconds
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true // Use buffered queries
                ];
                
                $this->conn = new \PDO($dsn, $this->user, $this->pass, $options);
                
            } catch(\PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                error_log("Connection details: host={$this->host}, dbname={$this->name}, user={$this->user}");
                throw new \Exception("Database connection failed. Please check your credentials and make sure the database server is running.");
            }
        }
        
        return $this->conn;
    }
    
    // Helper methods for secure queries
    public function prepareStatement($sql) {
        // Validate SQL statement
        if (preg_match('/^(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER)/i', trim($sql))) {
            throw new \Exception("Direct modification queries are not allowed. Use the provided methods instead.");
        }
        return $this->getConnection()->prepare($sql);
    }
    
    public function executeQuery($sql, $params = []) {
        $stmt = $this->prepareStatement($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data) {
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \Exception("Invalid table name");
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO `{$table}` (" . implode(', ', array_map(function($field) {
            return "`" . $field . "`";
        }, $fields)) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->executeQuery($sql, array_values($data));
        return $this->getConnection()->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \Exception("Invalid table name");
        }
        
        $fields = array_map(function($field) {
            return "`" . $field . "` = ?";
        }, array_keys($data));
        
        $sql = "UPDATE `{$table}` SET " . implode(', ', $fields) . " WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->executeQuery($sql, $params)->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \Exception("Invalid table name");
        }
        
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->executeQuery($sql, $params)->rowCount();
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserializing of the instance
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}