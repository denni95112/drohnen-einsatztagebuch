<?php
/**
 * Check for new version on GitHub
 * 
 * @param string $currentVersion Current version of the application
 * @param array $config Configuration array
 * @return array|null Array with 'new_version' and 'url' if update available, null otherwise
 */
function checkForNewVersion($currentVersion, $config) {
    $cacheFile = __DIR__ . '/cache/version_check.json';
    $cacheTime = 3600;
    
    $cacheData = null;
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if ($cacheData && isset($cacheData['timestamp']) && (time() - $cacheData['timestamp']) < $cacheTime) {
            if (isset($cacheData['new_version']) && version_compare($cacheData['new_version'], $currentVersion, '>')) {
                return [
                    'new_version' => $cacheData['new_version'],
                    'url' => $cacheData['url'] ?? 'https://github.com/denni95112/drohnen-einsatztagebuch/releases/latest'
                ];
            }
            return null;
        }
    }
    
    if (!function_exists('curl_init')) {
        return null;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/denni95112/drohnen-einsatztagebuch/releases/latest');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Drohnen-Einsatztagebuch');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3+json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    // If SSL verification failed, retry without verification (less secure but works)
    if ($response === false && ($curlErrno === CURLE_SSL_CACERT || $curlErrno === CURLE_SSL_PEER_CERTIFICATE || 
        strpos($curlError, 'SSL') !== false || strpos($curlError, 'certificate') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        // If API call failed and we have cached data, return it
        if ($cacheData && isset($cacheData['new_version']) && $cacheData['new_version']) {
            return [
                'new_version' => $cacheData['new_version'],
                'url' => $cacheData['url'] ?? 'https://github.com/denni95112/drohnen-einsatztagebuch/releases/latest'
            ];
        }
        return null;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['tag_name'])) {
        return null;
    }
    
    $latestVersion = ltrim($data['tag_name'], 'v');
    $releaseUrl = $data['html_url'] ?? 'https://github.com/denni95112/drohnen-einsatztagebuch/releases/latest';
    
    $hasUpdate = version_compare($latestVersion, $currentVersion, '>');
    
    if (!is_dir(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    
    file_put_contents($cacheFile, json_encode([
        'timestamp' => time(),
        'new_version' => $hasUpdate ? $latestVersion : null,
        'url' => $releaseUrl
    ]));
    
    if ($hasUpdate) {
        return [
            'new_version' => $latestVersion,
            'url' => $releaseUrl
        ];
    }
    
    return null;
}

