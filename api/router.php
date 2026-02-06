<?php
/**
 * API Router
 * Handles routing for RESTful API endpoints
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load autoloader
require_once dirname(__DIR__) . '/app/autoload.php';

// Set namespace
use App\Controllers\{
    AuthController,
    EinsatzController,
    DokumentationController,
    PersonalController,
    DrohnenController,
    FlightsController
};

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string
$requestUri = strtok($requestUri, '?');

// Remove base path if exists
$basePath = '/api/v1';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove leading/trailing slashes
$requestUri = trim($requestUri, '/');

// Split into parts
$parts = explode('/', $requestUri);
$parts = array_filter($parts, function($part) {
    return !empty($part);
});
$parts = array_values($parts);

// Route mapping
$routes = [
    'qr' => [
        'controller' => null,
        'routes' => [
            '' => 'qr'
        ]
    ],
    'auth' => [
        'controller' => AuthController::class,
        'routes' => [
            'login' => 'login',
            'logout' => 'logout',
            'check' => 'check'
        ]
    ],
    'einsatz' => [
        'controller' => EinsatzController::class,
        'routes' => [
            '' => 'index',
            '{id}' => 'show',
            '{id}/complete' => 'complete',
            '{id}/pdf' => 'pdf',
            '{id}/dokumentation' => 'getByEinsatz'
        ]
    ],
    'dokumentation' => [
        'controller' => DokumentationController::class,
        'routes' => [
            '' => 'create',
            '{id}' => 'show'
        ]
    ],
    'personal' => [
        'controller' => PersonalController::class,
        'routes' => [
            '' => 'index',
            '{id}' => 'show'
        ]
    ],
    'drohnen' => [
        'controller' => DrohnenController::class,
        'routes' => [
            '' => 'index',
            '{id}' => 'show'
        ]
    ],
    'flights' => [
        'controller' => FlightsController::class,
        'routes' => [
            '' => 'create'
        ]
    ]
];

// Handle routing
if (empty($parts)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Endpoint not found']]);
    exit;
}

$resource = $parts[0];

// Special handling for QR code
if ($resource === 'qr') {
    require_once dirname(__DIR__) . '/api/v1/qr.php';
    exit;
}

// Special handling for install_notification
if ($resource === 'install_notification') {
    require_once dirname(__DIR__) . '/api/v1/install_notification.php';
    exit;
}
$resourceId = $parts[1] ?? null;
$action = $parts[2] ?? null;

// Check if resource exists
if (!isset($routes[$resource])) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found']]);
    exit;
}

$routeConfig = $routes[$resource];
$controllerClass = $routeConfig['controller'];

// Determine method based on HTTP method and route
$method = null;
$params = [];

if ($resourceId && $action) {
    // Special route like /einsatz/{id}/complete – match literal first, then pattern {id}/action
    $routeKey = $resourceId . '/' . $action;
    if (isset($routeConfig['routes'][$routeKey])) {
        $method = $routeConfig['routes'][$routeKey];
        $params = [$resourceId];
    } elseif (isset($routeConfig['routes']['{id}/' . $action])) {
        $method = $routeConfig['routes']['{id}/' . $action];
        $params = [$resourceId];
    } elseif (isset($routeConfig['routes'][$action])) {
        $method = $routeConfig['routes'][$action];
        $params = [$resourceId];
    }
} elseif ($resourceId) {
    // Check if resourceId is actually a named route (like 'login', 'logout', 'check' for auth)
    if (isset($routeConfig['routes'][$resourceId])) {
        // It's a named route, not an ID
        $method = $routeConfig['routes'][$resourceId];
    } elseif (isset($routeConfig['routes']['{id}'])) {
        // Route like /resource/{id}
        $method = $routeConfig['routes']['{id}'];
        $params = [$resourceId];
    }
} else {
    // Root route /resource
    if (isset($routeConfig['routes'][''])) {
        $method = $routeConfig['routes'][''];
    }
}

// If no method found, try to infer from HTTP method
if (!$method) {
    switch ($requestMethod) {
        case 'GET':
            $method = $resourceId ? 'show' : 'index';
            if ($resourceId) {
                $params = [$resourceId];
            }
            break;
        case 'POST':
            $method = 'create';
            break;
        case 'PUT':
        case 'PATCH':
            $method = 'update';
            if ($resourceId) {
                $params = [$resourceId];
            }
            break;
        case 'DELETE':
            $method = 'delete';
            if ($resourceId) {
                $params = [$resourceId];
            }
            break;
    }
}

// Special handling for nested routes
if ($resource === 'einsatz' && $resourceId && $action === 'dokumentation') {
    try {
        $controller = new DokumentationController();
        if ($requestMethod === 'GET') {
            $controller->getByEinsatz($resourceId);
        } elseif ($requestMethod === 'POST') {
            // For POST, we need to add einsatz_id to request data. Store in GLOBALS so
            // controller getRequestData() can use it (php://input can only be read once).
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $data = is_array($data) ? $data : [];
            $data['einsatz_id'] = $resourceId;
            $GLOBALS['_api_request_data'] = $data;
            $controller->create();
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (!$method || !method_exists($controllerClass, $method)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Method not found']]);
    exit;
}

// Store route params in global for controller access
$GLOBALS['route_params'] = $params;

// Instantiate controller and call method
try {
    $controller = new $controllerClass();
    call_user_func_array([$controller, $method], $params);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_ERROR',
            'message' => $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
