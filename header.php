<?php
if (!isset($config)) {
    $config = include __DIR__ . '/config/config.php';
}

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/version_check.php';

// Initialize config option for existing installations
if (!isset($config['ask_for_install_notification'])) {
    updateConfig('ask_for_install_notification', true);
    $config = include __DIR__ . '/config/config.php';
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
        // Determine base path for logo (works from root and subdirectories)
        // Get the directory of the current script relative to document root
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $logoBasePath = '';
        
        // If script is in a subdirectory (not root), add ../ for each level
        if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.') {
            // Normalize path separators
            $scriptDir = str_replace('\\', '/', $scriptDir);
            $scriptDir = trim($scriptDir, '/');
            if (!empty($scriptDir)) {
                // Count directory depth
                $depth = substr_count($scriptDir, '/') + 1;
                $logoBasePath = str_repeat('../', $depth);
            }
        }
        
        $logoPath = $logoBasePath . ($config['logo_path'] ?? '');
        if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" class="header-logo">
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
<script src="js/install_notification.js"></script>
<?php endif; ?>
