<?php
/**
 * Bootstrap file
 * Loads all necessary dependencies for backward compatibility
 */

// Load autoloader
require_once __DIR__ . '/app/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load includes
require_once __DIR__ . '/includes/error_reporting.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/changelog_data.php';

// Load compatibility wrappers
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

// Load version check function
require_once __DIR__ . '/version_check.php';

// Make sure config is available
if (!isset($config)) {
    $configPath = __DIR__ . '/config/config.php';
    if (file_exists($configPath)) {
        $config = include $configPath;
    } else {
        $config = [];
    }
}

// Make sure getVersionedAsset function exists
if (!function_exists('getVersionedAsset')) {
    require_once __DIR__ . '/utils.php';
}

// Make sure updateConfig function exists
if (!function_exists('updateConfig')) {
    require_once __DIR__ . '/utils.php';
}

// Make sure checkForNewVersion function exists
if (!function_exists('checkForNewVersion')) {
    require_once __DIR__ . '/version_check.php';
}
