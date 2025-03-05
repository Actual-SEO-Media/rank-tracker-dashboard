<?php
require_once __DIR__ . '/../config/database.php';

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
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Get all reports for a client domain
    public function getClientReports($domain) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE client_domain = ? 
                  ORDER BY report_period DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result;
    }
    
    // Get single report by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
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
        $stmt->bind_param("ss", $domain, $period);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['count'] > 0;
        }
        
        return false;
    }
    
    // Create new report
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (import_date, report_period, client_domain, file_name, is_baseline) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", 
            $this->import_date, 
            $this->report_period, 
            $this->client_domain, 
            $this->file_name, 
            $this->is_baseline
        );
        
        if ($stmt->execute()) {
            $this->report_id = $this->conn->insert_id;
            return true;
        }
        
        return false;
    }
    

    // Delete a report by ID
    public function delete($id = null) {
    // If no ID is provided, use the current report_id property
    $id_to_delete = $id ?: $this->report_id;
    
    // Make sure we have a valid ID to delete
    if (!$id_to_delete) {
        return false;
    }
    
    $query = "DELETE FROM " . $this->table . " WHERE report_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id_to_delete);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}
    
    // Get all unique client domains
    public function getClientList() {
        $query = "SELECT DISTINCT client_domain FROM " . $this->table . " ORDER BY client_domain";
        $result = $this->conn->query($query);
        return $result;
    }
    
    // Get baseline report for a client
    public function getBaselineReport($domain) {
        $query = "SELECT * FROM " . $this->table . " WHERE client_domain = ? AND is_baseline = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    public function update() {
    $query = "UPDATE " . $this->table . " 
              SET import_date = ?, report_period = ?, client_domain = ?, 
                  file_name = ?, is_baseline = ?
              WHERE report_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssssii",
        $this->import_date,
        $this->report_period,
        $this->client_domain,
        $this->file_name,
        $this->is_baseline,
        $this->report_id
    );
    
    return $stmt->execute();
}

    public function setBaseline($isBaseline = true) {
    // First clear existing baseline if setting a new one
    if ($isBaseline) {
        $clearQuery = "UPDATE " . $this->table . " 
                      SET is_baseline = 0 
                      WHERE client_domain = ? AND report_id != ?";
        $clearStmt = $this->conn->prepare($clearQuery);
        $clearStmt->bind_param("si", $this->client_domain, $this->report_id);
        $clearStmt->execute();
    }
    
    // Set current report baseline status
    $this->is_baseline = $isBaseline ? 1 : 0;
    return $this->update();
}

    
}

