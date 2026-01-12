<?php
namespace App\Utils;

use PDO;
use PDOException;

/**
 * Database connection wrapper
 */
class Database {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        
        if (file_exists($configPath)) {
            $config = require $configPath;
            $databasePath = $config['database_path'] ?? 'einsatzbuch.db';
        } else {
            $databasePath = 'einsatzbuch.db';
        }
        
        // Handle absolute paths
        if (!is_absolute_path($databasePath)) {
            $databasePath = dirname(__DIR__, 2) . '/' . $databasePath;
        }
        
        $dbDir = dirname($databasePath);
        if ($dbDir !== '.' && $dbDir !== '' && !is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        try {
            $this->db = new PDO('sqlite:' . $databasePath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeSchema();
        } catch (PDOException $e) {
            die("Datenbankfehler: Konnte keine Verbindung zur Datenbank herstellen. " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    private function initializeSchema() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS personal (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vorname TEXT,
            nachname TEXT,
            dashboard_id INTEGER
        )");
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS einsatz (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            einsatznummer TEXT,
            adresse TEXT,
            gps_lat TEXT,
            gps_lng TEXT,
            einsatzart TEXT,
            gruppenfuehrer_id INTEGER,
            dokumentierende_id INTEGER,
            startzeit DATETIME,
            endzeit DATETIME DEFAULT NULL
        )");
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS einsatz_personal (
            einsatz_id INTEGER,
            personal_id INTEGER
        )");
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS einsatz_dokumentation (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            einsatz_id INTEGER,
            zeilennummer INTEGER,
            zeitpunkt DATETIME,
            text TEXT
        )");
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS drohnen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");
        
        // Create indexes
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_personal_einsatz_id ON einsatz_personal(einsatz_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_personal_personal_id ON einsatz_personal(personal_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_dokumentation_einsatz_id ON einsatz_dokumentation(einsatz_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_dokumentation_einsatz_zeile ON einsatz_dokumentation(einsatz_id, zeilennummer)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_startzeit ON einsatz(startzeit)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_personal_dashboard_id ON personal(dashboard_id)");
    }
}

/**
 * Helper function for absolute path check
 */
function is_absolute_path($path) {
    if (empty($path)) {
        return false;
    }
    return (PHP_OS_FAMILY === 'Windows' && (preg_match('/^[A-Z]:[\\\\\/]/i', $path) || preg_match('/^\\\\/', $path))) 
        || (PHP_OS_FAMILY !== 'Windows' && $path[0] === '/');
}
