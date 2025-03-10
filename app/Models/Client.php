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
        $result = $this->conn->query($query);
        
        return $result;
    }
    
    // Get client report count
    public function getReportCount($domain) {
        $query = "SELECT COUNT(*) as count FROM reports WHERE client_domain = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['count'];
        }
        
        return 0;
    }
    
    // Get latest report for client
    public function getLatestReport($domain) {
        $query = "SELECT * FROM reports 
                 WHERE client_domain = ? 
                 ORDER BY report_period DESC 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

        // Delete all reports for a client domain
    public function deleteClient($domain) {
        if (!$domain) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE client_domain = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $domain);
        
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        return false;
    }

}