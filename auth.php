<?php
/**
 * Authentication functions
 * Updated to use AuthService while maintaining backward compatibility
 */

// Load autoloader if not already loaded
if (!class_exists('App\Services\AuthService')) {
    require_once __DIR__ . '/app/autoload.php';
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Services\AuthService;

/**
 * Load config once and cache it
 */
static $app_config = null;
if ($app_config === null) {
    $configPath = __DIR__ . '/config/config.php';
    if (file_exists($configPath)) {
        $app_config = include $configPath;
    } else {
        $app_config = [];
    }
}

/**
 * Get application configuration
 *
 * @return array Configuration array
 */
function getConfig() {
    global $app_config;
    return $app_config;
}

/**
 * Check if the user is authenticated based on session or cookie
 *
 * @return bool True if authenticated, false otherwise
 */
function isAuthenticated() {
    return AuthService::isAuthenticated();
}

/**
 * Check if admin user is authenticated
 *
 * @return bool True if admin authenticated, false otherwise
 */
function isAdminAuthenticated() {
    return AuthService::isAdminAuthenticated();
}

/**
 * Require authentication, redirect to login if not authenticated
 *
 * @return void
 */
function requireAuth() {
    AuthService::requireAuth();
}

/**
 * Require admin authentication, redirect to index if not authenticated
 *
 * @return void
 */
function requireAdminAuth() {
    AuthService::requireAdminAuth();
}

/**
 * Set login cookie with password
 *
 * @param string $password Password to encode and store
 * @return void
 */
function setLoginCookie($password) {
    AuthService::setLoginCookie($password);
}

/**
 * Get password from cookie
 *
 * @return string Decoded password or empty string if cookie not set
 */
function getPasswortFromCookie() {
    $config = getConfig();
    if (!isset($_COOKIE[$config['token_name']])) {
        return '';
    }
    $cookie_value = $_COOKIE[$config['token_name']];
    return base64_decode($cookie_value);
}

/**
 * Log out user and delete cookie
 *
 * @return void
 */
function logout() {
    AuthService::logout();
}
