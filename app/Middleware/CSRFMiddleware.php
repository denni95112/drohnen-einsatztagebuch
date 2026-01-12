<?php
namespace App\Middleware;

use App\Utils\Response;

/**
 * CSRF protection middleware
 */
class CSRFMiddleware {
    public static function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Skip CSRF for GET requests
        if ($method === 'GET') {
            return;
        }
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            Response::error('CSRF token validation failed', 'CSRF_ERROR', 403);
        }
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get CSRF token
     */
    public static function getToken() {
        return self::generateToken();
    }
}
