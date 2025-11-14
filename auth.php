<?php
session_start();

// Load config once and cache it
static $app_config = null;
if ($app_config === null) {
    $configPath = __DIR__ . '/config/config.php';
    if (file_exists($configPath)) {
        $app_config = include $configPath;
    } else {
        $app_config = [];
    }
}

// Function to get config (optimized - loads once)
function getConfig() {
    global $app_config;
    return $app_config;
}

// Function to check if the user is authenticated based on session or cookie
function isAuthenticated() {
    $config = getConfig();
    // Check if session is set for logged in

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        return true;
    }

    // Check if the login cookie exists
    if (isset($_COOKIE[$config['token_name']])) {
        if (hash('sha256', getPasswortFromCookie()) === $config['password_hash']) {
            $_SESSION['loggedin'] = true; // Set session based on cookie validation
            return true;
        }else{
            $_SESSION['loggedin'] = false;
        }
    }
    return false;
}

function isAdminAuthenticated() {
    $config = getConfig();
    // Check if session is set for logged in

    if (isset($_SESSION['adminloggedin']) && $_SESSION['adminloggedin'] === true) {
        return true;
    }

    // Check if the login cookie exists
    if (isset($_COOKIE[$config['token_name']])) {
        if (hash('sha256', getPasswortFromCookie()) === $config['admin_password_hash']) {
            $_SESSION['adminloggedin'] = true; // Set session based on cookie validation
            return true;
        }else{
            $_SESSION['adminloggedin'] = false;
        }
    }
    return false;
}

// Function to require authentication (redirect if not authenticated)
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        header('Location: index.php');
        exit();
    }
}

function setLoginCookie($password) {
    $config = getConfig();
    // Base64 encode the password
    $cookie_value = base64_encode($password);

    // Set the cookie with a 30-day expiration
    setcookie($config['token_name'], $cookie_value, time() + (30 * 24 * 60 * 60), "/");
}

function getPasswortFromCookie() {
    $config = getConfig();
    $cookie_value = $_COOKIE[$config['token_name']];
    return base64_decode($cookie_value);
}


// Function to log the user out and delete the cookie
function logout() {
    $config = getConfig();
    setcookie($config['token_name'], '', time() - 3600, "/");
    session_destroy();
}
?>
