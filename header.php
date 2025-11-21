<?php
if (!isset($config)) {
    $config = include __DIR__ . '/config/config.php';
}

require_once __DIR__ . '/version_check.php';
$currentVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$versionUpdate = checkForNewVersion($currentVersion, $config);
?>
<header class="page-header">
    <div class="header-content">
        <?php if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
            <img src="<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo" class="header-logo">
        <?php endif; ?>
        <h1>Einsatztagebuch <?php echo htmlspecialchars($config['navigation_title']) ?></h1>
        <?php if ($versionUpdate): ?>
            <a href="<?= htmlspecialchars($versionUpdate['url']) ?>" target="_blank" class="version-notification" title="Neue Version <?= htmlspecialchars($versionUpdate['new_version']) ?> verfügbar!">
                <span class="notification-icon">🔔</span>
                <span class="notification-badge">Neu</span>
            </a>
        <?php endif; ?>
    </div>
</header>

