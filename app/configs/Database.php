<?php
namespace App\Configs;

class Database {
    private $host = 'localhost';
    private $user = 'root'; // Change to your MySQL username
    private $pass = ''; // Change to your MySQL password
    private $name = 'asm_seo_reports';
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->name);
            $this->conn->set_charset("utf8mb4");
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            echo "Database connection error: " . $e->getMessage();
            die();
        }
        
        return $this->conn;
    }
}