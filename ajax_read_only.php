<?php
require_once 'db.php';

if (!isset($_GET['einsatz_id'])) {
    http_response_code(400);
    exit(json_encode(["error" => "Keine Einsatz-ID angegeben."]));
}

$einsatz_id = (int)$_GET['einsatz_id'];

$stmt = $db->prepare("SELECT id, einsatz_id, zeilennummer, zeitpunkt, text FROM einsatz_dokumentation WHERE einsatz_id = ? ORDER BY zeilennummer DESC");
$stmt->execute([$einsatz_id]);
$eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/json");
echo json_encode($eintraege);
?>