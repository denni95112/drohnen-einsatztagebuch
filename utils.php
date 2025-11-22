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
    return (PHP_OS_FAMILY === 'Windows' && (preg_match('/^[A-Z]:[\\\\\/]/i', $path) || preg_match('/^\\\\/', $path))) 
        || (PHP_OS_FAMILY !== 'Windows' && $path[0] === '/');
}

/**
 * Get versioned asset URL (for cache busting)
 * 
 * @param string $path Relative path to the asset (e.g., 'css/styles.css')
 * @return string Path with version query parameter appended
 */
function getVersionedAsset($path) {
    static $version = null;
    
    if ($version === null) {
        $configPath = __DIR__ . '/config/config.php';
        $config = [];
        if (file_exists($configPath)) {
            $config = require $configPath;
        }
        $version = isset($config['version']) ? $config['version'] : '1.0.0';
    }
    
    $separator = strpos($path, '?') !== false ? '&' : '?';
    return $path . $separator . 'v=' . urlencode($version);
}

