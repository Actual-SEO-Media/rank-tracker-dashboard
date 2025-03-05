<?php
require_once __DIR__ . '/../config/database.php';

class SearchData {
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
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Get search data for a report by engine
    public function getByReportAndEngine($report_id, $engine) {
        $table = $this->getTableName($engine);
        
        $query = "SELECT * FROM " . $table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    // Get filtered search data (non-empty URLs)
    public function getFilteredByReportAndEngine($report_id, $engine) {
        $table = $this->getTableName($engine);
        
        $query = "SELECT * FROM " . $table . " WHERE report_id = ? AND url != ''";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    // Save search data
    public function save($engine) {
        $table = $this->getTableName($engine);
        
        $query = "INSERT INTO " . $table . " 
                 (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isiiiisss", 
            $this->report_id,
            $this->keyword,
            $this->visibility,
            $this->visibility_difference,
            $this->rank,
            $this->previous_rank,
            $this->difference,
            $this->serp_features,
            $this->url
        );
        
        return $stmt->execute();
    }
    
    // Clear existing data for a report
    public function clearReportData($report_id, $engine) {
        $table = $this->getTableName($engine);
        
        $query = "DELETE FROM " . $table . " WHERE report_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $report_id);
        
        return $stmt->execute();
    }
    
    // Calculate statistics for a report
    public function calculateReportStats($report_id) {
        $stats = [
            'total_keywords' => 0,
            'first_position' => 0,
            'top_5_positions' => 0,
            'first_page' => 0,
            'first_two_pages' => 0,
            'moved_up' => 0,
            'moved_down' => 0,
            'no_change' => 0,
            'new_listings' => 0,
            'net_position_change' => 0,
            'visibility_score' => 0,
            'visibility_percentage' => 0,
            'google_count' => 0,
            'google_mobile_count' => 0,
            'yahoo_count' => 0,
            'bing_count' => 0
        ];
        
        // Get data from all engines
        $engines = ['google', 'google_mobile', 'yahoo', 'bing'];
        $all_keywords = [];
        $all_rankings = [];
        
        foreach ($engines as $engine) {
            $result = $this->getFilteredByReportAndEngine($report_id, $engine);
            $count_key = $engine . '_count';
            $stats[$count_key] = $result->num_rows;
            
            while ($row = $result->fetch_assoc()) {
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
    
    // Helper method to get the table name for an engine
    private function getTableName($engine) {
        switch ($engine) {
            case 'google':
                return 'google_data';
            case 'bing':
                return 'bing_data';
            case 'yahoo':
                return 'yahoo_data';
            case 'google_mobile':
                return 'google_mobile_data';
            default:
                throw new Exception("Invalid search engine: " . $engine);
        }
    }
}