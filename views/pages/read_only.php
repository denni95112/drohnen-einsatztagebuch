<?php
/**
 * Read-only page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Models\Einsatz;

$config = include dirname(__DIR__, 2) . '/config/config.php';

if (!isset($_GET['token']) || $_GET['token'] !== $config['read_token']) {
    die("Ungültiger Token! Zugriff verweigert.");
}

if (!isset($_GET['einsatz_id'])) {
    http_response_code(400);
    exit(json_encode(["error" => "Keine Einsatz-ID angegeben."]));
}

$einsatz_id = (int)$_GET['einsatz_id'];

$einsatzModel = new Einsatz();
$einsatz = $einsatzModel->find($einsatz_id);

if (!$einsatz) {
    die("Einsatz nicht gefunden.");
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Einsatz-Dokumentation Lesemodus</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
</head>
<body data-einsatz-id="<?= htmlspecialchars($einsatz_id) ?>">
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
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
<a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

<script src="/js/read_only.js"></script>

</body>
</html>
