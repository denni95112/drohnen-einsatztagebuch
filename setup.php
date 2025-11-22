<?php
/**
 * Check which required libraries are missing
 *
 * @return array Array of missing libraries with their download information
 */
function checkLibraries() {
        $libs = [
            'dompdf' => [
                'path' => __DIR__ . '/lib/dompdf/autoload.inc.php',
                'name' => 'dompdf',
                'url' => 'https://github.com/dompdf/dompdf/releases/download/v3.1.4/dompdf-3.1.4.zip',
                'github_api' => 'null'
            ],
            'phpqrcode' => [
                'path' => __DIR__ . '/lib/phpqrcode/qrlib.php',
                'name' => 'phpqrcode',
                'url' => 'https://github.com/t0k4rt/phpqrcode/archive/refs/heads/master.zip',
                'github_api' => null
            ]
        ];
        
        $missing = [];
        foreach ($libs as $key => $lib) {
            if (!file_exists($lib['path'])) {
                $missing[$key] = $lib;
            }
        }
        
        return $missing;
    }
    
/**
 * Get manual installation instructions for a library
 *
 * @param array $libInfo Library information array
 * @return array Manual installation instructions with title and steps
 */
    function getManualInstallInstructions($libInfo) {
        $libDir = __DIR__ . '/lib';
        $instructions = [];
        
        if ($libInfo['name'] === 'dompdf') {
            $instructions = [
                'title' => 'dompdf manuell installieren',
                'steps' => [
                    '1. Erstelle das Verzeichnis: mkdir -p ' . $libDir . '/dompdf',
                    '2. Lade dompdf herunter:',
                    '   wget https://github.com/dompdf/dompdf/releases/download/v3.1.4/dompdf-3.1.4.zip',
                    '   ODER: curl -L -o dompdf.zip https://github.com/dompdf/dompdf/releases/download/v3.1.4/dompdf-3.1.4.zip',
                    '3. Entpacke die ZIP-Datei:',
                    '   unzip dompdf-3.1.4.zip -d ' . $libDir . '/dompdf_temp',
                    '4. Finde das dompdf-Verzeichnis im entpackten Inhalt und verschiebe es:',
                    '   mv ' . $libDir . '/dompdf_temp/dompdf-3.1.4/* ' . $libDir . '/dompdf/',
                    '   ODER falls die Dateien direkt im ZIP sind:',
                    '   mv ' . $libDir . '/dompdf_temp/* ' . $libDir . '/dompdf/',
                    '5. Aufräumen:',
                    '   rm -rf ' . $libDir . '/dompdf_temp dompdf-3.1.4.zip',
                    '6. Überprüfe, dass die Datei existiert:',
                    '   ls -la ' . $libDir . '/dompdf/autoload.inc.php'
                ]
            ];
        } elseif ($libInfo['name'] === 'phpqrcode') {
            $instructions = [
                'title' => 'phpqrcode manuell installieren',
                'steps' => [
                    '1. Erstelle das Verzeichnis: mkdir -p ' . $libDir . '/phpqrcode',
                    '2. Lade phpqrcode herunter:',
                    '   wget https://github.com/t0k4rt/phpqrcode/archive/refs/heads/master.zip',
                    '   ODER: curl -L -o phpqrcode.zip https://github.com/t0k4rt/phpqrcode/archive/refs/heads/master.zip',
                    '3. Entpacke die ZIP-Datei:',
                    '   unzip master.zip -d ' . $libDir . '/phpqrcode_temp',
                    '4. Finde das phpqrcode-Verzeichnis im entpackten Inhalt und verschiebe es:',
                    '   mv ' . $libDir . '/phpqrcode_temp/phpqrcode-master/* ' . $libDir . '/phpqrcode/',
                    '5. Aufräumen:',
                    '   rm -rf ' . $libDir . '/phpqrcode_temp master.zip',
                    '6. Überprüfe, dass die Datei existiert:',
                    '   ls -la ' . $libDir . '/phpqrcode/qrlib.php'
                ]
            ];
        }
        
        return $instructions;
    }
    
/**
 * Download and extract a library from its URL
 *
 * @param array $libInfo Library information array with URL and name
 * @param string $libDir Directory where library should be installed
 * @return array Result array with success status and optional error message
 */
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
            __DIR__ . '/cacert.pem', // Local certificate bundle
            ini_get('curl.cainfo'), // PHP ini setting
            ini_get('openssl.cafile'), // OpenSSL CA file
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
                $manualInstructions = getManualInstallInstructions($libInfo);
                return [
                    'success' => false, 
                    'error' => 'ZipArchive ist nicht verfügbar und unzip-Befehl wurde nicht gefunden. ' .
                               'Bitte installiere entweder die PHP Zip Extension oder unzip. ' .
                               'Alternativ kannst du die Bibliotheken manuell installieren.',
                    'manual_instructions' => $manualInstructions
                ];
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

