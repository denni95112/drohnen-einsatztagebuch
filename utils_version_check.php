/**
 * Check GitHub for new version
 * @param string $currentVersion Current version (e.g., '1.0.0')
 * @param string $owner GitHub username/organization
 * @param string $repo Repository name
 * @return array|null Returns array with 'available', 'version', and 'url' keys, or null on error
 */
function checkGitHubVersion($currentVersion, $owner, $repo) {
    $cacheFile = __DIR__ . '/logs/github_version_cache.json';
    $cacheTime = 3600; // Cache for 1 hour
    
    // Create logs directory if it doesn't exist
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if ($cache && isset($cache['timestamp']) && (time() - $cache['timestamp']) < $cacheTime) {
            return $cache['data'];
        }
    }
    
    // Fetch all releases to find the latest non-draft, non-prerelease release
    $url = "https://api.github.com/repos/{$owner}/{$repo}/releases";
    $response = null;
    $httpCode = 0;
    
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Drohnen-Einsatztagebuch',
            'Accept: application/vnd.github.v3+json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // If SSL verification failed, try again without verification (less secure but works)
        if ($httpCode === 0 && strpos($curlError, 'SSL') !== false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Drohnen-Einsatztagebuch',
                'Accept: application/vnd.github.v3+json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
    } elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Drohnen-Einsatztagebuch',
                    'Accept: application/vnd.github.v3+json'
                ],
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $httpCode = 200;
        }
    }
    
    if ($httpCode !== 200 || !$response) {
        // Return cached data if available, even if expired
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && isset($cache['data'])) {
                return $cache['data'];
            }
        }
        return null;
    }
    
    $releases = json_decode($response, true);
    if (!is_array($releases) || empty($releases)) {
        return null;
    }
    
    // Find the latest non-draft, non-prerelease release
    $latestRelease = null;
    foreach ($releases as $release) {
        if (isset($release['draft']) && $release['draft'] === true) {
            continue;
        }
        if (isset($release['prerelease']) && $release['prerelease'] === true) {
            continue;
        }
        if (!isset($release['tag_name'])) {
            continue;
        }
        $latestRelease = $release;
        break; // Releases are already sorted by date, newest first
    }
    
    if (!$latestRelease || !isset($latestRelease['tag_name'])) {
        return null;
    }
    
    $latestVersion = ltrim($latestRelease['tag_name'], 'v');
    $available = version_compare($latestVersion, $currentVersion, '>');
    
    $result = [
        'available' => $available,
        'version' => $latestVersion,
        'url' => $latestRelease['html_url'] ?? "https://github.com/{$owner}/{$repo}/releases/latest"
    ];
    
    // Save to cache
    @file_put_contents($cacheFile, json_encode([
        'timestamp' => time(),
        'data' => $result
    ]));
    
    return $result;
}
