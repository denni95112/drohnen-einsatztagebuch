<?php
namespace App\Services;

/**
 * Authentication service
 * Extracted from auth.php
 */
class AuthService {
    private static $config = null;
    
    private static function getConfig() {
        if (self::$config === null) {
            $configPath = dirname(__DIR__, 2) . '/config/config.php';
            if (file_exists($configPath)) {
                self::$config = include $configPath;
            } else {
                self::$config = [];
            }
        }
        return self::$config;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $config = self::getConfig();
        
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            return true;
        }
        
        if (isset($_COOKIE[$config['token_name']])) {
            $password = self::getPasswordFromCookie();
            if (hash('sha256', $password) === $config['password_hash']) {
                $_SESSION['loggedin'] = true;
                return true;
            } else {
                $_SESSION['loggedin'] = false;
            }
        }
        return false;
    }
    
    /**
     * Check if admin is authenticated
     */
    public static function isAdminAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $config = self::getConfig();
        
        if (isset($_SESSION['adminloggedin']) && $_SESSION['adminloggedin'] === true) {
            return true;
        }
        
        if (isset($_COOKIE[$config['token_name']])) {
            $password = self::getPasswordFromCookie();
            if (hash('sha256', $password) === $config['admin_password_hash']) {
                $_SESSION['adminloggedin'] = true;
                return true;
            } else {
                $_SESSION['adminloggedin'] = false;
            }
        }
        return false;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: /public/index.php?page=login');
            exit();
        }
    }
    
    /**
     * Require admin authentication
     */
    public static function requireAdminAuth() {
        if (!self::isAdminAuthenticated()) {
            header('Location: /public/index.php?page=index');
            exit();
        }
    }
    
    /**
     * Set login cookie
     */
    public static function setLoginCookie($password) {
        $config = self::getConfig();
        $cookie_value = base64_encode($password);
        setcookie($config['token_name'], $cookie_value, time() + (30 * 24 * 60 * 60), "/");
    }
    
    /**
     * Get password from cookie
     */
    private static function getPasswordFromCookie() {
        $config = self::getConfig();
        if (!isset($_COOKIE[$config['token_name']])) {
            return '';
        }
        $cookie_value = $_COOKIE[$config['token_name']];
        return base64_decode($cookie_value);
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        $config = self::getConfig();
        setcookie($config['token_name'], '', time() - 3600, "/");
        session_destroy();
    }
    
    /**
     * Login user
     */
    public static function login($password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $config = self::getConfig();
        $passwordHash = hash('sha256', $password);
        
        if ($passwordHash === $config['password_hash'] || $passwordHash === $config['admin_password_hash']) {
            $_SESSION['loggedin'] = true;
            
            if ($passwordHash === $config['admin_password_hash']) {
                $_SESSION['adminloggedin'] = true;
            }
            
            self::setLoginCookie($password);
            session_write_close();
            return true;
        }
        
        return false;
    }
}
