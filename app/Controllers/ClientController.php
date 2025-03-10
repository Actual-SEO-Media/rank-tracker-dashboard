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
        $clientsResult = $this->clientModel->getAll();
        $clients = [];
        
        while ($row = $clientsResult->fetch(\PDO::FETCH_ASSOC)) {
            $domain = $row['client_domain'];
            $reports = $this->reportModel->getClientReports($domain);
            $totalReports = $reports->rowCount();
            $latestReport = $totalReports > 0 ? $reports->fetch(\PDO::FETCH_ASSOC) : null;
            
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
        $reportsResult = $this->reportModel->getClientReports($domain);
        $reports = [];
        
        while ($row = $reportsResult->fetch(\PDO::FETCH_ASSOC)) {
            $reports[] = $row;
        }
        
        include __DIR__ . '/../views/reports/index.php';
    }
} 