<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['einsatz_id']) || empty($_POST['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Anfrage']);
    exit;
}

$einsatz_id = (int)$_GET['einsatz_id'];
$text = trim($_POST['text'] ?? '');

// Input validation and sanitization
if (empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Text darf nicht leer sein']);
    exit;
}

// Sanitize text (prevent XSS in stored data)
$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

// Nächste Zeilennummer ermitteln
$stmt = $db->prepare("SELECT MAX(zeilennummer) FROM einsatz_dokumentation WHERE einsatz_id = ?");
$stmt->execute([$einsatz_id]);
$zeilennummer = (int)$stmt->fetchColumn() + 1;

// Eintrag speichern - calculate timestamp once to avoid redundant query
$zeitpunkt = date('Y-m-d H:i:s');
$stmt = $db->prepare("INSERT INTO einsatz_dokumentation (einsatz_id, zeilennummer, zeitpunkt, text) VALUES (?, ?, ?, ?)");
$stmt->execute([$einsatz_id, $zeilennummer, $zeitpunkt, $text]);

echo json_encode([
    'zeilennummer' => $zeilennummer,
    'zeitpunkt' => $zeitpunkt,
    'text' => $text
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
