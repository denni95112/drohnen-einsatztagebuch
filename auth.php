<?php
session_start();

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
    $config = getConfig();

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        return true;
    }

    if (isset($_COOKIE[$config['token_name']])) {
        if (hash('sha256', getPasswortFromCookie()) === $config['password_hash']) {
            $_SESSION['loggedin'] = true;
            return true;
        }else{
            $_SESSION['loggedin'] = false;
        }
    }
    return false;
}

/**
 * Check if admin user is authenticated
 *
 * @return bool True if admin authenticated, false otherwise
 */
function isAdminAuthenticated() {
    $config = getConfig();

    if (isset($_SESSION['adminloggedin']) && $_SESSION['adminloggedin'] === true) {
        return true;
    }

    if (isset($_COOKIE[$config['token_name']])) {
        if (hash('sha256', getPasswortFromCookie()) === $config['admin_password_hash']) {
            $_SESSION['adminloggedin'] = true;
            return true;
        }else{
            $_SESSION['adminloggedin'] = false;
        }
    }
    return false;
}

/**
 * Require authentication, redirect to login if not authenticated
 *
 * @return void
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require admin authentication, redirect to index if not authenticated
 *
 * @return void
 */
function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Set login cookie with password
 *
 * @param string $password Password to encode and store
 * @return void
 */
function setLoginCookie($password) {
    $config = getConfig();
    $cookie_value = base64_encode($password);
    setcookie($config['token_name'], $cookie_value, time() + (30 * 24 * 60 * 60), "/");
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
    $config = getConfig();
    setcookie($config['token_name'], '', time() - 3600, "/");
    session_destroy();
}
?>
