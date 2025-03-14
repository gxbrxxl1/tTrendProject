<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\TrendController;
use App\Controllers\ChatController;
use Dotenv\Dotenv;

// Initialize session
session_start();

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove the base path from the request URI
$basePath = '/myapp/test/public';
$request = str_replace($basePath, '', $request);

// Remove query strings
$request = strtok($request, '?');

// Remove trailing slash
$request = rtrim($request, '/');

// If empty request, set to root
if (empty($request)) {
    $request = '/';
}

// Redirect to login page if accessing root and not authenticated
if ($request === '/' && !isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit;
}

// Define routes
$routes = [
    '/' => ['controller' => HomeController::class, 'action' => 'index'],
    '/login' => ['controller' => AuthController::class, 'action' => 'login'],
    '/signup' => ['file' => 'signup.php'],
    '/register' => ['controller' => AuthController::class, 'action' => 'register'],
    '/logout' => ['file' => 'logout.php'],
    '/trends' => ['controller' => TrendController::class, 'action' => 'index'],
    '/trends/analyze' => ['controller' => TrendController::class, 'action' => 'analyze'],
    '/chat' => ['controller' => ChatController::class, 'action' => 'index'],
    '/chat/create' => ['controller' => ChatController::class, 'action' => 'create']
];

// Handle dynamic routes with parameters
if (preg_match('/^\/chat\/(\d+)$/', $request, $matches)) {
    $controller = new ChatController();
    $controller->show($matches[1]);
} elseif ($request === '/chat/send' && $method === 'POST') {
    $controller = new ChatController();
    $controller->sendMessage();
} elseif (preg_match('/^\/chat\/(\d+)\/rename$/', $request, $matches) && $method === 'POST') {
    $controller = new ChatController();
    $controller->rename($matches[1]);
} elseif (preg_match('/^\/chat\/message\/(\d+)\/delete$/', $request, $matches) && $method === 'POST') {
    $controller = new ChatController();
    $controller->deleteMessage($matches[1]);
} elseif (preg_match('/^\/chat\/(\d+)\/delete$/', $request, $matches)) {
    if ($method !== 'POST') {
        header("HTTP/1.0 405 Method Not Allowed");
        echo "Method Not Allowed";
        exit;
    }
    $controller = new ChatController();
    $controller->delete($matches[1]);
} elseif (isset($routes[$request])) {
    $route = $routes[$request];
    
    if (isset($route['template'])) {
        // Handle template inclusion
        require_once __DIR__ . '/' . $route['template'];
    } elseif (isset($route['file'])) {
        // Handle direct file inclusion
        require_once __DIR__ . '/' . $route['file'];
    } else {
        // Handle controller actions
        $controller = new $route['controller']();
        $action = $route['action'];
        $controller->$action();
    }
} else {
    // Handle 404
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
} 