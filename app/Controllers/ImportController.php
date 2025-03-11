<?php
namespace App\Controllers;

use App\Services\ImportService;

class ImportController {
    private $importService;
    
    public function __construct() {
        $this->importService = new ImportService();
    }
    
    /**
     * Displays the import form and handles form submission.
     * If a file is uploaded, it processes the import and returns success or error messages.
     */ 
    public function index($domain) {
        $success = false;
        $success_message = '';
        $error = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handleFormSubmission();
            $success = $result['success'];
            $error = $result['error'];
            $domain = $result['domain'] ?? $domain;
            $report_id = $result['report_id'] ?? null;
          
        }
        
        // Include the view
        include __DIR__ . '/../views/import/index.php';
    }
    
    /**
     * Validate form data and call import service
     * 
     * @return array Result with success status, error message, and report details
     */
    private function handleFormSubmission() {
        // Validate input
        if (empty($_POST['client_domain'])) {
            return [
                'success' => false,
                'error' => 'Client domain is required. Please enter a domain name.',
                'domain' => $_POST['client_domain'] ?? ''
            ];
        }
        
        if (empty($_POST['report_period'])) {
            return [
                'success' => false,
                'error' => 'Report period is required. Please select a date range.',
                'domain' => $_POST['client_domain']
            ];
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
            return [
                'success' => false,
                'error' => 'Invalid file. Please upload a valid CSV file.',
                'domain' => $_POST['client_domain']
            ];
        }
        
        
        // Get form data
        $clientDomain = trim($_POST['client_domain']);
        $reportPeriod = trim($_POST['report_period']);
        $isBaseline = isset($_POST['is_baseline']) ? true : false;
        
        // Call service to handle import
        return $this->importService->importRankingData(
            $_FILES['csv_file'],
            $clientDomain,
            $reportPeriod,
            $isBaseline
        );
    }
}