<?php
namespace App\Controllers;

require_once __DIR__ . '/../Configs/Constants.php';

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($route, $callback) {
        error_log("Registering GET route: " . $route);
        $this->routes['GET'][$route] = $callback;
    }

    public function post($route, $callback) {
        error_log("Registering POST route: " . $route);
        $this->routes['POST'][$route] = $callback;
    }

    public function dispatch($method, $uri) {
        error_log("Dispatching request - Method: " . $method . ", Original URI: " . $uri);
        
        // Remove BASE_URL from the beginning of the URI if it exists
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/'); // Remove trailing slashes
        
        error_log("BASE_URL: " . BASE_URL);
        error_log("Parsed URI after rtrim: " . $uri);
        
        if (strpos($uri, BASE_URL) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
            error_log("URI after removing BASE_URL: " . $uri);
        }
        
        // If URI is empty after removing BASE_URL, treat it as root
        if (empty($uri)) {
            $uri = '';
        }
        
        error_log("Final URI to match: " . $uri);
        error_log("Available routes for " . $method . ": " . print_r($this->routes[$method], true));
        
        // Ignore requests for static files
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|woff|woff2|ttf|eot)$/i', $uri)) {
            error_log("Static file request - ignoring");
            return false; // Let the server handle static files
        }
        
        foreach ($this->routes[$method] as $route => $callback) {
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            error_log("Checking route pattern: " . $routePattern . " against URI: " . $uri);
            
            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                error_log("Route matched! Executing callback");
                array_shift($matches); // Remove full match
                return call_user_func_array($callback, $matches);
            }
        }

        error_log("No route matched - 404 Not Found");
        http_response_code(404);
        echo "404 Not Found";
    }
}