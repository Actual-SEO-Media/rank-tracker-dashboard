<?php
// Include database connection
require_once 'config/database.php';

/**
 * Get all unique client domains from the reports table
 *
 * @return array Array of client domains
 */
function getClientList() {
    global $conn;
    
    $clients = [];
    
    $sql = "SELECT DISTINCT client_domain FROM reports ORDER BY client_domain";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row['client_domain'];
        }
    }
    
    return $clients;
}

/**
 * Get client reports by domain
 *
 * @param string $domain Client domain name
 * @return array Array of client reports
 */
function getClientReports($domain) {
    global $conn;
    
    $reports = [];
    
    $sql = "SELECT report_id, report_period, import_date FROM reports 
            WHERE client_domain = ? 
            ORDER BY report_period DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    
    return $reports;
}

/**
 * Get data for a specific report and search engine
 *
 * @param int $report_id Report ID
 * @param string $engine Search engine (google, bing, yahoo, google_mobile)
 * @return array Array of search engine data
 */
function getReportData($report_id, $engine) {
    global $conn;
    
    $data = [];
    $table = $engine . '_data';
    
    $sql = "SELECT * FROM $table WHERE report_id = ? ORDER BY rank";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    return $data;
}

/**
 * Get report details
 *
 * @param int $report_id Report ID
 * @return array|false Report details or false if not found
 */
function getReportDetails($report_id) {
    global $conn;
    
    $sql = "SELECT * FROM reports WHERE report_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Format report period as a readable month/year
 *
 * @param string $period Report period in YYYY-MM format
 * @return string Formatted period (e.g., "March 2024")
 */
function formatReportPeriod($period) {
    $date = DateTime::createFromFormat('Y-m', $period);
    return $date ? $date->format('F Y') : $period;
}

/**
 * Check if a report exists for a domain and period
 *
 * @param string $domain Client domain
 * @param string $period Report period (YYYY-MM)
 * @return bool True if exists, false otherwise
 */
function reportExists($domain, $period) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM reports 
            WHERE client_domain = ? AND report_period = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $domain, $period);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    
    return false;
}