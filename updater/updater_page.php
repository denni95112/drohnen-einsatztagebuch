<?php
// Get base directory and normalize path
$baseDir = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $baseDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'error_reporting.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'security_headers.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'auth.php';
requireAdminAuth();

$config = include $baseDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
if (isset($config['timezone'])) {
    date_default_timezone_set($config['timezone']);
}

require_once $baseDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'utils.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'version.php';
require_once $baseDir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'csrf.php';

// Calculate base path for assets
$basePath = '../';

// Load updater class
require_once __DIR__ . '/updater.php';

$projectRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$updater = new Updater($projectRoot);

// Get current version info
$currentVersion = APP_VERSION;
$updateInfo = null;
$error = null;

// Check requirements
$requirements = $updater->checkRequirements();
$requirementsError = null;
if (!$requirements['available']) {
    $requirementsError = $updater->getRequirementErrorMessage($requirements['missing']);
}

// Try to check for updates on page load (non-blocking)
try {
    $updateInfo = $updater->checkForUpdates();
} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Tool - Admin</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/styles.css?v=<?php echo APP_VERSION; ?>">
    <link rel="stylesheet" href="updater.css?v=<?php echo APP_VERSION; ?>">
    <link rel="manifest" href="<?php echo $basePath; ?>manifest.json">
</head>
<body>
    <?php include $baseDir . DIRECTORY_SEPARATOR . 'header.php'; ?>
    <main>
        <h1>Update Tool</h1>
        
        <!-- Error message container -->
        <div id="error-message-container" class="error-message" style="display: none;"></div>
        
        <!-- Success message container -->
        <div id="success-message-container" class="success-message" style="display: none;"></div>
        
        <?php if ($requirementsError): ?>
        <!-- Requirements Warning -->
        <div class="error-message" style="display: block; margin-bottom: 2rem;">
            <strong>⚠️ Systemanforderungen:</strong><br>
            <p style="margin: 0.5rem 0;">Erforderliche PHP-Extension fehlt. Bitte installieren Sie die fehlende Extension, bevor Sie Updates durchführen können.</p>
            <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 8px; white-space: pre-wrap; font-family: monospace; font-size: 0.9rem; max-height: 400px; overflow-y: auto;">
                <?php echo nl2br(htmlspecialchars($requirementsError, ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="updater-container">
            <!-- Current Version Section -->
            <div class="version-section">
                <h2>Aktuelle Version</h2>
                <div class="version-badge current-version">
                    v<?php echo htmlspecialchars($currentVersion, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
            
            <!-- Update Check Section -->
            <div class="update-check-section">
                <h2>Update prüfen</h2>
                <button id="check-updates-btn" class="btn-primary">
                    <span class="btn-text">Auf Updates prüfen</span>
                    <span class="btn-spinner" style="display: none;">⏳</span>
                </button>
            </div>
            
            <!-- Available Update Section -->
            <div id="update-available-section" class="update-available-section" style="display: none;">
                <h2>Update verfügbar</h2>
                <div class="update-info">
                    <div class="version-comparison">
                        <span class="version-badge current-version">v<?php echo htmlspecialchars($currentVersion, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="version-arrow">→</span>
                        <span class="version-badge new-version" id="new-version-badge">v?.?.?</span>
                    </div>
                    <div id="release-notes" class="release-notes" style="display: none;"></div>
                    <a id="release-url" href="#" target="_blank" rel="noopener noreferrer" class="release-link" style="display: none;">
                        Release auf GitHub ansehen
                    </a>
                </div>
                <div class="update-actions">
                    <button id="update-now-btn" class="btn-update">
                        <span class="btn-text">Jetzt aktualisieren</span>
                        <span class="btn-spinner" style="display: none;">⏳</span>
                    </button>
                </div>
            </div>
            
            <!-- No Update Section -->
            <div id="no-update-section" class="no-update-section" style="display: none;">
                <h2>Kein Update verfügbar</h2>
                <p>Sie verwenden bereits die neueste Version.</p>
            </div>
            
            <!-- Update Progress Section -->
            <div id="update-progress-section" class="update-progress-section" style="display: none;">
                <h2>Update wird durchgeführt</h2>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div id="progress-bar-fill" class="progress-bar-fill" style="width: 0%;"></div>
                    </div>
                    <div id="progress-text" class="progress-text">Vorbereitung...</div>
                </div>
                <div id="update-status" class="update-status"></div>
            </div>
            
            <!-- Update Complete Section -->
            <div id="update-complete-section" class="update-complete-section" style="display: none;">
                <h2>Update abgeschlossen</h2>
                <div id="update-results" class="update-results"></div>
                <div class="update-actions">
                    <button id="reload-page-btn" class="btn-primary">Seite neu laden</button>
                </div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <h3>Hinweise</h3>
            <ul>
                <li>Vor dem Update wird automatisch ein Backup erstellt</li>
                <li>Konfigurationsdateien, Uploads und Datenbanken werden geschützt</li>
                <li>Bei einem Fehler wird automatisch ein Rollback durchgeführt</li>
                <li>Das Update kann einige Minuten dauern</li>
            </ul>
        </div>
        
        <!-- Back Button -->
        <div style="margin-top: 2rem; text-align: center;">
            <a href="<?php echo $basePath; ?>admin.php" class="btn-primary" style="display: inline-block; text-decoration: none; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                ← Zurück zur Administration
            </a>
        </div>
    </main>
    
    <?php include $baseDir . DIRECTORY_SEPARATOR . 'footer.php'; ?>
    
    <?php
    // Calculate base path for assets
    $basePath = '../';
    ?>
    <script>
        // Make config available to JavaScript
        window.updaterConfig = {
            currentVersion: <?php echo json_encode($currentVersion); ?>,
            updateInfo: <?php echo json_encode($updateInfo); ?>,
            csrfToken: <?php echo json_encode(getCSRFToken()); ?>,
            basePath: <?php echo json_encode($basePath); ?>
        };
    </script>
    <script src="updater.js"></script>
</body>
</html>