require_once 'utils.php';
    
/**
 * Recursively remove a directory and its contents
 *
 * @param string $dir Directory path to remove
 * @return void
 */
    function rmdir_recursive($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? rmdir_recursive($path) : unlink($path);
        }
        rmdir($dir);
    }
    
/**
 * Recursively find the library path within extracted files
 *
 * @param string $dir Directory to search in
 * @param string $libName Name of the library to find
 * @return string|null Path to library directory or null if not found
 */
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

$isAjaxRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && 
                 (!empty($_POST['ajax']) || 
                  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')));
$isDownloadRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_libs']));

if ($isAjaxRequest && $isDownloadRequest) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8', true);
        header('Cache-Control: no-cache, must-revalidate', true);
        header('X-Content-Type-Options: nosniff', true);
        header_remove('Location');
    }
    ob_start();
    
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            if (!empty($_POST['ajax'])) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'results' => [],
                    'remaining' => [],
                    'error' => 'Fatal error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    });
    
    try {
        $missingLibs = checkLibraries();
        $libDir = __DIR__ . '/lib';
        $results = [];
        
        foreach ($missingLibs as $key => $lib) {
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
            header('Cache-Control: no-cache, must-revalidate');
            header('X-Content-Type-Options: nosniff');
        }
        
        $response = [
            'results' => $results,
            'remaining' => checkLibraries()
        ];
        
        if (!empty($output) && trim($output) !== '') {
            $response['debug_output'] = substr($output, 0, 500); // Limit debug output
        }
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = json_encode([
                'results' => [],
                'remaining' => [],
                'error' => 'JSON encoding failed: ' . json_last_error_msg(),
                'debug_output' => substr($output, 0, 500)
            ], JSON_UNESCAPED_UNICODE);
        }
        
        echo $json;
        exit;
    } catch (Throwable $e) {
        $output = ob_get_clean();
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('X-Content-Type-Options: nosniff');
        }
        
        $response = [
            'results' => [],
            'remaining' => [],
            'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage(),
            'error_type' => get_class($e),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ];
        
        if (!empty($output)) {
            $response['debug_output'] = substr($output, 0, 500);
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($isDownloadRequest && !$isAjaxRequest) {
    if (ob_get_level() == 0) {
        ob_start();
    } else {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }
    
    try {
        $missingLibs = checkLibraries();
        $libDir = __DIR__ . '/lib';
        $results = [];
        
        foreach ($missingLibs as $key => $lib) {
            if (isset($_POST['lib_' . $key]) && $_POST['lib_' . $key] === '1') {
                $result = downloadLibrary($lib, $libDir);
                $results[$key] = $result;
            }
        }
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header("Location: setup.php");
        }
        exit;
    } catch (Throwable $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header("Location: setup.php?error=" . urlencode($e->getMessage()));
        }
        exit;
    }
}

$configPath = __DIR__ . '/config/config.php';

if (file_exists($configPath) && !isset($_POST['download_libs']) && !headers_sent()) {
    header("Location: login.php");
    exit;
}

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

$suggestedPaths = [];

if ($isWindows) {
    $suggestedPaths = [
        'C:/data/einsatzbuch.db' => 'C:/data/einsatzbuch.db - Empfohlen',
        'einsatzbuch.db' => '(einsatzbuch.db) - Weniger sicher',
        'D:/apps/einsatzbuch.db' => 'D:/apps/einsatzbuch.db',
        '../data/einsatzbuch.db' => '../data/einsatzbuch.db - Relativ',
        'C:/ProgramData/einsatzbuch.db' => 'C:/ProgramData/einsatzbuch.db',
    ];
} else {
    $user = get_current_user();
    $suggestedPaths = [
        '/var/data/einsatzbuch.db' => '/var/data/einsatzbuch.db - Empfohlen',
        'einsatzbuch.db' => '(einsatzbuch.db) - Weniger sicher',
        '/home/' . $user . '/data/einsatzbuch.db' => '/home/' . $user . '/data/einsatzbuch.db',
        '../data/einsatzbuch.db' => '../data/einsatzbuch.db - Relativ',
        '/opt/data/einsatzbuch.db' => '/opt/data/einsatzbuch.db',
    ];
}

