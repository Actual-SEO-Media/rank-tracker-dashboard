<?php
namespace App\Controllers;

require_once __DIR__ . '/../Configs/Constants.php';

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    private $protectedRoutes = [];
    private $publicRoutes = ['/login']; // Always public

    /**
     * Register a GET route
     */
    public function get($route, $callback) {
        error_log("Registering GET route: " . $route);
        $this->routes['GET'][$route] = $callback;
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post($route, $callback) {
        error_log("Registering POST route: " . $route);
        $this->routes['POST'][$route] = $callback;
        return $this;
    }
    
    /**
     * Mark a route as protected
     */
    public function protected($route) {
        error_log("Marking route as protected: " . $route);
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
                error_log("Public route, no auth needed: " . $uri);
                return true; // Allow access to public routes
            }
        }
        
        // Force start the session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if the user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            error_log("Authentication failed for URI: " . $uri);
            
            // Store current URL for redirect after login
            $_SESSION['redirect_url'] = $uri;
            
            // Redirect to login
            header('Location: ' . SITE_URL . '/login');
            exit; // Critical - must exit to prevent further execution
        }
        
        return true;
    }

    /**
     * Dispatch the request
     */
    public function dispatch($method, $uri) {
        error_log("Dispatching request - Method: " . $method . ", Original URI: " . $uri);
        
        // Parse URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/'); // Remove trailing slashes
        
        // Remove BASE_URL from the URI
        if (strpos($uri, BASE_URL) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
        }
        
        // If URI is empty, treat as root
        if (empty($uri)) {
            $uri = '';
        }
        
        error_log("Final URI to match: " . $uri);
        
        // Ignore static files
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|woff|woff2|ttf|eot)$/i', $uri)) {
            return false;
        }
        
        // Check authentication for all routes except explicitly public ones
        $this->checkAuthentication($uri);
        
        // Route matching
        foreach ($this->routes[$method] as $route => $callback) {
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            
            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                error_log("Route matched: " . $route);
                array_shift($matches); // Remove full match
                return call_user_func_array($callback, $matches);
            }
        }

        // No route matched
        error_log("404 Not Found: " . $uri);
        http_response_code(404);
        echo "404 Not Found";
    }
}