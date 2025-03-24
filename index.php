<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);

// Load configuration
require_once __DIR__ . '/app/Configs/AuthConfig.php';
require __DIR__ . '/vendor/autoload.php';

use App\Controllers\Router;
use App\Controllers\ClientController;
use App\Controllers\ReportController;
use App\Controllers\ImportController;
use App\Controllers\UserController;

// Initialize router
$router = new Router();

// Public routes (no authentication required)
$router->get("/login", function () {
    $userController = new UserController();
    $userController->showLogin();
});

$router->post("/login", function () {
    $userController = new UserController();
    $userController->login();
});

$router->get("/logout", function () {
    $userController = new UserController();
    $userController->logout();
});

// All other routes - simplified approach
// Note: You don't need to call protected() since our new Router 
// protects everything except explicitly public routes

// Default homepage - show client list
$router->get("", function () {
    $clientController = new ClientController();
    $clientController->index();
});

// Client reports
$router->get("/reports/{domain}", function ($domain) {
    $clientController = new ClientController();
    $clientController->reports(htmlspecialchars($domain));
});

// Import routes
$router->post("/import", function () {
    $importController = new ImportController();
    $importController->index('');
});

$router->get("/import", function () {
    $importController = new ImportController();
    $importController->index('');
});

$router->get("/import/{domain}", function ($domain) {
    $importController = new ImportController();
    $importController->index(htmlspecialchars($domain));
});

// Report details, positions, and keywords
$router->get("/details/{report_id}", function ($report_id) {
    $reportController = new ReportController();
    $reportController->details(htmlspecialchars($report_id));
});

$router->get("/positions/{report_id}", function ($report_id) {
    $reportController = new ReportController();
    $reportController->positions(htmlspecialchars($report_id));
});

$router->get("/keywords/{report_id}", function ($report_id) {
    $reportController = new ReportController();
    $reportController->keywords(htmlspecialchars($report_id));
});

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);