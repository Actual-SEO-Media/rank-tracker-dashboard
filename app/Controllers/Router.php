<?php
namespace App\Controllers;

use App\Configs\Config;

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    private $protectedRoutes = [];
    private $publicRoutes = ['/login']; // Always public
    private $baseUrl;
    private $config;
    
    public function __construct() {
        // Get configuration
        $this->config = Config::getInstance();
        $this->baseUrl = $this->config->get('base_url');
        
        if ($this->config->isDebug()) {
            error_log("Router initialized with base URL: " . $this->baseUrl);
        }
    }

    /**
     * Register a GET route
     */
    public function get($route, $callback) {
        if ($this->config->isDebug()) {
            error_log("Registering GET route: " . $route);
        }
        $this->routes['GET'][$route] = $callback;
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post($route, $callback) {
        if ($this->config->isDebug()) {
            error_log("Registering POST route: " . $route);
        }
        $this->routes['POST'][$route] = $callback;
        return $this;
    }
    
    /**
     * Mark a route as protected
     */
    public function protected($route) {
        if ($this->config->isDebug()) {
            error_log("Marking route as protected: " . $route);
        }
        $this->protectedRoutes[] = $route;
        return $this;
    }

    /**
     * Force authentication check
     */
    private function checkAuthentication($uri) {
        // First check if this is a public route
        foreach ($this->publicRoutes as $publicRoute) {
            if ($uri === $publicRoute || strpos($uri, $publicRoute) === 0) {
                if ($this->config->isDebug()) {
                    error_log("Public route, no auth needed: " . $uri);
                }
                return true; // Allow access to public routes
            }
        }
        
        // Force start the session if not already started
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        // Check if the user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            if ($this->config->isDebug()) {
                error_log("Authentication failed for URI: " . $uri);
            }
            
            // Store current URL for redirect after login
            $_SESSION['redirect_url'] = $uri;
            
            // Get the site URL from configuration and ensure it doesn't end with a slash
            $siteUrl = rtrim($this->config->get('site_url'), '/');
            
            // Redirect to login, ensuring only one slash between site URL and login path
            if (!headers_sent()) {
                header('Location: ' . $siteUrl . '/login');
                exit; // Critical - must exit to prevent further execution
            } else {
                // Headers already sent, display message instead
                echo "<div style='text-align:center; margin-top:50px;'>";
                echo "<h2>Authentication Required</h2>";
                echo "<p>You need to be logged in to access this page.</p>";
                echo "<p><a href='" . $siteUrl . "/login'>Click here to login</a></p>";
                echo "</div>";
                exit;
            }
        }
        
        return true;
    }

    /**
     * Dispatch the request
     */
    public function dispatch($method, $uri) {
        if ($this->config->isDebug()) {
            error_log("Dispatching request - Method: " . $method . ", Original URI: " . ($uri ?? 'null'));
        }
        
        // Ensure $uri is a string
        if ($uri === null) {
            $uri = '';
        }
        
        // Parse URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        if ($uri === null) {
            $uri = '';
        }
        
        $uri = rtrim($uri, '/'); // Remove trailing slashes
        
        // Remove BASE_URL from the URI if present
        if (!empty($this->baseUrl) && strpos($uri, $this->baseUrl) === 0) {
            $uri = substr($uri, strlen($this->baseUrl));
        }
        
        // If URI is empty, treat as root
        if (empty($uri)) {
            $uri = '';
        }
        
        if ($this->config->isDebug()) {
            error_log("Final URI to match: " . $uri);
        }
        
        // Check if this is a static file request
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico)$/i', $uri)) {
            if ($this->config->isDebug()) {
                error_log("Static file request - not routing: " . $uri);
            }
            return false;
        }
        
        // Check authentication for all routes except explicitly public ones
        $this->checkAuthentication($uri);
        
        // Check if the method exists in routes
        if (!isset($this->routes[$method])) {
            if ($this->config->isDebug()) {
                error_log("Method not supported: " . $method);
            }
            http_response_code(405);
            echo "405 Method Not Allowed";
            return;
        }
        
        // Route matching
        foreach ($this->routes[$method] as $route => $callback) {
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            
            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                if ($this->config->isDebug()) {
                    error_log("Route matched: " . $route);
                }
                array_shift($matches); // Remove full match
                return call_user_func_array($callback, $matches);
            }
        }

        // No route matched
        if ($this->config->isDebug()) {
            error_log("404 Not Found: " . $uri);
        }
        http_response_code(404);
        echo "404 Not Found";
    }
}