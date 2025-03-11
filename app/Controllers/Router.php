<?php
namespace App\Controllers;

require_once __DIR__ . '/../Configs/Constants.php';

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($route, $callback) {
        $this->routes['GET'][BASE_URL . $route] = $callback;
    }

    public function post($route, $callback) {
        $this->routes['POST'][BASE_URL . $route] = $callback;
    }

    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/'); // Remove trailing slashes
        
        // Ignore requests for static files
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|woff|woff2|ttf|eot)$/i', $uri)) {
            return false; // Let the server handle static files
        }
        
        foreach ($this->routes[$method] as $route => $callback) {
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                array_shift($matches); // Remove full match
                return call_user_func_array($callback, $matches);
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}