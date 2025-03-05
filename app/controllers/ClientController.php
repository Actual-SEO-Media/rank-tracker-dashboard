<?php
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Report.php';

class ClientController {
    private $clientModel;
    private $reportModel;
    
    public function __construct() {
        $this->clientModel = new Client();
        $this->reportModel = new Report();
    }
    
    // Display clients dashboard
    public function index() {
        // Get all clients
        $clientsResult = $this->clientModel->getAll();
        $clients = [];
        
        while ($row = $clientsResult->fetch_assoc()) {
            $domain = $row['client_domain'];
            $reports = $this->reportModel->getClientReports($domain);
            $totalReports = $reports->num_rows;
            $latestReport = $totalReports > 0 ? $reports->fetch_assoc() : null;
            
            $clients[] = [
                'domain' => $domain,
                'totalReports' => $totalReports,
                'latestReport' => $latestReport
            ];
        }
        
        // Include the view
        include __DIR__ . '/../views/clients/index.php';
    }
    
    // Display client reports
    public function reports($domain) {
        // Get client reports
        $reportsResult = $this->reportModel->getClientReports($domain);
        $reports = [];
        
        while ($row = $reportsResult->fetch_assoc()) {
            $reports[] = $row;
        }
        
        // Include the view
        include __DIR__ . '/../views/reports/index.php';
    }
}