/**
 * Generate a random token
 *
 * @param int $length Length of token in characters
 * @return string Hexadecimal token string
 */
function generateToken($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate a random IV for encryption
 *
 * @param int $length Length of IV in bytes
 * @return string Hexadecimal string representation of IV
 */
function generateIV($length = 16) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_config'])) {
    $tokenName = 'dg_' . preg_replace('/[^a-z0-9]/i', '', $_POST['ort']) . '_tagebuch_token';
    $navigationTitle = trim($_POST['einheit']) . ' ' . trim($_POST['ort']);
    $iv = substr(generateIV(), 0, 16);
    $passwordHash = hash('sha256', $_POST['passwort']);
    $adminPasswordHash = hash('sha256', $_POST['admin_passwort']);
    $readToken = generateToken(16);
    $domain = $_SERVER['HTTP_HOST'];
    
    $logo_path = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $fileType = $_FILES['logo']['type'] ?? '';
        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        // Check both MIME type and file extension for better compatibility
        if ((in_array($fileType, $allowedTypes) || empty($fileType)) && in_array($extension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
                $logo_path = 'uploads/' . $fileName;
            }
        }
    }

    $database_path_dropdown = $_POST['database_path_dropdown'] ?? '';
    $database_path_custom = trim($_POST['database_path_custom'] ?? '');
    
    if ($database_path_dropdown === 'custom' && !empty($database_path_custom)) {
        $database_path = $database_path_custom;
    } elseif ($database_path_dropdown === 'custom' && empty($database_path_custom)) {
        $database_path = 'einsatzbuch.db';
    } elseif (!empty($database_path_dropdown)) {
        $database_path = $database_path_dropdown;
    } else {
        $database_path = 'einsatzbuch.db';
    }
    
    $database_path = trim($database_path);
    $database_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $database_path);
    $database_path_escaped = addslashes($database_path);

    $path_to_dashboard_db = trim($_POST['path_to_dashboard_db'] ?? '');
    if (!empty($path_to_dashboard_db)) {
        $path_to_dashboard_db = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path_to_dashboard_db);
        $path_to_dashboard_db_escaped = addslashes($path_to_dashboard_db);
    }

    $dashboard_url = trim($_POST['dashboard_url'] ?? '');
    if (!empty($dashboard_url)) {
        $dashboard_url = rtrim($dashboard_url, '/');
        $dashboard_url_escaped = addslashes($dashboard_url);
    }

    $config = "<?php\nreturn [\n" .
        "    'token_name' => '" . addslashes($tokenName) . "',\n" .
        "    'navigation_title' => '" . addslashes($navigationTitle) . "',\n" .
        "    'encryption' => [\n" .
        "        'method' => 'aes-256-cbc',\n" .
        "        'iv' => '" . addslashes($iv) . "',\n" .
        "    ],\n" .
        "    'password_hash' => '" . $passwordHash . "',\n" .
        "    'admin_password_hash' => '" . $adminPasswordHash . "',\n" .
        "    'read_token' => '" . $readToken . "',\n" .
        "    'domain' => '" . addslashes($domain) . "',\n" .
        "    'database_path' => '" . $database_path_escaped . "',\n";
    
    // Add logo_path if it was uploaded and saved successfully
    if (!empty($logo_path)) {
        $config .= "    'logo_path' => '" . addslashes($logo_path) . "',\n";
    }
    
    if (!empty($path_to_dashboard_db)) {
        $config .= "    'path_to_dashboard_db' => '" . $path_to_dashboard_db_escaped . "',\n";
    }
    
    if (!empty($dashboard_url)) {
        $config .= "    'dashboard_url' => '" . $dashboard_url_escaped . "',\n";
    }
    
    $config .= "    'version' => '1.0.0',\n";
    $config .= "];\n";

    if (!is_dir(__DIR__ . '/config')) {
        mkdir(__DIR__ . '/config', 0755, true);
    }

    file_put_contents($configPath, $config);

    require_once 'db.php';

    header("Location: login.php");
    exit;
}

