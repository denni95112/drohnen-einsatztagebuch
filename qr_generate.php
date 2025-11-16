<?php
require_once 'lib/phpqrcode/qrlib.php';

if (!isset($_GET['data'])) {
    die("Keine Daten übergeben.");
}

$url = urldecode($_GET['data']);
QRcode::png($url, false, QR_ECLEVEL_L, 10);
