<?php
namespace App\Middleware;

use App\Services\AuthService;
use App\Utils\Response;

/**
 * Authentication middleware
 */
class AuthMiddleware {
    public static function handle($requireAdmin = false) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($requireAdmin) {
            if (!AuthService::isAdminAuthenticated()) {
                Response::forbidden('Admin access required');
            }
        } else {
            if (!AuthService::isAuthenticated()) {
                Response::unauthorized('Authentication required');
            }
        }
    }
}
