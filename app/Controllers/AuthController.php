<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\Validator;
use App\Utils\Response;

/**
 * Authentication controller
 */
class AuthController extends BaseController {
    /**
     * Login
     */
    public function login() {
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        $password = $data['password'] ?? '';
        
        if (empty($password)) {
            $this->error('Password is required', 'VALIDATION_ERROR', 400);
        }
        
        if (AuthService::login($password)) {
            $this->success([
                'authenticated' => true,
                'is_admin' => AuthService::isAdminAuthenticated()
            ], 'Login successful');
        } else {
            $this->error('Invalid password', 'INVALID_CREDENTIALS', 401);
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        // Allow both GET and POST for backward compatibility
        AuthService::logout();
        
        // For web requests, redirect to login
        if (!$this->isJsonRequest()) {
            header('Location: /public/index.php?page=login');
            exit;
        }
        
        $this->success(null, 'Logout successful');
    }
    
    /**
     * Check authentication status
     */
    public function check() {
        if ($this->getMethod() !== 'GET') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $this->success([
            'authenticated' => AuthService::isAuthenticated(),
            'is_admin' => AuthService::isAdminAuthenticated()
        ]);
    }
}
