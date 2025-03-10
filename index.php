<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\ClientController;
use App\Controllers\ReportController;
use App\Controllers\ImportController;

$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;

$clientController = new ClientController();
$reportController = new ReportController();
$importController = new ImportController();

switch ($action) {
    case 'home':
        // Default homepage - show client list
        $clientController->index();
        break;
        
    case 'reports':
        // Show reports for a specific client
        if ($domain) {
            $clientController->reports($domain);
        } else {
            // Redirect to homepage if no domain specified
            header('Location: index.php');
            exit;
        }
        break;
        
    case 'details':
        // Show detailed report view
        if ($id) {
            $reportController->details($id);
        } else {
            // Redirect to homepage if no ID specified
            header('Location: index.php');
            exit;
        }
        break;

    case 'keywords':
        // Show keywordpres for a report
        if ($id) {
            $reportController->keywords($id);
        } else {
            // Redirect to homepage if no ID specified
            header('Location: index.php');
            exit;
        }
        break;
        
    case 'positions':
        // Show search positions for a report
        if ($id) {
            $reportController->positions($id);
        } else {
            // Redirect to homepage if no ID specified
            header('Location: index.php');
            exit;
        }
        break;
        
    case 'import':
        // Show import form or process import
        $importController->index();
        break;
        
    default:
        // Handle unknown actions
        header('HTTP/1.0 404 Not Found');
        echo '404 - Page not found';
        break;
}