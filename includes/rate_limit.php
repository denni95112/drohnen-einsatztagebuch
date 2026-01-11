<?php
/**
 * Rate limiting functions
 */

/**
 * Check rate limit
 * @param string $key Rate limit key
 * @param int $maxRequests Maximum requests
 * @param int $windowSeconds Time window in seconds
 * @return bool True if rate limit exceeded
 */
function checkRateLimit(string $key, int $maxRequests, int $windowSeconds): bool {
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
        return false;
    }
    
    $rateLimit = $_SESSION[$rateLimitKey];
    
    // Reset if window expired
    if ($now - $rateLimit['window_start'] >= $windowSeconds) {
        $_SESSION[$rateLimitKey] = [
            'count' => 1,
            'window_start' => $now
        ];
        return false;
    }
    
    // Increment count
    $rateLimit['count']++;
    
    // Check if limit exceeded
    if ($rateLimit['count'] > $maxRequests) {
        $_SESSION[$rateLimitKey] = $rateLimit;
        return true;
    }
    
    $_SESSION[$rateLimitKey] = $rateLimit;
    return false;
}
