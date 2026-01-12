<?php
namespace App\Middleware;

use App\Utils\Response;

/**
 * Rate limiting middleware
 */
class RateLimitMiddleware {
    public static function handle($key, $maxRequests = 60, $windowSeconds = 60) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $rateLimitKey = 'rate_limit_' . $key;
        $now = time();
        
        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = [
                'count' => 1,
                'window_start' => $now
            ];
            return;
        }
        
        $rateLimit = $_SESSION[$rateLimitKey];
        
        // Reset if window expired
        if ($now - $rateLimit['window_start'] >= $windowSeconds) {
            $_SESSION[$rateLimitKey] = [
                'count' => 1,
                'window_start' => $now
            ];
            return;
        }
        
        // Increment count
        $rateLimit['count']++;
        
        // Check if limit exceeded
        if ($rateLimit['count'] > $maxRequests) {
            $_SESSION[$rateLimitKey] = $rateLimit;
            Response::rateLimitExceeded('Too many requests. Please try again later.');
        }
        
        $_SESSION[$rateLimitKey] = $rateLimit;
    }
}
