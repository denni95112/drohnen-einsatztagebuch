<?php
if (!isset($config)) {
    $config = include __DIR__ . '/config/config.php';
}

require_once __DIR__ . '/utils.php';
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
<script>
(function() {
    function adjustHeaderLogo() {
        const headerTitle = document.querySelector('.page-header h1');
        const headerLogo = document.querySelector('.header-logo');
        
        if (!headerTitle || !headerLogo) return;
        
        const titleHeight = headerTitle.scrollHeight;
        const lineHeight = parseFloat(getComputedStyle(headerTitle).lineHeight);
        const isTwoLines = titleHeight > lineHeight * 1.5;
        
        if (isTwoLines) {
            headerLogo.classList.add('scaled');
        } else {
            headerLogo.classList.remove('scaled');
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', adjustHeaderLogo);
    } else {
        adjustHeaderLogo();
    }
    
    window.addEventListener('resize', adjustHeaderLogo);
    
    const headerTitle = document.querySelector('.page-header h1');
    if (headerTitle) {
        const observer = new MutationObserver(adjustHeaderLogo);
        observer.observe(headerTitle, { 
            childList: true, 
            characterData: true, 
            subtree: true 
        });
    }
})();
</script>

