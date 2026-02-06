<?php
/**
 * Admin page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;
use App\Utils\Database;

AuthService::requireAdminAuth();

$config = include dirname(__DIR__, 2) . '/config/config.php';
$db = Database::getInstance()->getConnection();

// AJAX: Test Dashboard API connection (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'test_dashboard_api') {
    header('Content-Type: application/json; charset=utf-8');
    $url = trim($_POST['dashboard_api_url'] ?? '');
    $token = trim($_POST['dashboard_api_token'] ?? '');
    if ($url === '') {
        echo json_encode(['success' => false, 'error' => 'Bitte API-URL eingeben.']);
        exit;
    }
    if ($token === '') {
        $token = trim($config['dashboard_api_token'] ?? '');
    }
    if ($token === '') {
        echo json_encode(['success' => false, 'error' => 'Bitte API-Token eingeben oder zuerst speichern.']);
        exit;
    }
    $baseUrl = rtrim(preg_replace('#/+$#', '', $url), '/');
    $testUrl = $baseUrl . '/api/pilots.php?action=list';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($response === false) {
        echo json_encode(['success' => false, 'error' => 'Verbindung fehlgeschlagen: ' . $curlError]);
        exit;
    }
    $data = json_decode($response, true);
    if ($httpCode === 200 && isset($data['success']) && $data['success'] === true) {
        echo json_encode(['success' => true, 'message' => 'Verbindung erfolgreich.']);
        exit;
    }
    $errMsg = isset($data['error']) ? $data['error'] : ('HTTP ' . $httpCode);
    echo json_encode(['success' => false, 'error' => $errMsg]);
    exit;
}

// Library installation functions (reused from setup.php logic)
if (!function_exists('getAllLibraries')) {
    function getAllLibraries() {
        return [
            'dompdf' => [
                'path' => dirname(__DIR__, 2) . '/lib/dompdf/autoload.inc.php',
                'name' => 'dompdf',
                'url' => 'https://github.com/dompdf/dompdf/releases/download/v3.1.4/dompdf-3.1.4.zip',
                'github_api' => 'null'
            ],
            'phpqrcode' => [
                'path' => dirname(__DIR__, 2) . '/lib/phpqrcode/qrlib.php',
                'name' => 'phpqrcode',
                'url' => 'https://github.com/t0k4rt/phpqrcode/archive/refs/heads/master.zip',
                'github_api' => null
            ]
        ];
    }
}

if (!function_exists('rmdir_recursive')) {
    function rmdir_recursive($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? rmdir_recursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}

if (!function_exists('findLibraryPath')) {
    function findLibraryPath($dir, $libName) {
        if (!is_dir($dir)) return null;
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $dir . '/' . $file;
            if ($libName === 'dompdf') {
                if (file_exists($fullPath . '/autoload.inc.php')) {
                    return $fullPath;
                }
                if (file_exists($fullPath . '/vendor/autoload.php')) {
                    return $fullPath;
                }
                if (is_dir($fullPath)) {
                    $found = findLibraryPath($fullPath, $libName);
                    if ($found) return $found;
                }
            } elseif ($libName === 'phpqrcode') {
                if (file_exists($fullPath . '/qrlib.php')) {
                    return $fullPath;
                }
                if (is_dir($fullPath)) {
                    $found = findLibraryPath($fullPath, $libName);
                    if ($found) return $found;
                }
            }
        }
        return null;
    }
}

if (!function_exists('downloadLibrary')) {
    function downloadLibrary($libInfo, $libDir) {
        if (!is_dir($libDir)) {
            if (!mkdir($libDir, 0755, true)) {
                return ['success' => false, 'error' => "Konnte Verzeichnis $libDir nicht erstellen"];
            }
        }
        
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL ist nicht verfügbar. Bitte installiere die PHP cURL Extension.'];
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), 'lib_download_');
        $fp = fopen($tempFile, 'w');
        
        if (!$fp) {
            return ['success' => false, 'error' => "Konnte temporäre Datei nicht erstellen"];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $libInfo['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Library Downloader');
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        
        $caBundlePaths = [
            __DIR__ . '/cacert.pem',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        
        $caBundleFound = false;
        foreach ($caBundlePaths as $caPath) {
            if ($caPath && file_exists($caPath)) {
                curl_setopt($ch, CURLOPT_CAINFO, $caPath);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                $caBundleFound = true;
                break;
            }
        }
        
        if (!$caBundleFound) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        
        if (!$success || $httpCode !== 200) {
            @unlink($tempFile);
            return ['success' => false, 'error' => "Download fehlgeschlagen: " . ($error ?: "HTTP $httpCode")];
        }
        
        if (!file_exists($tempFile) || filesize($tempFile) === 0) {
            @unlink($tempFile);
            return ['success' => false, 'error' => "Download-Datei ist leer oder fehlt"];
        }
        
        $extractPath = $libDir . '/' . $libInfo['name'] . '_temp';
        if (is_dir($extractPath)) {
            rmdir_recursive($extractPath);
        }
        mkdir($extractPath, 0755, true);
        
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($tempFile) !== TRUE) {
                @unlink($tempFile);
                rmdir_recursive($extractPath);
                return ['success' => false, 'error' => 'Konnte ZIP-Datei nicht öffnen'];
            }
            $zip->extractTo($extractPath);
            $zip->close();
            @unlink($tempFile);
        } else {
            $unzipCommand = 'unzip';
            $unzipPath = null;
            $whichUnzip = shell_exec('which unzip 2>/dev/null');
            if ($whichUnzip) {
                $unzipPath = trim($whichUnzip);
            } elseif (shell_exec('command -v unzip 2>/dev/null')) {
                $unzipPath = 'unzip';
            }
            
            if ($unzipPath) {
                $tempFileEscaped = escapeshellarg($tempFile);
                $extractPathEscaped = escapeshellarg($extractPath);
                $command = "$unzipPath -q $tempFileEscaped -d $extractPathEscaped 2>&1";
                $output = [];
                $returnVar = 0;
                exec($command, $output, $returnVar);
                
                @unlink($tempFile);
                
                if ($returnVar !== 0) {
                    rmdir_recursive($extractPath);
                    return ['success' => false, 'error' => 'ZIP-Extraktion fehlgeschlagen: ' . implode("\n", $output)];
                }
            } else {
                @unlink($tempFile);
                rmdir_recursive($extractPath);
                return ['success' => false, 'error' => 'ZipArchive ist nicht verfügbar und unzip-Befehl wurde nicht gefunden.'];
            }
        }
        
        $files = scandir($extractPath);
        $actualLibPath = null;
        
        if ($libInfo['name'] === 'dompdf' && (file_exists($extractPath . '/autoload.inc.php') || file_exists($extractPath . '/vendor/autoload.php'))) {
            $actualLibPath = $extractPath;
        } elseif ($libInfo['name'] === 'phpqrcode' && file_exists($extractPath . '/qrlib.php')) {
            $actualLibPath = $extractPath;
        } else {
            $actualLibPath = findLibraryPath($extractPath, $libInfo['name']);
            
            if (!$actualLibPath) {
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $fullPath = $extractPath . '/' . $file;
                    if (is_dir($fullPath) && (strpos($file, $libInfo['name']) !== false || strpos($file, 'dompdf') !== false || strpos($file, 'phpqrcode') !== false)) {
                        $actualLibPath = findLibraryPath($fullPath, $libInfo['name']);
                        if ($actualLibPath) break;
                    }
                }
            }
        }
        
        if (!$actualLibPath) {
            rmdir_recursive($extractPath);
            return ['success' => false, 'error' => 'Bibliotheksverzeichnis in ZIP nicht gefunden'];
        }
        
        $finalPath = $libDir . '/' . $libInfo['name'];
        if (is_dir($finalPath)) {
            rmdir_recursive($finalPath);
        }
        
        if (!rename($actualLibPath, $finalPath)) {
            rmdir_recursive($extractPath);
            return ['success' => false, 'error' => 'Konnte Bibliothek nicht nach ' . $finalPath . ' verschieben'];
        }
        
        if (is_dir($extractPath)) {
            rmdir_recursive($extractPath);
        }
        
        return ['success' => true];
    }
}

// Handle library reinstallation (AJAX)
$isAjaxRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && 
                 (!empty($_POST['ajax']) || 
                  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')));
$isReinstallLibs = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reinstall_libs']));

if ($isAjaxRequest && $isReinstallLibs) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8', true);
        header('Cache-Control: no-cache, must-revalidate', true);
        header('X-Content-Type-Options: nosniff', true);
    }
    ob_start();
    
    try {
        $allLibs = getAllLibraries();
        $libDir = dirname(__DIR__, 2) . '/lib';
        $results = [];
        
        foreach ($allLibs as $key => $lib) {
            if (isset($_POST['lib_' . $key]) && $_POST['lib_' . $key] === '1') {
                $result = downloadLibrary($lib, $libDir);
                $results[$key] = $result;
            }
        }
        
        $output = ob_get_clean();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        
        $response = [
            'results' => $results,
            'all_libs' => getAllLibraries()
        ];
        
        if (!empty($output) && trim($output) !== '') {
            $response['debug_output'] = substr($output, 0, 500);
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        $output = ob_get_clean();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        
        echo json_encode([
            'results' => [],
            'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Handle library reinstallation (non-AJAX)
if ($isReinstallLibs && !$isAjaxRequest) {
    $allLibs = getAllLibraries();
    $libDir = __DIR__ . '/lib';
    $success = true;
    
    foreach ($allLibs as $key => $lib) {
        if (isset($_POST['lib_' . $key]) && $_POST['lib_' . $key] === '1') {
            $result = downloadLibrary($lib, $libDir);
            if (!$result['success']) {
                $success = false;
                break;
            }
        }
    }
    
    if ($success) {
        header("Location: /public/index.php?page=admin&success=libs_reinstalled");
    } else {
        header("Location: /public/index.php?page=admin&error=libs_reinstall_failed");
    }
    exit;
}

$configPath = dirname(__DIR__, 2) . '/config/config.php';
$databasePath = isset($config['database_path']) ? $config['database_path'] : 'einsatzbuch.db';
if (is_absolute_path($databasePath)) {
    $dbFile = $databasePath;
} else {
    $dbFile = dirname(__DIR__, 2) . '/' . $databasePath;
}

if (isset($_POST['delete_all_einsaetze'])) {
    $db->beginTransaction();
    try {
        $db->exec("DELETE FROM einsatz_dokumentation");
        $db->exec("DELETE FROM einsatz_personal");
        $db->exec("DELETE FROM einsatz");
        $db->commit();
        header("Location: /public/index.php?page=admin&success=einsaetze_geloescht");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        die("Fehler beim Löschen: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download_db'])) {
    if (file_exists($dbFile)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="einsatztagebuch_backup.db"');
        readfile($dbFile);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['db_upload'])) {
    if ($_FILES['db_upload']['error'] == 0) {
        move_uploaded_file($_FILES['db_upload']['tmp_name'], $dbFile);
        header("Location: /public/index.php?page=admin&success=db_hochgeladen");
        exit;
    }
}

if (isset($_POST['delete_db']) && file_exists($dbFile)) {
    unlink($dbFile);
    header("Location: /public/index.php?page=admin&success=db_geloescht");
    exit;
}

if (isset($_POST['create_db']) && !file_exists($dbFile)) {
    require_once 'db.php';
        header("Location: /public/index.php?page=admin&success=db_neu_erstellt");
    exit;
}

if (isset($_POST['update_unit'])) {
    $config['navigation_title'] = trim($_POST['einheit']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: /public/index.php?page=admin&success=einheit_geaendert");
            exit;
        } else {
            @unlink($tempPath);
            die("Fehler beim Speichern der Konfiguration");
        }
    } else {
        die("Fehler beim Schreiben der Konfiguration");
    }
}

if (isset($_POST['update_password'])) {
    $config['admin_password_hash'] = hash('sha256', $_POST['admin_passwort']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: /public/index.php?page=admin&success=passwort_geaendert");
            exit;
        } else {
            @unlink($tempPath);
            die("Fehler beim Speichern der Konfiguration");
        }
    } else {
        die("Fehler beim Schreiben der Konfiguration");
    }
}

if (isset($_POST['save_dashboard_api'])) {
    require_once dirname(__DIR__, 2) . '/utils.php';
    $apiUrl = trim($_POST['dashboard_api_url'] ?? '');
    $apiToken = trim($_POST['dashboard_api_token'] ?? '');
    $configPath = dirname(__DIR__, 2) . '/config/config.php';
    $configToWrite = file_exists($configPath) ? (array) include $configPath : [];
    $configToWrite['dashboard_api_url'] = $apiUrl;
    if ($apiToken !== '') {
        $configToWrite['dashboard_api_token'] = $apiToken;
    }
    if (writeConfig($configToWrite)) {
        header("Location: /public/index.php?page=admin&success=dashboard_api_gespeichert");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
    $fileType = $_FILES['logo']['type'];
    
    if (in_array($fileType, $allowedTypes)) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $logoFullPath = dirname(__DIR__, 2) . '/' . $config['logo_path'];
        if (!empty($config['logo_path']) && file_exists($logoFullPath)) {
            @unlink($logoFullPath);
        }
        
        $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
            $config['logo_path'] = 'uploads/' . $fileName;
            $tempPath = $configPath . '.tmp';
            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            if (file_put_contents($tempPath, $configContent) !== false) {
                if (rename($tempPath, $configPath)) {
                    header("Location: /public/index.php?page=admin&success=logo_geaendert");
                    exit;
                } else {
                    @unlink($tempPath);
                    die("Fehler beim Speichern der Konfiguration");
                }
            } else {
                die("Fehler beim Schreiben der Konfiguration");
            }
        }
    }
}

if (isset($_POST['delete_logo']) && !empty($config['logo_path'])) {
    $logoFullPath = dirname(__DIR__, 2) . '/' . $config['logo_path'];
    if (file_exists($logoFullPath)) {
        @unlink($logoFullPath);
    }
    unset($config['logo_path']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: /public/index.php?page=admin&success=logo_geloescht");
            exit;
        } else {
            @unlink($tempPath);
            die("Fehler beim Speichern der Konfiguration");
        }
    } else {
        die("Fehler beim Schreiben der Konfiguration");
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verwaltung</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
</head>
<body>

<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<h2>Admin Verwaltung</h2>

<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php 
        $errorMessages = [
            "libs_reinstall_failed" => "Fehler beim Neuinstallieren der Bibliotheken!"
        ];
        echo $errorMessages[$_GET['error']] ?? "Ein Fehler ist aufgetreten!";
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="success-message">
        <?php 
        $messages = [
            "einsaetze_geloescht" => "Alle Einsätze wurden gelöscht!",
            "db_hochgeladen" => "Datenbank erfolgreich hochgeladen!",
            "db_geloescht" => "Datenbankdatei gelöscht!",
            "db_neu_erstellt" => "Neue Datenbank angelegt!",
            "einheit_geaendert" => "Einheit erfolgreich geändert!",
            "passwort_geaendert" => "Passwort wurde geändert!",
            "logo_geaendert" => "Logo erfolgreich hochgeladen!",
            "logo_geloescht" => "Logo erfolgreich gelöscht!",
            "libs_reinstalled" => "Bibliotheken erfolgreich neu installiert!",
            "dashboard_api_gespeichert" => "Dashboard-API-Einstellungen gespeichert. API-Token ist aktiv – Personal und Drohnen werden vom Flug-Dienstbuch geladen."
        ];
        echo $messages[$_GET['success']] ?? "Aktion erfolgreich!";
        ?>
    </div>
<?php endif; ?>

<div class="admin-sections">
    <!-- Datenbank Verwaltung -->
    <div class="admin-section">
        <h3>📊 Datenbank Verwaltung</h3>
        <div class="admin-actions">
            <form method="get" action="/public/index.php" class="admin-action-item">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="download_db" value="1">
                <button type="submit" class="btn-action">
                    <span class="action-icon">⬇️</span>
                    <span>Datenbank herunterladen</span>
                </button>
            </form>
            
            <form method="post" action="/public/index.php?page=admin" enctype="multipart/form-data" class="admin-action-item">
                <label class="file-input-label">
                    <input type="file" name="db_upload" required class="file-input">
                    <span class="file-input-button">📤 Datei auswählen</span>
                    <span class="file-input-text">Keine Datei ausgewählt</span>
                </label>
                <button type="submit" class="btn-action">Datenbank hochladen</button>
            </form>
            
            <?php if (!file_exists($dbFile)): ?>
            <form method="post" class="admin-action-item">
                <button type="submit" name="create_db" class="btn-action">
                    <span class="action-icon">➕</span>
                    <span>Datenbank neu anlegen</span>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Einsätze Verwaltung -->
    <div class="admin-section admin-section-danger">
        <h3>⚠️ Einsätze Verwaltung</h3>
        <form method="post" onsubmit="return confirm('Wirklich alle Einsätze löschen? Diese Aktion kann nicht rückgängig gemacht werden!')">
            <button type="submit" name="delete_all_einsaetze" class="danger btn-full">
                🗑️ Alle Einsätze löschen
            </button>
        </form>
    </div>

    <!-- Einstellungen -->
    <div class="admin-section">
        <h3>⚙️ Einstellungen</h3>
        
        <form method="post" class="admin-form-item">
            <label>Name der Einheit:</label>
            <input type="text" name="einheit" value="<?= htmlspecialchars($config['navigation_title']) ?>" required>
            <button type="submit" name="update_unit" class="btn-action-inline">💾 Speichern</button>
        </form>

        <form method="post" class="admin-form-item">
            <label>Admin Passwort ändern:</label>
            <input type="password" name="admin_passwort" placeholder="Neues Passwort eingeben" required>
            <button type="submit" name="update_password" class="btn-action-inline">🔒 Passwort ändern</button>
        </form>
    </div>

    <!-- Dashboard-Integration (Flug-Dienstbuch API) -->
    <div class="admin-section">
        <h3>🔗 Dashboard-Integration (Flug-Dienstbuch)</h3>
        <p>Optional: Verbindung zum <strong>Drohnen-Flug-und-Dienstbuch</strong> per API (Token). Wenn konfiguriert, werden Piloten, Drohnen und Flugstandorte von dort geladen und Flüge per API übertragen. Ansonsten kann weiterhin der Datenbank-Pfad (Setup) genutzt werden.</p>
        <?php
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $configDashboard = file_exists($configPath) ? (array) include $configPath : [];
        $dashboardApiUrl = isset($configDashboard['dashboard_api_url']) ? (string) $configDashboard['dashboard_api_url'] : '';
        $dashboardApiTokenSet = !empty($configDashboard['dashboard_api_token'] ?? '');
        $dashboardApiConnected = $dashboardApiUrl !== '' && $dashboardApiTokenSet;
        ?>
        <form method="post" id="dashboard-api-form" class="admin-form-item">
            <label for="dashboard_api_url">API-Basis-URL (Flug-Dienstbuch)</label>
            <input type="url" id="dashboard_api_url" name="dashboard_api_url" value="<?= htmlspecialchars($dashboardApiUrl) ?>" placeholder="z. B. https://mein-server.de/flug-dienstbuch" style="max-width: 400px; width: 100%; padding: 0.5rem;">
            <label for="dashboard_api_token" style="margin-top: 0.75rem;">API-Token</label>
            <div class="dashboard-token-row">
                <input type="password" id="dashboard_api_token" name="dashboard_api_token" placeholder="<?= $dashboardApiTokenSet ? '•••••••• (leer lassen = unverändert)' : 'Token aus dem Flug-Dienstbuch (Admin → API-Tokens)' ?>" autocomplete="off">
                <button type="button" id="dashboard-api-token-toggle" class="btn-action-inline" title="Anzeigen/Verbergen">👁</button>
            </div>
            <?php if ($dashboardApiTokenSet): ?>
            <p class="dashboard-token-set-hint">✓ API-Token ist gespeichert. Personal, Drohnen und Flugstandorte werden vom Flug-Dienstbuch geladen.</p>
            <?php endif; ?>
            <div class="admin-actions" style="margin-top: 0.75rem;">
                <button type="submit" name="save_dashboard_api" class="btn-action">💾 Einstellungen speichern</button>
                <button type="button" id="dashboard-api-test-btn" class="btn-action">🔌 Verbindung testen</button>
            </div>
            <div id="dashboard-api-status" class="dashboard-api-status" style="display: none;"></div>
        </form>
    </div>

    <!-- Library Management -->
    <div class="admin-section">
        <h3>📚 Bibliotheken Verwaltung</h3>
        <p>Benötigte Bibliotheken neu installieren (dompdf, phpqrcode)</p>
        <?php 
        $allLibraries = getAllLibraries();
        $libStatus = [];
        foreach ($allLibraries as $key => $lib) {
            $libStatus[$key] = file_exists($lib['path']);
        }
        ?>
        <form method="post" id="libReinstallForm">
            <?php foreach ($allLibraries as $key => $lib): ?>
            <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0.5rem 0;">
                <input type="checkbox" name="lib_<?= $key ?>" value="1" checked>
                <strong><?= htmlspecialchars($lib['name']) ?></strong>
                <span style="color: <?= $libStatus[$key] ? '#28a745' : '#dc3545' ?>">
                    (<?= $libStatus[$key] ? '✓ Installiert' : '✗ Fehlt' ?>)
                </span>
            </label>
            <?php endforeach; ?>
            <button type="submit" name="reinstall_libs" id="reinstallLibsBtn" class="btn-action" style="margin-top: 1rem;">
                <span class="action-icon">📥</span>
                <span>Ausgewählte Bibliotheken neu installieren</span>
            </button>
            <div id="reinstallStatus" style="margin-top: 1rem;"></div>
        </form>
    </div>

    <!-- Update Tool -->
    <div class="admin-section">
        <h3>🔄 Update Tool</h3>
        <p>Prüfen Sie auf neue Versionen und aktualisieren Sie die Anwendung direkt über die Website.</p>
        <div class="admin-actions">
            <a href="../updater/updater_page.php" class="btn-action" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <span class="action-icon">🔄</span>
                <span>Update Tool öffnen</span>
            </a>
        </div>
    </div>

    <!-- Logo Verwaltung -->
    <div class="admin-section">
        <h3>🖼️ Logo Verwaltung</h3>
        
        <?php 
        $logoFullPathForDisplay = dirname(__DIR__, 2) . '/' . ($config['logo_path'] ?? '');
        $logoUrlForDisplay = function_exists('getLogoUrl') ? getLogoUrl() : '';
        if (!empty($config['logo_path']) && file_exists($logoFullPathForDisplay)): ?>
        <div class="current-logo">
            <p><strong>Aktuelles Logo:</strong></p>
            <div class="logo-preview">
                <img src="<?= htmlspecialchars($logoUrlForDisplay) ?>" alt="Logo">
            </div>
        </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="admin-form-item">
            <label class="file-input-label">
                <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp" class="file-input">
                <span class="file-input-button">📁 Logo auswählen</span>
                <span class="file-input-text">Keine Datei ausgewählt</span>
            </label>
            <div class="logo-actions">
                <button type="submit" class="btn-action">📤 Logo hochladen</button>
                <?php if (!empty($config['logo_path']) && file_exists($logoFullPath)): ?>
                    <button type="submit" name="delete_logo" class="danger btn-action" onclick="return confirm('Logo wirklich löschen?')">🗑️ Logo löschen</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('.file-input');
    
    fileInputs.forEach(input => {
        const textSpan = input.nextElementSibling.nextElementSibling;
        
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                textSpan.textContent = this.files[0].name;
                textSpan.style.color = '#10b981';
                textSpan.style.fontStyle = 'normal';
            } else {
                textSpan.textContent = 'Keine Datei ausgewählt';
                textSpan.style.color = '#64748b';
                textSpan.style.fontStyle = 'italic';
            }
        });
    });
    
    // Dashboard API: token visibility toggle
    const tokenInput = document.getElementById('dashboard_api_token');
    const tokenToggle = document.getElementById('dashboard-api-token-toggle');
    if (tokenToggle && tokenInput) {
        tokenToggle.addEventListener('click', function() {
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                tokenToggle.textContent = '🙈';
            } else {
                tokenInput.type = 'password';
                tokenToggle.textContent = '👁';
            }
        });
    }

    // Dashboard API: test connection
    const dashboardTestBtn = document.getElementById('dashboard-api-test-btn');
    const dashboardApiForm = document.getElementById('dashboard-api-form');
    const dashboardApiStatus = document.getElementById('dashboard-api-status');
    if (dashboardTestBtn && dashboardApiForm && dashboardApiStatus) {
        dashboardTestBtn.addEventListener('click', function() {
            var url = document.getElementById('dashboard_api_url').value.trim();
            var token = document.getElementById('dashboard_api_token').value.trim();
            dashboardApiStatus.style.display = 'block';
            dashboardApiStatus.className = 'dashboard-api-status dashboard-api-status-pending';
            dashboardApiStatus.textContent = 'Prüfe Verbindung…';
            var formData = new FormData();
            formData.append('ajax', 'test_dashboard_api');
            formData.append('dashboard_api_url', url);
            formData.append('dashboard_api_token', token);
            fetch(dashboardApiForm.action || '/public/index.php?page=admin', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                dashboardApiStatus.className = 'dashboard-api-status ' + (data.success ? 'dashboard-api-status-ok' : 'dashboard-api-status-error');
                dashboardApiStatus.textContent = data.success ? (data.message || 'Verbindung erfolgreich.') : (data.error || 'Fehler');
            })
            .catch(function() {
                dashboardApiStatus.className = 'dashboard-api-status dashboard-api-status-error';
                dashboardApiStatus.textContent = 'Netzwerkfehler.';
            });
        });
    }

    // Library reinstallation form handler
    const libReinstallForm = document.getElementById('libReinstallForm');
    if (libReinstallForm) {
        libReinstallForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('reinstallLibsBtn');
            const status = document.getElementById('reinstallStatus');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="action-icon">⏳</span><span>Lädt herunter...</span>';
            status.style.display = 'block';
            status.innerHTML = '<p style="color: #856404;">Bibliotheken werden heruntergeladen, bitte warten...</p>';
            
            const formData = new FormData(libReinstallForm);
            formData.append('ajax', '1');
            formData.append('reinstall_libs', '1');
            
            fetch('/public/index.php?page=admin', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
                let html = '';
                let allSuccess = true;
                
                if (data.error) {
                    html += '<p style="color: #dc3545; font-weight: 500;">✗ ' + escapeHtml(data.error) + '</p>';
                    allSuccess = false;
                }
                
                if (data.results) {
                    for (const [lib, result] of Object.entries(data.results)) {
                        if (result.success) {
                            html += '<p style="color: #28a745; font-weight: 500;">✓ ' + escapeHtml(lib) + ' erfolgreich installiert</p>';
                        } else {
                            html += '<p style="color: #dc3545; font-weight: 500;">✗ ' + escapeHtml(lib) + ': ' + escapeHtml(result.error || 'Fehler') + '</p>';
                            allSuccess = false;
                        }
                    }
                }
                
                if (allSuccess && data.results && Object.keys(data.results).length > 0) {
                    html += '<p style="color: #28a745; font-weight: 600;"><strong>Alle Bibliotheken erfolgreich installiert! Seite wird neu geladen...</strong></p>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
                
                status.innerHTML = html;
                btn.disabled = false;
                btn.innerHTML = originalText;
            })
            .catch(error => {
                status.innerHTML = '<p style="color: #dc3545; font-weight: 500;">Fehler: ' + escapeHtml(error.message) + '</p>';
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }
});
</script>

</body>
</html>
