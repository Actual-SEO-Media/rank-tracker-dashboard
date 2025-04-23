<?php
namespace App\Models;

use App\Configs\Database;

class Report {
    private $conn;
    private $table = 'reports';
    
    // Report properties
    public $report_id;
    public $import_date;
    public $report_period;
    public $client_domain;
    public $file_name;
    public $is_baseline;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Get all reports for a client domain
    public function getClientReports($domain) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE client_domain = ? 
                  ORDER BY report_period DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // Get single report by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if ($row) {
            $this->report_id = $row['report_id'];
            $this->import_date = $row['import_date'];
            $this->report_period = $row['report_period'];
            $this->client_domain = $row['client_domain'];
            $this->file_name = $row['file_name'];
            $this->is_baseline = $row['is_baseline'];
            
            return true;
        }
        
        return false;
    }
    
    // Check if report exists
    public function exists($domain, $period) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE client_domain = ? AND report_period = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain, $period]);
        $row = $stmt->fetch();
        
        return $row['count'] > 0;
    }
    
    // Create new report
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (import_date, report_period, client_domain, file_name, is_baseline) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $this->import_date,
            $this->report_period,
            $this->client_domain,
            $this->file_name,
            $this->is_baseline
        ]);
        
        if ($stmt->rowCount() > 0) {
            $this->report_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Delete a report by ID
    public function delete($id = null) {
        $id_to_delete = $id ?: $this->report_id;
        
        if (!$id_to_delete) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE report_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_to_delete]);
    }
    
    // Get all unique client domains
    public function getClientList() {
        $query = "SELECT DISTINCT client_domain FROM " . $this->table . " ORDER BY client_domain";
        $stmt = $this->conn->query($query);
        return $stmt;
    }
    
    // Get baseline report for a client
    public function getBaselineReport($domain) {
        $query = "SELECT * FROM " . $this->table . " WHERE client_domain = ? AND is_baseline = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain]);
        return $stmt->fetch();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET import_date = ?, report_period = ?, client_domain = ?, 
                      file_name = ?, is_baseline = ?
                  WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $this->import_date,
            $this->report_period,
            $this->client_domain,
            $this->file_name,
            $this->is_baseline,
            $this->report_id
        ]);
    }

    /**
     * Get report ID by client domain and period
     * 
     * @param string $domain The client domain
     * @param string $period The period in YYYY-MM format
     * @return int|false The report ID if found, false otherwise
     */
    public function getReportIdByPeriod($domain, $period) {
        $query = "SELECT report_id FROM " . $this->table . " 
                WHERE client_domain = ? 
                AND report_period = ?
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain, $period]);
        $row = $stmt->fetch();
        
        return $row ? $row['report_id'] : false;
    }

    /**
     * Get all available periods for a client domain
     * 
     * @param string $domain The client domain
     * @param int $limit Optional limit on how many periods to return (default 12)
     * @return array Array of period data with 'period' and 'report_id' fields
     */
    public function getAvailablePeriods($domain, $limit = 12) {
        $query = "SELECT report_period as period, report_id 
                FROM " . $this->table . " 
                WHERE client_domain = ? 
                ORDER BY report_period DESC 
                LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain, $limit]);
        return $stmt->fetchAll();
    }

    public function setBaseline($isBaseline = true) {
        // First clear existing baseline if setting a new one
        if ($isBaseline) {
            $clearQuery = "UPDATE " . $this->table . " 
                          SET is_baseline = 0 
                          WHERE client_domain = ? AND report_id != ?";
            $clearStmt = $this->conn->prepare($clearQuery);
            $clearStmt->execute([$this->client_domain, $this->report_id]);
        }
        
        // Set current report baseline status
        $this->is_baseline = $isBaseline ? 1 : 0;
        return $this->update();
    }

    public function getPrevReportByPeriod($domain, $period) {
        $query = "SELECT report_period as period, report_id 
                FROM " . $this->table . " 
                WHERE client_domain = ? AND report_period < ?
                ORDER BY report_period DESC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain, $period]);
        return $stmt->fetch();
    }

    public function getNextReportByPeriod($domain, $period) {
        $query = "SELECT report_period as period, report_id 
                FROM " . $this->table . " 
                WHERE client_domain = ? AND report_period > ?
                ORDER BY report_period DESC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$domain, $period]);
        return $stmt->fetch();
    }
}
