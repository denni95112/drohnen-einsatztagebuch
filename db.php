<?php
$databasePath = 'einsatzbuch.db';
$configPath = __DIR__ . '/config/config.php';

if (file_exists($configPath)) {
    $config = require $configPath;
    if (isset($config['database_path'])) {
        $databasePath = $config['database_path'];
    }
}

$dbDir = dirname($databasePath);
if ($dbDir !== '.' && $dbDir !== '' && !is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $db = new PDO('sqlite:' . $databasePath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankfehler: Konnte keine Verbindung zur Datenbank herstellen. " . $e->getMessage());
}

$db->exec("CREATE TABLE IF NOT EXISTS personal (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vorname TEXT,
    nachname TEXT,
    dashboard_id INTEGER
)");

$db->exec("CREATE TABLE IF NOT EXISTS einsatz (
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

$db->exec("CREATE TABLE IF NOT EXISTS einsatz_personal (
    einsatz_id INTEGER,
    personal_id INTEGER
)");

$db->exec("CREATE TABLE IF NOT EXISTS einsatz_dokumentation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    einsatz_id INTEGER,
    zeilennummer INTEGER,
    zeitpunkt DATETIME,
    text TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS drohnen (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
)");

$db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_personal_einsatz_id ON einsatz_personal(einsatz_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_personal_personal_id ON einsatz_personal(personal_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_dokumentation_einsatz_id ON einsatz_dokumentation(einsatz_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_dokumentation_einsatz_zeile ON einsatz_dokumentation(einsatz_id, zeilennummer)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_einsatz_startzeit ON einsatz(startzeit)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_personal_dashboard_id ON personal(dashboard_id)");
