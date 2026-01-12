<?php
$qrlibPath = __DIR__ . '/lib/phpqrcode/qrlib.php';

if (!file_exists($qrlibPath)) {
    http_response_code(500);
    die("Fehler: Die phpqrcode-Bibliothek wurde nicht gefunden. Bitte installieren Sie die Bibliothek über das Setup (setup.php) oder manuell in lib/phpqrcode/");
}

require_once $qrlibPath;

if (!isset($_GET['data'])) {
    die("Keine Daten übergeben.");
}

$url = urldecode($_GET['data']);
QRcode::png($url, false, QR_ECLEVEL_L, 10);