$missingLibraries = checkLibraries();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Einsatztagebuch</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/setup.css') ?>">
    <script src="js/setup.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <h2>Erstkonfiguration Einsatztagebuch</h2>

    <?php if (!empty($missingLibraries)): ?>
    <div class="library-section">
        <h3>Benötigte Bibliotheken</h3>
        <p>Die folgenden Bibliotheken fehlen und müssen heruntergeladen werden:</p>
        <form method="post" id="libDownloadForm">
            <?php foreach ($missingLibraries as $key => $lib): ?>
            <label>
                <input type="checkbox" name="lib_<?= $key ?>" value="1" checked>
                <strong><?= htmlspecialchars($lib['name']) ?></strong>
                <span>(<?= htmlspecialchars(basename($lib['path'])) ?>)</span>
            </label>
            <?php endforeach; ?>
            <button type="submit" name="download_libs" id="downloadLibsBtn">
                Bibliotheken herunterladen
            </button>
            <div id="downloadStatus"></div>
        </form>
    </div>
    <?php endif; ?>

    <form method="post" id="configForm" enctype="multipart/form-data">
        <label>Einheit (z. B. Feuerwehr, Drohnengruppe, etc.):<br>
            <input type="text" name="einheit" required>
        </label>
        <label>Ort:<br>
            <input type="text" name="ort" required>
        </label>
        <label>Passwort für Login:<br>
            <input type="password" name="passwort" required>
        </label>
        <label>Passwort für Admin Login:<br>
            <input type="password" name="admin_passwort" required>
        </label>
        <label>
            Datenbank-Pfad
            <span class="tooltip">?
                <span class="tooltiptext">
                    <strong>Sicherheitshinweis:</strong> Speichere die Datenbank außerhalb des Web-Verzeichnisses!<br><br>
                    <strong>Beispiele:</strong><br>
                    <?php if ($isWindows): ?>
                    Windows: C:/data/einsatzbuch.db oder ..\\data\\einsatzbuch.db<br>
                    <?php else: ?>
                    Linux: /var/data/einsatzbuch.db oder ../data/einsatzbuch.db<br>
                    <?php endif; ?>
                    Relativ: einsatzbuch.db (Standard, weniger sicher)<br><br>
                    Wähle einen empfohlenen Pfad oder verwende "Eigener Pfad" für eine benutzerdefinierte Option.
                </span>
            </span>
        </label>
        <select name="database_path_dropdown" id="database_path_dropdown" required>
            <?php foreach ($suggestedPaths as $path => $label): 
                $pathEscaped = htmlspecialchars($path);
                $labelEscaped = htmlspecialchars($label); ?>
                <option value="<?= $pathEscaped ?>"><?= $labelEscaped ?></option>
            <?php endforeach; ?>
            <option value="custom">Eigener Pfad...</option>
        </select>
        <input type="text" name="database_path_custom" id="database_path_custom" placeholder="Eigener Pfad eingeben...">
        <small>
            <?php if ($isWindows): ?>
                Betriebssystem erkannt: Windows
            <?php else: ?>
                Betriebssystem erkannt: Linux/Unix
            <?php endif; ?>
        </small>
        <br>
        <label>
            Pfad zur Flug Dashboard Datenbank (optional)
            <span class="tooltip">?
                <span class="tooltiptext">
                    Wenn du auch das Flug Dashboard verwendest, kann das Einsatztagebuch die Flüge automatisch einfügen. Dafür wird der Pfad zur Datenbank des Flugdashboards benötigt.
                </span>
            </span>
        </label>
        <input type="text" name="path_to_dashboard_db" placeholder="z.B. C:/data/dashboard-database.sqlite oder leer lassen">
        <br>
        <label>
            Flug Dashboard URL (optional)
            <span class="tooltip">?
                <span class="tooltiptext">
                    Die URL zum Flug Dashboard, falls du es verwendest. Wird für Verlinkungen und Integration verwendet.
                </span>
            </span>
        </label>
        <input type="url" name="dashboard_url" placeholder="z.B. https://example.com/dashboard oder leer lassen">
        <br>
        <label>
            Logo hochladen (optional)
            <span class="tooltip">?
                <span class="tooltiptext">
                    Lade ein Logo hoch, das links neben dem Titel angezeigt wird. Unterstützte Formate: JPG, PNG, GIF, SVG, WebP. Maximale Größe: 5MB.
                </span>
            </span>
        </label>
        <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp">
        <small>Optional: Logo wird links neben dem Titel angezeigt</small>
        <br>

        <button type="submit" name="submit_config" id="submitConfigBtn" <?= !empty($missingLibraries) ? 'disabled' : '' ?>>
            Konfiguration abschließen
        </button>
        <?php if (!empty($missingLibraries)): ?>
        <small class="error-message">
            Bitte lade zuerst die fehlenden Bibliotheken herunter.
        </small>
        <?php endif; ?>
    </form>

<?php include 'footer.php'; ?>

</body>
</html>
