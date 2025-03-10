<?php
namespace App\Models;

use App\Configs\Database;

class Client {
    private $conn;
    private $table = 'reports';

    public $client_domain;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Get all clients
    public function getAll() {
        $query = "SELECT DISTINCT client_domain FROM reports ORDER BY client_domain";
        $stmt = $this->conn->query($query);
        return $stmt;
    }
    
    // Get client report count
    public function getReportCount($domain) {
        $query = "SELECT COUNT(*) as count FROM reports WHERE client_domain = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain]);
        $row = $stmt->fetch();
        
        return $row ? $row['count'] : 0;
    }
    
    // Get latest report for client
    public function getLatestReport($domain) {
        $query = "SELECT * FROM reports 
                 WHERE client_domain = ? 
                 ORDER BY report_period DESC 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain]);
        return $stmt->fetch();
    }

    // Delete all reports for a client domain
    public function deleteClient($domain) {
        if (!$domain) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE client_domain = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$domain]);
    }
}