<?php
require_once 'db.php';
require 'auth.php';
requireAdminAuth();
$config = include __DIR__ . '/config/config.php';

// Alle Einsätze abrufen
$stmt = $db->query("SELECT id, einsatznummer, adresse, startzeit, endzeit FROM einsatz ORDER BY id DESC");
$einsaetze = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatzliste - Einsatztagebuch</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/einsatzliste.css">
</head>
<body>

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
            <td><a href="einsatz_abschluss.php?einsatz_id=<?= $einsatz['id'] ?>" class="download-btn">PDF herunterladen</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

<script src="js/einsatzliste.js"></script>

</body>
</html>
