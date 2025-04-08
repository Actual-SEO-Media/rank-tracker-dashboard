<?php
// Save this as check_files.php in your public_html directory
echo "Current directory: " . __DIR__ . "<br>";
echo "Router path: " . __DIR__ . "/app/Controllers/Router.php<br>";
echo "Router exists: " . (file_exists(__DIR__ . "/app/Controllers/Router.php") ? "Yes" : "No") . "<br>";

// Try to include the file directly
if (file_exists(__DIR__ . "/app/Controllers/Router.php")) {
    include_once __DIR__ . "/app/Controllers/Router.php";
    echo "Router file included. Class exists: " . (class_exists("App\\Controllers\\Router") ? "Yes" : "No") . "<br>";
} else {
    echo "Router file not found<br>";
}

// List all files in the Controllers directory
$controllersDir = __DIR__ . "/app/Controllers";
if (is_dir($controllersDir)) {
    echo "Controllers directory contents:<br>";
    $files = scandir($controllersDir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "- $file<br>";
        }
    }
} else {
    echo "Controllers directory not found<br>";
}
