<?php
require_once 'db.php';
$config = include __DIR__ . '/config/config.php';


if (!isset($_GET['token']) || $_GET['token'] !== $config['read_token']) {
    die("Ungültiger Token! Zugriff verweigert.");
}

if (!isset($_GET['einsatz_id'])) {
    http_response_code(400);
    exit(json_encode(["error" => "Keine Einsatz-ID angegeben."]));
}

$einsatz_id = (int)$_GET['einsatz_id'];

$stmt = $db->prepare("SELECT id, einsatznummer, adresse, gps_lat, gps_lng, einsatzart, gruppenfuehrer_id, dokumentierende_id, startzeit, endzeit FROM einsatz WHERE id = ?");
$stmt->execute([$einsatz_id]);
$einsatz = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Einsatz-Dokumentation Lesemodus</title>
    <link rel="stylesheet" href="css/styles.css">

</head>
<body data-einsatz-id="<?= htmlspecialchars($einsatz_id) ?>">
<h2>Einsatz-Dokumentation (Lese-Modus) (#<?= htmlspecialchars($einsatz['einsatznummer']) ?>)</h2>

<br><br>

<table id="eintraegeTabelle">
    <thead>
        <tr>
            <th>#</th>
            <th onclick="sortTable(1)">Zeitpunkt 🔽</th>
            <th>Text</th>
        </tr>
    </thead>
    <tbody>
        <!-- Einträge werden per JavaScript geladen -->
    </tbody>
</table>

<br>
<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

<script src="js/read_only.js"></script>

</body>
</html>
