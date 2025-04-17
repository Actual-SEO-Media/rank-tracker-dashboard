<?php
namespace App\Services;

use App\Models\Report;
use App\Models\RankingData;
use App\Services\RankingDataParser;
use App\Services\FileUploadService;
use App\Configs\Database;
use App\Configs\EngineConfig;

class ImportService {
    private $reportModel;
    private $rankingDataModel;
    private $fileUploadService;
    private $dataParser;
    
    public function __construct() {
        $this->reportModel = new Report();
        $this->rankingDataModel = new RankingData();
        $this->fileUploadService = new FileUploadService();
        $this->dataParser = new RankingDataParser();
    }
    
    /**
     * Process CSV import and store data in database
     * 
     * @param array $file Uploaded file data ($_FILES element)
     * @param string $clientDomain Client domain
     * @param string $reportPeriod Report period
     * @param bool $isBaseline Whether this is a baseline report
     * @return array Result with success status, error message, and report details
     */
    public function processImport($file, $clientDomain, $reportPeriod, $isBaseline = false) {
        $result = [
            'success' => false,
            'error' => '',
            'domain' => $clientDomain,
            'report_id' => null
        ];
        
        // Upload file
        $uploadResult = $this->fileUploadService->uploadFile($file, ['csv']);
        if (!$uploadResult['success']) {
            $result['error'] = $uploadResult['error'];
            return $result;
        }
        
        // Get file path
        $filepath = $uploadResult['filepath'];
        
        // Start transaction
        $database = Database::getInstance();
        $conn = $database->getConnection();
        $conn->beginTransaction();
        
        try {
            // Create or update report entry and get report ID
            $reportId = $this->handleReportEntry(
                $conn, 
                $clientDomain, 
                $reportPeriod, 
                $uploadResult['original_filename'], 
                $isBaseline
            );
            
            $result['report_id'] = $reportId;
            
            // Parse CSV file
            $parsedData = $this->dataParser->parseCSVFile($filepath);

            if ($parsedData === false) {
                throw new \Exception("Failed to process CSV file");
            }
            
            // Clear existing data for this report
            $this->clearExistingData($reportId);
            
            // Save parsed data to database
            $this->saveRankingData($reportId, $parsedData);
            
            // Commit transaction
            $conn->commit();
            $result['success'] = true;
            
            // Delete temporary file
            $this->fileUploadService->deleteFile($filepath);
            
        } catch(\Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $result['error'] = 'Error during import: ' . $e->getMessage();
            
            // Clean up file on error
            $this->fileUploadService->deleteFile($filepath);
        }
        
        return $result;
    }
    
    /**
     * Create or update report entry
     * 
     * @param \PDO $conn Database connection
     * @param string $clientDomain Client domain
     * @param string $reportPeriod Report period
     * @param string $filename Original file name
     * @param bool $isBaseline Whether this is a baseline report
     * @return int Report ID
     */
    private function handleReportEntry(\PDO $conn, $clientDomain, $reportPeriod, $filename, $isBaseline) {
        // Clear any existing baseline if this is marked as baseline
        if ($isBaseline) {
            $stmt = $conn->prepare("UPDATE reports SET is_baseline = 0 WHERE client_domain = ?");
            $stmt->execute([$clientDomain]);
        }
        
        // Setup report model data
        $this->reportModel->client_domain = $clientDomain;
        $this->reportModel->report_period = $reportPeriod;
        $this->reportModel->import_date = date('Y-m-d');
        $this->reportModel->file_name = $filename;
        $this->reportModel->is_baseline = $isBaseline ? 1 : 0;
        
        if ($this->reportModel->exists($clientDomain, $reportPeriod)) {
            // Get existing report ID
            $stmt = $conn->prepare("SELECT report_id FROM reports WHERE client_domain = ? AND report_period = ?");
            $stmt->execute([$clientDomain, $reportPeriod]);
            $row = $stmt->fetch();
            
            if ($row) {
                // Update existing report
                $this->reportModel->report_id = $row['report_id'];
                $this->reportModel->update();
                return $row['report_id'];
            }
        }
        
        // Create new report
        if (!$this->reportModel->create()) {
            throw new \Exception("Failed to create report record");
        }
        return $this->reportModel->report_id;
    }
    
    /**
     * Clear existing ranking data for a report
     * 
     * @param int $reportId Report ID
     */
    private function clearExistingData($reportId) {
        foreach (EngineConfig::getEngineKeys() as $engine) {
            $this->rankingDataModel->clearReportData($reportId, $engine);
        }
    }
    
    /**
     * Save ranking data to database
     * 
     * @param int $reportId Report ID
     * @param array $parsedData Parsed ranking data
     */
    private function saveRankingData($reportId, $parsedData) {
        foreach ($parsedData as $row) {
            // Set common properties
            $this->rankingDataModel->report_id = $reportId;
            $this->rankingDataModel->keyword = $row['keyword'];
            $this->rankingDataModel->visibility = $row['visibility'];
            $this->rankingDataModel->visibility_difference = $row['visibility_difference'];
            
            // Save data for each engine
            foreach ($row['engines'] as $engine => $engineData) {
                $this->rankingDataModel->rank = $engineData['rank'];
                $this->rankingDataModel->previous_rank = $engineData['previous_rank'];
                $this->rankingDataModel->difference = $engineData['difference'];
                $this->rankingDataModel->serp_features = $engineData['serp_features'];
                $this->rankingDataModel->url = $engineData['url'];
                
                if (!$this->rankingDataModel->save($engine)) {
                    throw new \Exception("Failed to save ranking data for engine: " . $engine);
                }
            }
        }
    }
}