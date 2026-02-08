<?php
/**
 * QR Code generation endpoint
 */
require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Services\QRCodeService;

if (!isset($_GET['data'])) {
    http_response_code(400);
    die("Keine Daten übergeben.");
}

$url = urldecode($_GET['data']);
QRCodeService::generate($url);
