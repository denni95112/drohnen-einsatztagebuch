<?php
namespace App\Controllers;

use App\Utils\Response;

/**
 * Base controller class
 */
abstract class BaseController {
    /**
     * Get request data
     */
    protected function getRequestData() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if ($method === 'GET') {
            return $_GET;
        }
        
        // For POST, PUT, DELETE
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return $data ?? [];
        }
        
        return $_POST;
    }
    
    /**
     * Get request method
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Get route parameters
     */
    protected function getRouteParams() {
        // This will be populated by the router
        return $GLOBALS['route_params'] ?? [];
    }
    
    /**
     * Check if request is JSON
     */
    protected function isJsonRequest() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
    
    /**
     * Send success response
     */
    protected function success($data = null, $message = 'Operation successful', $statusCode = 200) {
        Response::success($data, $message, $statusCode);
    }
    
    /**
     * Send error response
     */
    protected function error($message, $code = 'ERROR', $statusCode = 400, $details = null) {
        Response::error($message, $code, $statusCode, $details);
    }
}
