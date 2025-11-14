<?php
/**
 * Utility functions shared across the application
 */

/**
 * Check if a path is absolute (Windows: C:\ or \\, Unix: /)
 * 
 * @param string $path The path to check
 * @return bool True if absolute, false if relative
 */
function is_absolute_path($path) {
    if (empty($path)) {
        return false;
    }
    // Check if path is absolute (Windows: C:\ or \\, Unix: /)
    return (PHP_OS_FAMILY === 'Windows' && (preg_match('/^[A-Z]:[\\\\\/]/i', $path) || preg_match('/^\\\\/', $path))) 
        || (PHP_OS_FAMILY !== 'Windows' && $path[0] === '/');
}

