<?php
// Load bootstrap for compatibility
if (!defined('BOOTSTRAP_LOADED')) {
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
    define('BOOTSTRAP_LOADED', true);
}

if (!isset($config)) {
    $configPath = dirname(__DIR__, 2) . '/config/config.php';
    if (file_exists($configPath)) {
        $config = include $configPath;
    } else {
        $config = [];
    }
}

$currentVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$versionUpdate = checkForNewVersion($currentVersion, $config);
?>
<header class="page-header">
    <div class="header-content">
        <?php 
        // Simple path handling: if script is in updater directory, use ../ prefix
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = (strpos($scriptName, '/updater/') !== false || strpos($scriptName, '\\updater\\') !== false) ? '../' : '';
        
        // Logo URL via logo.php so it works with any document root (public/ or project root)
        $logoUrl = (!empty($config['logo_path']) && function_exists('getLogoUrl')) ? getLogoUrl() : '';
        // Logo link: absolute path to index so it works from any page
        $indexPath = $basePath ? $basePath . 'public/index.php' : '/public/index.php';
        $logoFullPath = dirname(__DIR__, 2) . '/' . ltrim($config['logo_path'] ?? '', '/');
        if (!empty($config['logo_path']) && file_exists($logoFullPath)): ?>
            <a href="<?= htmlspecialchars($indexPath) ?>" class="header-logo-link">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="header-logo">
            </a>
        <?php endif; ?>
        <h1>Einsatztagebuch <?php echo htmlspecialchars($config['navigation_title']) ?></h1>
        <a href="https://github.com/denni95112/drohnen-einsatztagebuch/wiki" target="_blank" rel="noopener noreferrer" class="header-wiki-btn" title="Anleitung (Wiki)">Wiki</a>
        <?php if ($versionUpdate): 
            // Determine if user is admin and set appropriate link
            $isAdminForLink = false;
            if (function_exists('isAdminAuthenticated')) {
                $isAdminForLink = isAdminAuthenticated();
            }
            
            if ($isAdminForLink) {
                // Admin: link to updater tool
                $notificationUrl = $basePath . 'updater/updater_page.php';
                $notificationTarget = '';
            } else {
                // Non-admin: link to GitHub
                $notificationUrl = $versionUpdate['url'];
                $notificationTarget = ' target="_blank"';
            }
        ?>
            <a href="<?= htmlspecialchars($notificationUrl) ?>"<?= $notificationTarget ?> class="version-notification" title="Neue Version <?= htmlspecialchars($versionUpdate['new_version']) ?> verfügbar!">
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
