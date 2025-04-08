<?php
// File: /home/analyticsactuals/env/db_config.php
// Keep this file outside web root for security

// Application environment
define('APP_ENV', 'production');
define('APP_DEBUG', true);

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'analyticsactuals_reports');
define('DB_USER', 'analyticsactuals_root');
define('DB_PASS', 'CyF+L&s.kkE(');
define('DB_PERSISTENT', false);

// Application URL constants
define('BASE_URL', '');
define('SITE_URL', 'https://analytics.actualseomedia.com');

// Session and security settings
define('SESSION_SECURE', true);
define('SESSION_DOMAIN', 'localhost');
define('SESSION_LIFETIME', 86400);
define('SESSION_TIMEOUT', 1800);
define('AUTH_TIMEOUT', 3600);

// File upload settings
define('UPLOAD_MAX_SIZE', 2097152);
define('UPLOAD_ALLOWED_TYPES', 'csv,xlsx,xls');
