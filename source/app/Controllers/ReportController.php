<?php
namespace App\Controllers;

use App\Models\Report;
use App\Models\RankingData;
use App\Configs\EngineConfig;

class ReportController {
    private $reportModel;
    private $rankingDataModel;
    
    public function __construct() {
        $this->reportModel = new Report();
        $this->rankingDataModel = new RankingData();
    }
    
    // Display report details
    public function details($id) {
        // Get report data
        if (!$this->reportModel->getById($id)) {
            // Report not found, redirect to home
            header('Location: index.php');
            exit;
        }
        
        $engineData = [];
        foreach (EngineConfig::getEngineKeys() as $engine) {
            $engineData[$engine] = $this->getFilteredSearchData($id, $engine);
        }
        
        // For backward compatibility with existing view templates
        $google_data = $engineData['google'] ?? [];
        $google_mobile_data = $engineData['google_mobile'] ?? [];
        $yahoo_data = $engineData['yahoo'] ?? [];
        $bing_data = $engineData['bing'] ?? [];

        $period = $this->reportModel->report_period;
        $domain = $this->reportModel->client_domain;

        $prev_period = date('Y-m', strtotime($period . '-01 -1 month'));
        $prev_report_id = $this->getReportIdByPeriod($domain, $prev_period);

        $next_period = date('Y-m', strtotime($period . '-01 +1 month'));
        $next_report_id = $this->getReportIdByPeriod($domain, $next_period);

        $available_periods = $this->getAvailablePeriods($domain);
        
        // Add to data going to the view
        $navigation = [
            'prev_report_id' => $prev_report_id,
            'next_report_id' => $next_report_id,
            'available_periods' => $available_periods
        ];
        
        // Calculate report statistics
        $stats = $this->rankingDataModel->calculateReportStats($id);
        
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

    // Display report keywords
    public function keywords($id) {
        // Get report data
        if (!$this->reportModel->getById($id)) {
            // Report not found, redirect to home
            header('Location: index.php');
            exit;
        }
        
        // Prepare data for view
        $report = [
            'report_id' => $this->reportModel->report_id,
            'report_period' => $this->reportModel->report_period,
            'report_keywords' => $this->rankingDataModel->getKeywords($id)
        ];
        
        // Include the view
        include __DIR__ . '/../views/reports/keywords.php';
    }
    
    // Display search positions
    public function positions($id) {
        // Get report data
        if (!$this->reportModel->getById($id)) {
            // Report not found, redirect to home
            header('Location: index.php');
            exit;
        }
        
        // Get search data for all engines defined in config
        $engineData = [];
        foreach (EngineConfig::getEngineKeys() as $engine) {
            $engineData[$engine] = $this->getFilteredSearchData($id, $engine);
        }
        
        // For backward compatibility with existing view templates
        $google_data = $engineData['google'] ?? [];
        $google_mobile_data = $engineData['google_mobile'] ?? [];
        $yahoo_data = $engineData['yahoo'] ?? [];
        $bing_data = $engineData['bing'] ?? [];
        
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
        $result = $this->rankingDataModel->getFilteredByReportAndEngine($report_id, $engine);
        $data = [];
        
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
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
        
        // Filter out "not ranked" keywords (rank = 101)
        $filtered = array_filter($data, function($row) {
            return $row['rank'] != EngineConfig::NOT_RANKED;
        });
        
        // Get top 5
        return array_slice($filtered, 0, 5);
    }
    
    // Helper method to prepare most improved data
    private function prepareMostImproved($data) {
        // Sort by difference (improvement)
        usort($data, function($a, $b) {
            return $b['difference'] - $a['difference']; 
        });
        
        // Filter to only show improved keywords (not including "entered" which is 100)
        $improved = array_filter($data, function($row) {
            // Include normal improvements (positive values less than ENTERED constant)
            return $row['difference'] > 0 && $row['difference'] < EngineConfig::ENTERED;
        });
        
        return array_slice($improved, 0, 5);
    }
    
    // Helper method to get newly entered keywords
    private function getNewlyEnteredKeywords($data) {
        // Filter to only show keywords that entered rankings
        return array_filter($data, function($row) {
            return $row['difference'] == EngineConfig::ENTERED;
        });
    }
    
    // Helper method to get dropped keywords
    private function getDroppedKeywords($data) {
        // Filter to only show keywords that dropped from rankings
        return array_filter($data, function($row) {
            return $row['difference'] == EngineConfig::DROPPED;
        });
    }
    
    // TODO: Implement the following methods

    private function getReportIdByPeriod($domain, $period) {
        // Implementation needed
        return null;
    }
    
    private function getAvailablePeriods($domain) {
        // Implementation needed
        return [];
    }
}