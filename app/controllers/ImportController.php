<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/SearchData.php';

class ImportController {
    private $reportModel;
    private $searchDataModel;
    
    public function __construct() {
        $this->reportModel = new Report();
        $this->searchDataModel = new SearchData();
    }
    
    // Display import form
    public function index() {
        $domain = isset($_GET['domain']) ? $_GET['domain'] : '';
        $success = false;
        $error = '';
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processImport();
            $success = $result['success'];
            $error = $result['error'];
            $domain = $result['domain'] ?? $domain;
            $report_id = $result['report_id'] ?? null;
        }
        
        // Include the view
        include __DIR__ . '/../views/import/index.php';
    }
    
    // Process CSV import
    private function processImport() {
        $result = [
            'success' => false,
            'error' => '',
            'domain' => '',
            'report_id' => null
        ];
        
        // Validate input
        if (empty($_POST['client_domain'])) {
            $result['error'] = 'Client domain is required';
            return $result;
        }
        
        if (empty($_POST['report_period'])) {
            $result['error'] = 'Report period is required';
            return $result;
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
            $result['error'] = 'Please select a valid CSV file';
            return $result;
        }
        
        // Get form data
        $client_domain = trim($_POST['client_domain']);
        $report_period = trim($_POST['report_period']);
        $is_baseline = isset($_POST['is_baseline']) ? 1 : 0;
        $import_date = date('Y-m-d');
        $file = $_FILES['csv_file'];
        
        $result['domain'] = $client_domain;
        
        // Validate file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            $result['error'] = 'Only CSV files are allowed';
            return $result;
        }
        
        // Create uploads directory if needed
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid('seo_') . '_' . $file['name'];
        $filepath = $upload_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $result['error'] = 'Failed to upload file. Error code: ' . $file['error'];
            return $result;
        }
        
        // Start transaction
        $database = new Database();
        $conn = $database->getConnection();
        $conn->begin_transaction();
        
        try {
            // Clear any existing baseline if this is marked as baseline
            if ($is_baseline) {
                $stmt = $conn->prepare("UPDATE reports SET is_baseline = 0 WHERE client_domain = ?");
                $stmt->bind_param("s", $client_domain);
                $stmt->execute();
            }
            
            // Create or update report
            $this->reportModel->client_domain = $client_domain;
            $this->reportModel->report_period = $report_period;
            $this->reportModel->import_date = $import_date;
            $this->reportModel->file_name = $file['name'];
            $this->reportModel->is_baseline = $is_baseline;
            
            if ($this->reportModel->exists($client_domain, $report_period)) {
                // Get existing report ID
                $reportResult = $this->reportModel->getClientReports($client_domain);
                while ($row = $reportResult->fetch_assoc()) {
                    if ($row['report_period'] === $report_period) {
                        $report_id = $row['report_id'];
                        break;
                    }
                }
                
                // TODO: Update existing report
            } else {
                // Create new report
                if (!$this->reportModel->create()) {
                    throw new Exception("Failed to create report record");
                }
                $report_id = $this->reportModel->report_id;
            }
            
            $result['report_id'] = $report_id;
            
            // Process CSV file
            if (!$this->processCSV($filepath, $report_id)) {
                throw new Exception("Failed to process CSV data");
            }
            
            // Commit transaction
            $conn->commit();
            $result['success'] = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $result['error'] = 'Error during import: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    // Process CSV file
    private function processCSV($filepath, $report_id) {
        if (($handle = fopen($filepath, "r")) === FALSE) {
            return false;
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        
        // Convert headers to UTF-8 and clean them
        $headers = array_map(function($header) {
            $header = iconv('CP1252', 'UTF-8//IGNORE', $header);
            return trim($header);
        }, $headers);
        
        // Find column indexes
        $keyword_idx = array_search('Keyword', $headers);
        $visibility_idx = array_search('Visibility', $headers);
        $vis_diff_idx = array_search('Visibility Difference', $headers);
        
        // Google columns
        $google_rank_idx = array_search('Google HOU Rank', $headers);
        $google_prev_idx = array_search('Google HOU Previous Rank', $headers);
        $google_diff_idx = array_search('Google HOU Difference', $headers);
        $google_serp_idx = array_search('Google HOU SERP Features', $headers);
        $google_url_idx = array_search('Google HOU URL Found', $headers);
        
        // Google Mobile columns
        $gmobile_rank_idx = array_search('Google Mobile HOU Rank', $headers);
        $gmobile_prev_idx = array_search('Google Mobile HOU Previous Rank', $headers);
        $gmobile_diff_idx = array_search('Google Mobile HOU Difference', $headers);
        $gmobile_serp_idx = array_search('Google Mobile HOU SERP Features', $headers);
        $gmobile_url_idx = array_search('Google Mobile HOU URL Found', $headers);
        
        // Yahoo columns
        $yahoo_rank_idx = array_search('Yahoo! Rank', $headers);
        $yahoo_prev_idx = array_search('Yahoo! Previous Rank', $headers);
        $yahoo_diff_idx = array_search('Yahoo! Difference', $headers);
        $yahoo_serp_idx = array_search('Yahoo! SERP Features', $headers);
        $yahoo_url_idx = array_search('Yahoo! URL Found', $headers);
        
        // Bing columns
        $bing_rank_idx = array_search('Bing US Rank', $headers);
        $bing_prev_idx = array_search('Bing US Previous Rank', $headers);
        $bing_diff_idx = array_search('Bing US Difference', $headers);
        $bing_serp_idx = array_search('Bing US SERP Features', $headers);
        $bing_url_idx = array_search('Bing US URL Found', $headers);
        
        // Validate required columns
        if ($keyword_idx === false || $visibility_idx === false || $vis_diff_idx === false) {
            fclose($handle);
            return false;
        }
        
        // Clear existing data
        $engines = ['google', 'google_mobile', 'yahoo', 'bing'];
        foreach ($engines as $engine) {
            $this->searchDataModel->clearReportData($report_id, $engine);
        }
        
        // Process data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Clean and convert data
            $data = array_map(function($value) {
                return iconv('CP1252', 'UTF-8//IGNORE', $value);
            }, $data);
            
            // Extract common data
            $keyword = $data[$keyword_idx];
            $visibility = intval($data[$visibility_idx]);
            $visibility_difference = intval($data[$vis_diff_idx]);
            
            // Set common properties
            $this->searchDataModel->report_id = $report_id;
            $this->searchDataModel->keyword = $keyword;
            $this->searchDataModel->visibility = $visibility;
            $this->searchDataModel->visibility_difference = $visibility_difference;
            
            // Process Google data
            if ($google_rank_idx !== false && !empty($data[$google_rank_idx])) {
                $this->searchDataModel->rank = intval($data[$google_rank_idx]);
                $this->searchDataModel->previous_rank = intval($data[$google_prev_idx]);
                $this->searchDataModel->difference = intval($data[$google_diff_idx]);
                $this->searchDataModel->serp_features = $data[$google_serp_idx];
                $this->searchDataModel->url = $data[$google_url_idx];
                
                $this->searchDataModel->save('google');
            }
            
            // Process Google Mobile data
            if ($gmobile_rank_idx !== false && !empty($data[$gmobile_rank_idx])) {
                $this->searchDataModel->rank = intval($data[$gmobile_rank_idx]);
                $this->searchDataModel->previous_rank = intval($data[$gmobile_prev_idx]);
                $this->searchDataModel->difference = intval($data[$gmobile_diff_idx]);
                $this->searchDataModel->serp_features = $data[$gmobile_serp_idx];
                $this->searchDataModel->url = $data[$gmobile_url_idx];
                
                $this->searchDataModel->save('google_mobile');
            }
            
            // Process Yahoo data
            if ($yahoo_rank_idx !== false && !empty($data[$yahoo_rank_idx])) {
                $this->searchDataModel->rank = intval($data[$yahoo_rank_idx]);
                $this->searchDataModel->previous_rank = intval($data[$yahoo_prev_idx]);
                $this->searchDataModel->difference = intval($data[$yahoo_diff_idx]);
                $this->searchDataModel->serp_features = $data[$yahoo_serp_idx];
                $this->searchDataModel->url = $data[$yahoo_url_idx];
                
                $this->searchDataModel->save('yahoo');
            }
            
            // Process Bing data
            if ($bing_rank_idx !== false && !empty($data[$bing_rank_idx])) {
                $this->searchDataModel->rank = intval($data[$bing_rank_idx]);
                $this->searchDataModel->previous_rank = intval($data[$bing_prev_idx]);
                $this->searchDataModel->difference = intval($data[$bing_diff_idx]);
                $this->searchDataModel->serp_features = $data[$bing_serp_idx];
                $this->searchDataModel->url = $data[$bing_url_idx];
                
                $this->searchDataModel->save('bing');
            }
        }
        
        fclose($handle);
        return true;
    }
}