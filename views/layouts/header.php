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

// Initialize config option for existing installations
if (!isset($config['ask_for_install_notification'])) {
    if (function_exists('updateConfig')) {
        updateConfig('ask_for_install_notification', true);
        $config = include dirname(__DIR__, 2) . '/config/config.php';
    }
}

$currentVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$versionUpdate = checkForNewVersion($currentVersion, $config);

// Check if we should show installation notification dialog
$showNotificationDialog = false;
if (isset($config['ask_for_install_notification']) && $config['ask_for_install_notification'] === true) {
    // Check if auth is loaded (auth.php starts session)
    if (function_exists('isAdminAuthenticated')) {
        $isAdmin = isAdminAuthenticated();
        if ($isAdmin) {
            $showNotificationDialog = true;
        }
    }
}
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

<?php if ($showNotificationDialog): ?>
<!-- Installation Notification Dialog -->
<div id="install-notification-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Installationsbenachrichtigung</h2>
        <p>Möchten Sie den Entwickler über diese Installation informieren?</p>
        <p><strong>Hinweis:</strong> Es werden keine privaten Daten übertragen. Es wird nur eine Benachrichtigung mit dem aktuellen Datum und der Uhrzeit gesendet. Optional können Sie den Namen Ihrer Organisation teilen, wenn Sie dies wünschen.</p>
        <div id="install-notification-form" style="margin: 1.5rem 0;">
            <div style="margin-bottom: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="install-notification-share-org" style="cursor: pointer;">
                    <span>Ich möchte den Namen meiner Organisation teilen</span>
                </label>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="install-notification-organization" style="display: block; margin-bottom: 0.5rem;">Organisation (optional):</label>
                <input type="text" id="install-notification-organization" placeholder="Name Ihrer Organisation" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" disabled>
            </div>
        </div>
        <div id="install-notification-message-container"></div>
        <div class="modal-buttons">
            <button type="button" id="install-notification-yes" class="modal-button">Ja</button>
            <button type="button" id="install-notification-no" class="modal-button modal-button-no">Nein</button>
        </div>
    </div>
</div>
<meta name="csrf-token" content="<?php echo htmlspecialchars(getCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
<script>
    // Make config value available to JavaScript
    window.showInstallNotification = true;
</script>
            <script src="<?= $basePath ? $basePath . 'public/js/install_notification.js' : getVersionedAsset('js/install_notification.js') ?>"></script>
<?php endif; ?>
