<?php

// Set error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base path
define('BASE_PATH', __DIR__);

// Include controllers
require_once BASE_PATH . '/app/controllers/ClientController.php';
require_once BASE_PATH . '/app/controllers/ReportController.php';
require_once BASE_PATH . '/app/controllers/ImportController.php';

// Simple router
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;

// Initialize controllers
$clientController = new ClientController();
$reportController = new ReportController();
$importController = new ImportController();

// Route to the appropriate controller and action
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