<?php
/**
 * Index page view
 */
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

<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<div class="button-container">
    <?php if ($config['dashboard_url']): ?>
        <a href="<?= htmlspecialchars($config['dashboard_url']) ?>" class="btn-dashboard">Dashboard öffnen</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <?php if (!empty($dashboardApiManaged)): ?>
        <a href="/public/index.php?page=drohnen" class="btn">Drohnen anzeigen</a>
        <a href="/public/index.php?page=personal" class="btn">Personal anzeigen</a>
        <?php else: ?>
        <a href="/public/index.php?page=drohnen" class="btn">Drohnenverwaltung</a>
        <a href="/public/index.php?page=personal" class="btn">Personalverwaltung</a>
        <?php endif; ?>
    <?php endif; ?>
    <a href="/public/index.php?page=neuer_einsatz" class="btn">Neuen Einsatz starten</a>

    <?php if ($letzter_einsatz): ?>
        <a href="/public/index.php?page=dokumentation&einsatz_id=<?= htmlspecialchars($letzter_einsatz) ?>" class="btn">Letzten Einsatz öffnen</a>
    <?php endif; ?>

    <?php if ($letzter_einsatz): ?>
        <a href="/public/index.php?page=read_only&einsatz_id=<?= htmlspecialchars($letzter_einsatz) ?>&token=<?=$config['read_token']?>" class="btn">Letzten Einsatz öffnen (Lese-Modus)</a>
    <?php endif; ?>

    <?php if ($letzter_einsatz): ?>
        <?php
        $read_only_url = getBaseUrl() . "/public/index.php?page=read_only&einsatz_id=" . htmlspecialchars($letzter_einsatz) . "&token=" . $config['read_token'];
        ?>
        <div class="qr-container">
            <p><strong>QR-Code für den Lese-Modus:</strong></p>
            <img src="/api/v1/qr?data=<?= urlencode($read_only_url) ?>" alt="QR Code">
        </div>
        <br><br>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="/public/index.php?page=einsatzliste" class="btn">Alle Einsätze</a>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <a href="/public/index.php?page=admin" class="btn">Administration</a>
    <?php endif; ?>
    <a href="/api/v1/auth/logout" class="btn">Logout</a>
    <br><br>
</div>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

</body>
</html>
