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
    
    $separator = strpos($path, '?') !== false ? '&' : '?';
    // Use /public/ so assets work with both Apache (.htaccess) and PHP built-in server (project root)
    $path = '/public/' . ltrim($path, '/');
    return $path . $separator . 'v=' . urlencode($version);
}

/**
 * Get URL for the logo image (served via logo.php so it works with any document root).
 *
 * @return string URL to the logo script, or empty string if no logo
 */
function getLogoUrl() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    // Use /public/ so logo works from main app and from updater (project root doc)
    $base = (strpos($scriptName, '/public/') !== false || strpos($scriptName, '/updater/') !== false) ? '/public' : '';
    return $base . '/logo.php';
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
