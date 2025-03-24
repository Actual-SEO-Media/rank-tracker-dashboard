<?php
namespace App\Controllers;

use App\Models\Client;
use App\Models\Report;

class ClientController {
    private $clientModel;
    private $reportModel;
    
    public function __construct() {
        $this->clientModel = new Client();
        $this->reportModel = new Report();
    }
    
    /**
     * Displays the clients dashboard.
     * 
     * Retrieves all clients along with the total number of reports for each client 
     * and the latest report if available.
     */
    public function index() {
        $clientsData = $this->clientModel->getAll();
        $clients = [];
        
        foreach ($clientsData as $row) {
            $domain = $row['client_domain'];
            $reportsList = $this->reportModel->getClientReports($domain) ?? [];
            
            $totalReports = count($reportsList);
            $latestReport = $totalReports > 0 ? $reportsList[0] : null;
            
            $clients[] = [
                'domain' => $domain,
                'totalReports' => $totalReports,
                'latestReport' => $latestReport
            ];
        }
        
        include __DIR__ . '/../views/clients/index.php';
    }
    
    /**
     * Displays reports for a specific client.
     * 
     * @param string $domain The domain of the client whose reports are being retrieved.
     */
    public function reports($domain) {
        $reports = $this->reportModel->getClientReports($domain);
        include __DIR__ . '/../views/reports/index.php';
    }
} 