<?php
namespace App\Models;

use App\Configs\Database;
use App\Configs\EngineConfig;
use App\Configs\Session;

class RankingData {
    private $conn;
    
    // Properties
    public $id;
    public $report_id;
    public $keyword;
    public $visibility;
    public $visibility_difference;
    public $rank;
    public $previous_rank;
    public $difference;
    public $serp_features;
    public $url;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    // Get search data for a report by engine
    public function getByReportAndEngine($report_id, $engine) {

        $table = $this->getTableName($engine);
        
        $query = "SELECT * FROM " . $table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$report_id]);
        return $stmt;
    }
    
    // Get filtered search data (non-empty URLs)
    public function getFilteredByReportAndEngine($report_id, $engine) {

        $table = $this->getTableName($engine);
        
        $query = "SELECT * FROM " . $table . " WHERE report_id = ? AND url != ''";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$report_id]);
        return $stmt;
    }
    
    // Save search data
    public function save($engine) {
        try {
            // Validate and sanitize data
            $this->validateData();
            $this->sanitizeData();

            $table = $this->getTableName($engine);
            
            $query = "INSERT INTO " . $table . " 
                     (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $this->report_id,
                $this->keyword,
                $this->visibility,
                $this->visibility_difference,
                $this->rank,
                $this->previous_rank,
                $this->difference,
                $this->serp_features,
                $this->url
            ]);
        } catch (\Exception $e) {
            error_log("Error saving ranking data: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Clear existing data for a report
    public function clearReportData($report_id, $engine) {

        $table = $this->getTableName($engine);
        
        $query = "DELETE FROM " . $table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$report_id]);
    }
    
    // Calculate statistics for a report
    public function calculateReportStats($report_id) {
        $stats = [
            'first_position' => 0,
            'top_5_positions' => 0,
            'first_page' => 0,
            'first_two_pages' => 0,
            'new_listings' => 0,
            'moved_up' => 0,
            'moved_down' => 0,
            'no_change' => 0,
            'net_position_change' => 0,
            'visibility_score' => 0,
            'visibility_percentage' => 0,
            'total_keywords' => 0
        ];
        
        $engines = EngineConfig::getEngineKeys();
        $all_keywords = [];
        $all_rankings = [];
        
        foreach ($engines as $engine) {
            $result = $this->getFilteredByReportAndEngine($report_id, $engine);
            $count_key = $engine . '_count';
            $stats[$count_key] = $result->rowCount();
            
            while ($row = $result->fetch()) {
                $all_keywords[$row['keyword']] = true;
                $all_rankings[] = $row;
                
                // Count by position
                if ($row['rank'] == 1) {
                    $stats['first_position']++;
                }
                if ($row['rank'] <= 5) {
                    $stats['top_5_positions']++;
                }
                if ($row['rank'] <= 10) {
                    $stats['first_page']++;
                }
                if ($row['rank'] <= 20) {
                    $stats['first_two_pages']++;
                }
                
                // Count by movement
                if ($row['previous_rank'] == 0) {
                    $stats['new_listings']++; // Was not ranking before
                } elseif ($row['difference'] > 0) {
                    $stats['moved_up']++;
                    $stats['net_position_change'] += $row['difference'];
                } elseif ($row['difference'] < 0) {
                    $stats['moved_down']++;
                    $stats['net_position_change'] -= abs($row['difference']);
                } else {
                    $stats['no_change']++;
                }
                
                // Calculate visibility score
                if ($row['rank'] <= 100) { // Only count rankings in top 100
                    $points = 101 - $row['rank'];
                    $stats['visibility_score'] += $points;
                }
            }
        }
        
        $stats['total_keywords'] = count($all_keywords);
        
        // Calculate visibility percentage
        $max_possible = count($all_rankings) * 100; // Maximum possible score
        if ($max_possible > 0) {
            $stats['visibility_percentage'] = ($stats['visibility_score'] / $max_possible) * 100;
        }
        
        return $stats;
    }
    
    private function getTableName($engine) {
        if (!in_array($engine, EngineConfig::getEngineKeys())) {
            throw new \Exception("Invalid search engine: " . $engine);
        }
        return $engine . '_data';
    }

    public function getKeywords($report_id) {
        $query = "
            SELECT DISTINCT atbl.keyword
            FROM (
                SELECT keyword FROM google_data WHERE report_id = ?
                UNION
                SELECT keyword FROM google_mobile_data WHERE report_id = ?
                UNION
                SELECT keyword FROM yahoo_data WHERE report_id = ?
                UNION
                SELECT keyword FROM bing_data WHERE report_id = ?
            ) AS atbl
            ORDER BY atbl.keyword ASC;
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$report_id, $report_id, $report_id, $report_id]);
        
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = htmlspecialchars($row['keyword'], ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }

    // Add these new private methods for validation and sanitization
    private function validateData() {
        // Validate required fields
        if (empty($this->report_id)) {
            throw new \Exception("Report ID is required");
        }
        if (empty($this->keyword)) {
            throw new \Exception("Keyword is required");
        }

        // Validate numeric fields
        if (!is_numeric($this->rank) || $this->rank < 0) {
            throw new \Exception("Invalid rank value");
        }
        if (!is_numeric($this->visibility) || $this->visibility < 0 || $this->visibility > 100) {
            throw new \Exception("Invalid visibility value");
        }
        if (!is_numeric($this->visibility_difference) || $this->visibility_difference < -100 || $this->visibility_difference > 100) {
            throw new \Exception("Invalid visibility difference value");
        }
        if (!is_numeric($this->previous_rank) || $this->previous_rank < 0) {
            throw new \Exception("Invalid previous rank value");
        }
        if (!is_numeric($this->difference)) {
            throw new \Exception("Invalid difference value");
        }
    }

    private function sanitizeData() {
        // Sanitize string fields
        $this->keyword = htmlspecialchars(strip_tags($this->keyword), ENT_QUOTES, 'UTF-8');
        $this->serp_features = htmlspecialchars(strip_tags($this->serp_features), ENT_QUOTES, 'UTF-8');
        
        // Sanitize URL
        if (!empty($this->url)) {
            $this->url = filter_var($this->url, FILTER_SANITIZE_URL);
            if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
                $this->url = ''; // Clear invalid URLs
            }
        }
    }

 
}