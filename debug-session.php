<?php
// Ensure errors are displayed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
require_once __DIR__ . '/app/Configs/AuthConfig.php';
require_once __DIR__ . '/app/Configs/Constants.php';

// Start session
session_start();

echo "<h1>Session Debug Information</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . " (2 means active)\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Cookie Parameters: \n";
print_r(session_get_cookie_params());
echo "\n\nSession Data:\n";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Path Information</h2>";
echo "<pre>";
echo "BASE_URL: " . BASE_URL . "\n";
echo "SITE_URL: " . SITE_URL . "\n";
echo "Current script: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "</pre>";

echo "<h2>Authentication Test</h2>";
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "<div style='color: green;'>You are logged in as: " . ($_SESSION['username'] ?? 'Unknown') . "</div>";
} else {
    echo "<div style='color: red;'>You are NOT logged in</div>";
}

echo "<p><a href='" . SITE_URL . "/login'>Go to login page</a></p>";
echo "<p><a href='" . SITE_URL . "'>Go to homepage</a></p>";