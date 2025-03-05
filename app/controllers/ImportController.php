<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/SearchData.php';
require_once __DIR__ . '/../config/EngineConfig.php';

class ImportController {
    private $reportModel;
    private $searchDataModel;
    
    public function __construct() {
        $this->reportModel = new Report();
        $this->searchDataModel = new SearchData();
    }
    
    /**
     * Displays the import form and handles form submission.
     * If a file is uploaded, it processes the import and returns success or error messages.
     */ 
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
    
    /**
     * Displays the import form and handles form submission.
     * If a file is uploaded, it processes the import and returns success or error messages.
     */
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
                
                // Update existing report
                $this->reportModel->report_id = $report_id;
                $this->reportModel->update();
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
            
            // Optional: Delete the temporary file
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $result['error'] = 'Error during import: ' . $e->getMessage();
            
            // Clean up file on error
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        return $result;
    }
    
      /**
     * Processes the CSV file and saves data to the database.
     * 
     * @param string $filepath Path to the CSV file
     * @param int $report_id Report ID for storing data
     * @return bool Returns true on success, false on failure
     */
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
        
        // Find column indexes for all engines
        $column_indexes = $this->findColumnIndexes($headers);
        
        // Validate required columns
        if (!isset($column_indexes['keyword']) || 
            !isset($column_indexes['visibility']) || 
            !isset($column_indexes['visibility_difference'])) {
            fclose($handle);
            return false;
        }
        
        // Clear existing data for each engine
        foreach (EngineConfig::getEngineKeys() as $engine) {
            $this->searchDataModel->clearReportData($report_id, $engine);
        }
        
        // Process data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Skip short rows
            if (count($data) < count($headers)) {
                continue;
            }
            
            // Clean and convert data
            $data = array_map(function($value) {
                return iconv('CP1252', 'UTF-8//IGNORE', trim($value));
            }, $data);
            
            // Extract common data
            $keyword = $data[$column_indexes['keyword']];
            $visibility = $this->parseNumericValue($data[$column_indexes['visibility']]);
            $visibility_difference = $this->parseNumericValue($data[$column_indexes['visibility_difference']]);
            
            // Set common properties
            $this->searchDataModel->report_id = $report_id;
            $this->searchDataModel->keyword = $keyword;
            $this->searchDataModel->visibility = $visibility;
            $this->searchDataModel->visibility_difference = $visibility_difference;
            
            // Process each search engine's data
            foreach (EngineConfig::getAllEngines() as $engine => $columns) {
                // Skip if we don't have the column indexes for this engine
                if (!isset($column_indexes[$engine])) {
                    continue;
                }
                
                $engineIndexes = $column_indexes[$engine];
                
                // Check if rank column exists and has a value
                if (isset($engineIndexes['rank']) && isset($data[$engineIndexes['rank']])) {
                    // Set rank based on whether it contains "not in top"
                    $rankValue = $data[$engineIndexes['rank']];
                    if (strpos(strtolower($rankValue), 'not') !== false) {
                        $this->searchDataModel->rank = EngineConfig::NOT_RANKED;
                    } else {
                        $this->searchDataModel->rank = $this->parseNumericValue($rankValue);
                    }
                    
                    // Set other values
                    $this->searchDataModel->previous_rank = $this->parsePreviousRank(
                        $data[$engineIndexes['prevRank']] ?? ''
                    );
                    
                    $this->searchDataModel->difference = $this->parseDifference(
                        $data[$engineIndexes['diff']] ?? ''
                    );
                    
                    $this->searchDataModel->serp_features = $data[$engineIndexes['serp']] ?? '';
                    $this->searchDataModel->url = $data[$engineIndexes['url']] ?? '';
                    
                    $this->searchDataModel->save($engine);
                }
            }
        }
        
        fclose($handle);
        return true;
    }
      /**
     * Processes the CSV file and saves data to the database.
     * 
     * @param string $filepath Path to the CSV file
     * @param int $report_id Report ID for storing data
     */
    private function findColumnIndexes($headers) {
        $indexes = [
            'keyword' => array_search('Keyword', $headers),
            'visibility' => array_search('Visibility', $headers),
            'visibility_difference' => array_search('Visibility Difference', $headers)
        ];
        
        // Find indexes for each engine
        foreach (EngineConfig::getAllEngines() as $engine => $columns) {
            $engineIndexes = [
                'rank' => array_search($columns['rankColumn'], $headers),
                'prevRank' => array_search($columns['prevRankColumn'], $headers),
                'diff' => array_search($columns['diffColumn'], $headers),
                'serp' => array_search($columns['serpColumn'], $headers),
                'url' => array_search($columns['urlColumn'], $headers)
            ];
            
            // Only include this engine if we found the rank column
            if ($engineIndexes['rank'] !== false) {
                $indexes[$engine] = $engineIndexes;
            }
        }
        
        return $indexes;
    }
    
    /**
     * Parse numeric value from string - handles empty, non-numeric values, and formats like "1(2)"
     */
    private function parseNumericValue($value) {
        if (empty($value)) {
            return 0;
        }
        
        // Handle formats like "1(2)" - extract the first number only
        if (preg_match('/^(\d+)\(\d+\)$/', $value, $matches)) {
            return (int)$matches[1];
        }
        
        // Extract numbers from string if mixed
        if (preg_match('/(-?\d+(\.\d+)?)/', $value, $matches)) {
            return floatval($matches[1]);
        }
        
        return is_numeric($value) ? floatval($value) : 0;
    }
    
    /**
     * Parse previous rank value - handle 'not in top' cases and formats like "1(2)"
     */
    private function parsePreviousRank($value) {
        if (empty($value)) {
            return 0;
        }
        
        // Check for "not in top" strings
        if (strpos(strtolower($value), 'not') !== false) {
            return EngineConfig::NOT_RANKED;
        }
        
        // Handle formats like "1(2)" - extract the first number only
        if (preg_match('/^(\d+)\(\d+\)$/', $value, $matches)) {
            return (int)$matches[1];
        }
        
        return $this->parseNumericValue($value);
    }
    
    /**
     * Parse difference value - handle special cases like 'dropped', 'entered', etc.
     */
    private function parseDifference($value) {
        if (empty($value)) {
            return EngineConfig::NO_CHANGE;
        }
        
        $value = strtolower(trim($value));
        
        // Handle special text cases
        switch ($value) {
            case 'dropped':
                return EngineConfig::DROPPED;
            case 'entered':
                return EngineConfig::ENTERED;
            case 'stays out':
                return EngineConfig::NO_CHANGE;
            case 'new':
                return EngineConfig::ENTERED;
            default:
                return $this->parseNumericValue($value);
        }
    }
}