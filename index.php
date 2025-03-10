<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\Router;
use App\Controllers\ClientController;
use App\Controllers\ReportController;
use App\Controllers\ImportController;

$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$domain = isset($_GET['domain']) ? $_GET['domain'] : null;

$router = new Router();
$reportController = new ReportController();
$importController = new ImportController();

$root_directory = 'rank-tracker-dashboard';

// Define GET routes
$router->get("{$root_directory}", function () {
    $clientController = new ClientController();

    $clientController->index();
});

$router->get("{$root_directory}/reports/{domain}", function ($domain) {
    echo "Tracking Rank for ID: " . htmlspecialchars($id);
});

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);