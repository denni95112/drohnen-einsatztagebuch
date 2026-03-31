<?php
/**
 * Utility functions shared across the application
 */

/**
 * Check if a path is absolute (Windows: C:\ or \\, Unix: /)
 * 
 * @param string $path The path to check
 * @return bool True if absolute, false if relative
 */
function is_absolute_path($path) {
    if (empty($path)) {
        return false;
    }
    return (PHP_OS_FAMILY === 'Windows' && (preg_match('/^[A-Z]:[\\\\\/]/i', $path) || preg_match('/^\\\\/', $path))) 
        || (PHP_OS_FAMILY !== 'Windows' && $path[0] === '/');
}

/**
 * Web path to a file under public/ (css, js, vendor, img, …).
 * Uses dirname(SCRIPT_NAME) so assets work when the app lives in a subdirectory
 * (e.g. https://example.org/myorg/public/index.php → …/public/vendor/leaflet/…).
 * When the document root is the public/ folder, SCRIPT_NAME is often /index.php
 * and paths become /css/… (no /public/ prefix).
 *
 * @param string $path Relative path inside public/ (e.g. 'css/styles.css')
 * @return string URL path starting with /
 */
function getPublicAssetUrlPath($path) {
    $path = ltrim((string) $path, '/');
    if (strpos($path, 'public/') === 0) {
        $path = substr($path, 7);
    }
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/index.php';
    $dir = dirname($scriptName);
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $urlPath = '/' . $path;
    } else {
        $urlPath = rtrim($dir, '/') . '/' . $path;
    }
    if ($urlPath === '' || $urlPath[0] !== '/') {
        $urlPath = '/' . ltrim($urlPath, '/');
    }
    return $urlPath;
}

/**
 * Get versioned asset URL (for cache busting)
 * 
 * @param string $path Relative path to the asset (e.g., 'css/styles.css')
 * @return string Path with version query parameter appended
 */
function getVersionedAsset($path) {
    static $version = null;
    
    if ($version === null) {
        if (!defined('APP_VERSION')) {
            $versionPath = __DIR__ . '/includes/version.php';
            if (file_exists($versionPath)) {
                require_once $versionPath;
            }
        }
        $version = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
    }
    
    $glue = (strpos($path, '?') !== false) ? '&' : '?';
    $urlPath = getPublicAssetUrlPath($path);
    return $urlPath . $glue . 'v=' . urlencode($version);
}

/**
 * Get current request base URL (scheme + host, no path).
 * Used for absolute URLs (e.g. QR code) so no config 'domain' is needed.
 *
 * @return string e.g. "http://localhost:8001"
 */
function getBaseUrl() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return ($https ? 'https' : 'http') . '://' . $host;
}

/**
 * Get URL for the logo image (served via logo.php so it works with any document root).
 *
 * @return string URL to the logo script, or empty string if no logo
 */
function getLogoUrl() {
    return getPublicAssetUrlPath('logo.php');
}

/**
 * Write config array to config file (used when updating multiple keys at once to avoid read cache issues).
 * @param array $config Full config array to write
 * @return bool Success status
 */
function writeConfig(array $config) {
    $configFile = __DIR__ . '/config/config.php';
    $content = "<?php\nreturn [\n";
    foreach ($config as $k => $v) {
        if (is_array($v)) {
            $content .= "    '{$k}' => [\n";
            foreach ($v as $subKey => $subValue) {
                $subValueEscaped = addslashes($subValue);
                $content .= "        '{$subKey}' => '{$subValueEscaped}',\n";
            }
            $content .= "    ],\n";
        } elseif (is_bool($v)) {
            $content .= "    '{$k}' => " . ($v ? 'true' : 'false') . ",\n";
        } elseif (is_numeric($v) && !is_string($v)) {
            $content .= "    '{$k}' => {$v},\n";
        } else {
            $vEscaped = addslashes((string) $v);
            $content .= "    '{$k}' => '{$vEscaped}',\n";
        }
    }
    $content .= "];\n";
    $backupFile = $configFile . '.backup.' . time();
    @copy($configFile, $backupFile);
    $result = file_put_contents($configFile, $content) !== false;
    if ($result) {
        @unlink($backupFile);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile, true);
        }
    }
    return $result;
}

/**
 * Update a config value in the config file
 * @param string $key Config key to update
 * @param mixed $value New value
 * @return bool Success status
 */
function updateConfig($key, $value) {
    $configFile = __DIR__ . '/config/config.php';
    if (!file_exists($configFile)) {
        return false;
    }
    
    // Read current config
    $config = include $configFile;
    if (!is_array($config)) {
        return false;
    }
    
    // Update the value
    $config[$key] = $value;
    
    // Write back to file, preserving structure
    $content = "<?php\nreturn [\n";
    
    foreach ($config as $k => $v) {
        if (is_array($v)) {
            $content .= "    '{$k}' => [\n";
            foreach ($v as $subKey => $subValue) {
                $subValueEscaped = addslashes($subValue);
                $content .= "        '{$subKey}' => '{$subValueEscaped}',\n";
            }
            $content .= "    ],\n";
        } elseif (is_bool($v)) {
            $content .= "    '{$k}' => " . ($v ? 'true' : 'false') . ",\n";
        } elseif (is_numeric($v) && !is_string($v)) {
            $content .= "    '{$k}' => {$v},\n";
        } else {
            $vEscaped = addslashes($v);
            $content .= "    '{$k}' => '{$vEscaped}',\n";
        }
    }
    
    $content .= "];\n";
    
    // Create backup before writing
    $backupFile = $configFile . '.backup.' . time();
    @copy($configFile, $backupFile);
    
    $result = file_put_contents($configFile, $content) !== false;
    
    // Remove backup if write was successful
    if ($result) {
        @unlink($backupFile);
    }
    
    return $result;
}

/**
 * Send install/update tracking webhook to open-drone-tools.de.
 * Fire-and-forget: does not throw or block on failure.
 *
 * @param string $repo Repository name (e.g. GITHUB_REPO_NAME)
 * @param string $version Version string (e.g. APP_VERSION or update version)
 */
function sendInstallTrackingWebhook(string $repo, string $version): void {
    $url = 'https://open-drone-tools.de/webhook.php';
    $payload = json_encode([
        'repo' => trim($repo),
        'version' => trim($version),
    ]);
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        @curl_exec($ch);
        @curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);
        @file_get_contents($url, false, $context);
    }
}
