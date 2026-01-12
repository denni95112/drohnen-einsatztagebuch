<?php
/**
 * Database connection
 * Updated to use Database class while maintaining backward compatibility
 */

// Load autoloader if not already loaded
if (!class_exists('App\Utils\Database')) {
    require_once __DIR__ . '/app/autoload.php';
}

use App\Utils\Database;

// Get database connection
$db = Database::getInstance()->getConnection();
