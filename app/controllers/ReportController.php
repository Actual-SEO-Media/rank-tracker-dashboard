<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/SearchData.php';

class ReportController {
    private $reportModel;
    private $searchDataModel;
    
    public function __construct() {
        $this->reportModel = new Report();
        $this->searchDataModel = new SearchData();
    }
    
    // Display report details
    public function details($id) {
        // Get report data
        if (!$this->reportModel->getById($id)) {
            // Report not found, redirect to home
            header('Location: index.php');
            exit;
        }
        
        // Get search data for different engines
        $google_data = $this->getFilteredSearchData($id, 'google');
        $google_mobile_data = $this->getFilteredSearchData($id, 'google_mobile');
        $yahoo_data = $this->getFilteredSearchData($id, 'yahoo');
        $bing_data = $this->getFilteredSearchData($id, 'bing');
        
        // Calculate report statistics
        $stats = $this->searchDataModel->calculateReportStats($id);
        
        // Get baseline report
        $baseline_report = $this->reportModel->getBaselineReport($this->reportModel->client_domain);
        
        // Prepare data for view
        $report = [
            'report_id' => $this->reportModel->report_id,
            'import_date' => $this->reportModel->import_date,
            'report_period' => $this->reportModel->report_period,
            'client_domain' => $this->reportModel->client_domain,
            'file_name' => $this->reportModel->file_name,
            'is_baseline' => $this->reportModel->is_baseline
        ];
        
       
        // Include the view
        include __DIR__ . '/../views/reports/details.php';
    }

        /**
     * Get report ID by client domain and period
     * 
     * @param string $domain The client domain
     * @param string $period The period in YYYY-MM format
     * @return int|false The report ID if found, false otherwise
     */
    public function getReportIdByPeriod($domain, $period) {
        return $this->reportModel->getReportIdByPeriod($domain, $period);
    }

    /**
     * Get all available periods for a client domain
     * 
     * @param string $domain The client domain
     * @param int $limit Optional limit on how many periods to return (default 12)
     * @return array Array of period data with 'period' and 'report_id' fields
     */
    public function getAvailablePeriods($domain, $limit = 12) {
        return $this->reportModel->getAvailablePeriods($domain, $limit);
    }
        
        
    // Display search positions
    public function positions($id) {
        // Get report data
        if (!$this->reportModel->getById($id)) {
            // Report not found, redirect to home
            header('Location: index.php');
            exit;
        }
        
        // Get search data for different engines
        $google_data = $this->getFilteredSearchData($id, 'google');
        $google_mobile_data = $this->getFilteredSearchData($id, 'google_mobile');
        $yahoo_data = $this->getFilteredSearchData($id, 'yahoo');
        $bing_data = $this->getFilteredSearchData($id, 'bing');
        
        // Prepare data for view
        $report = [
            'report_id' => $this->reportModel->report_id,
            'import_date' => $this->reportModel->import_date,
            'report_period' => $this->reportModel->report_period,
            'client_domain' => $this->reportModel->client_domain,
            'file_name' => $this->reportModel->file_name
        ];
        
        // Include the view
        include __DIR__ . '/../views/reports/positions.php';
    }
    
    // Helper method to get filtered search data (only entries with URLs)
    private function getFilteredSearchData($report_id, $engine) {
        $result = $this->searchDataModel->getFilteredByReportAndEngine($report_id, $engine);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Helper method to prepare top performers data
    private function prepareTopPerformers($data) {
        // Sort by rank
        usort($data, function($a, $b) {
            return $a['rank'] - $b['rank']; 
        });
        
        // Get top 5
        return array_slice($data, 0, 5);
    }
    
    // Helper method to prepare most improved data
    private function prepareMostImproved($data) {
        // Sort by difference (improvement)
        usort($data, function($a, $b) {
            return $b['difference'] - $a['difference']; 
        });
        
        // Filter to only show improved keywords and get top 5
        $improved = array_filter($data, function($row) {
            return $row['difference'] > 0;
        });
        
        return array_slice($improved, 0, 5);
    }
    
}