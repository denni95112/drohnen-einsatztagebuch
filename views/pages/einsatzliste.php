<?php
/**
 * Einsatzliste page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;
use App\Models\Einsatz;

AuthService::requireAdminAuth();

$config = include dirname(__DIR__, 2) . '/config/config.php';

$einsatzModel = new Einsatz();
$einsaetze = $einsatzModel->getAllWithPersonal();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatzliste - Einsatztagebuch</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= getVersionedAsset('css/einsatzliste.css') ?>">
</head>
<body>

<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<h2>Einsatzliste</h2>

<table id="einsatzTabelle">
    <thead>
        <tr>
            <th onclick="sortTable(0)">Einsatznummer 🔽</th>
            <th onclick="sortTable(1)">Adresse 🔽</th>
            <th onclick="sortTable(2)">Startzeit 🔽</th>
            <th onclick="sortTable(3)">Endzeit 🔽</th>
            <th>PDF</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($einsaetze as $einsatz): ?>
        <tr>
            <td><?= htmlspecialchars($einsatz['einsatznummer']) ?></td>
            <td><?= htmlspecialchars($einsatz['adresse']) ?></td>
            <td><?= htmlspecialchars($einsatz['startzeit']) ?></td>
            <td><?= htmlspecialchars($einsatz['endzeit'] ?? '-') ?></td>
            <td><a href="/api/v1/einsatz/<?= $einsatz['id'] ?>/pdf" class="download-btn">PDF herunterladen</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

<script src="<?= getVersionedAsset('js/einsatzliste.js') ?>"></script>

</body>
</html>
