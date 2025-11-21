<?php
if (!isset($config)) {
    $config = include __DIR__ . '/config/config.php';
}
?>
<header class="page-header">
    <div class="header-content">
        <?php if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
            <img src="<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo" class="header-logo">
        <?php endif; ?>
        <h1>Einsatztagebuch <?php echo htmlspecialchars($config['navigation_title']) ?></h1>
    </div>
</header>

