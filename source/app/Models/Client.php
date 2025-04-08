<?php
namespace App\Models;

use App\Configs\Database;
use PDO;

class Client {
    private $conn;
    private $table = 'reports';
    public $client_domain;
    
    public function __construct() {
        // Use the singleton pattern to get the database instance
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Get all clients
    public function getAll() {
        $query = "SELECT DISTINCT client_domain FROM reports ORDER BY client_domain";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Get client report count
    public function getReportCount($domain) {
        $query = "SELECT COUNT(*) as count FROM reports WHERE client_domain = :domain";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domain', $domain);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }
    
    // Get latest report for client
    public function getLatestReport($domain) {
        $query = "SELECT * FROM reports
                  WHERE client_domain = :domain
                  ORDER BY report_period DESC
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domain', $domain);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Delete all reports for a client domain
    public function deleteClient($domain) {
        if (!$domain) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE client_domain = :domain";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domain', $domain);
        $stmt->execute();
        
        // Return true if at least one row was affected
        return $stmt->rowCount() > 0;
    }
}