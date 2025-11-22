<?php

if (!file_exists(__DIR__ . '/config/config.php')) {
    header('Location: setup.php');
    exit;
}

require_once 'utils.php';
require_once 'db.php';
require 'auth.php';
requireAuth();
$config = include __DIR__ . '/config/config.php';
$isAdmin = isAdminAuthenticated();
$stmt = $db->query("SELECT id FROM einsatz ORDER BY id DESC LIMIT 1");
$letzter_einsatz = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatztagebuch <?php echo $config['navigation_title'] ?></title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/index.css') ?>">
</head>
<body>

<?php include 'header.php'; ?>

<div class="button-container">
    <?php if ($config['dashboard_url']): ?>
        <a href="<?= htmlspecialchars($config['dashboard_url']) ?>" class="btn-dashboard">Dashboard öffnen</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="drohnen.php" class="btn">Drohnenverwaltung</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="personal.php" class="btn">Personalverwaltung</a>
    <?php endif; ?>
    <a href="neuer_einsatz.php" class="btn">Neuen Einsatz starten</a>

    <?php if ($letzter_einsatz): ?>
        <a href="dokumentation.php?einsatz_id=<?= htmlspecialchars($letzter_einsatz) ?>" class="btn">Letzten Einsatz öffnen</a>
    <?php endif; ?>

    <?php if ($letzter_einsatz): ?>
        <a href="read_only.php?einsatz_id=<?= htmlspecialchars($letzter_einsatz) ?>&token=<?=$config['read_token']?>" class="btn">Letzten Einsatz öffnen (Lese-Modus)</a>
    <?php endif; ?>

    <?php if ($letzter_einsatz): ?>
        <?php
        $read_only_url = $config['domain'] . "/read_only.php?einsatz_id=" . htmlspecialchars($letzter_einsatz) . "&token=" . $config['read_token'];
        ?>
        <div class="qr-container">
            <p><strong>QR-Code für den Lese-Modus:</strong></p>
            <img src="qr_generate.php?data=<?= urlencode($read_only_url) ?>" alt="QR Code">
        </div>
        <br><br>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="einsatzliste.php" class="btn">Alle Einsätze</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="admin.php" class="btn">Administration</a>
    <?php endif; ?>
    <a href="logout.php" class="btn">Logout</a>
    <br><br>
</div>

<?php include 'footer.php'; ?>

</body>
</html>