<?php
namespace App\Utils;

/**
 * Standardized API response handler
 */
class Response {
    /**
     * Send success response
     */
    public static function success($data = null, $message = 'Operation successful', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function error($message, $code = 'ERROR', $statusCode = 400, $details = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
        
        if ($details !== null) {
            $response['error']['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send created response
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        self::success($data, $message, 201);
    }
    
    /**
     * Send not found response
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 'NOT_FOUND', 404);
    }
    
    /**
     * Send unauthorized response
     */
    public static function unauthorized($message = 'Authentication required') {
        self::error($message, 'UNAUTHORIZED', 401);
    }
    
    /**
     * Send forbidden response
     */
    public static function forbidden($message = 'Insufficient permissions') {
        self::error($message, 'FORBIDDEN', 403);
    }
    
    /**
     * Send validation error response
     */
    public static function validationError($message = 'Validation failed', $errors = []) {
        self::error($message, 'VALIDATION_ERROR', 422, ['validation_errors' => $errors]);
    }
    
    /**
     * Send rate limit response
     */
    public static function rateLimitExceeded($message = 'Too many requests') {
        self::error($message, 'RATE_LIMIT_EXCEEDED', 429);
    }
}
