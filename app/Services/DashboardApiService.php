<?php
namespace App\Services;

/**
 * Service for Flug-Dienstbuch API integration.
 * Fetches pilots, drones, locations and (later) creates flights via API.
 */
class DashboardApiService {

    /**
     * Check if Dashboard API is configured and should be used
     */
    public static function isApiEnabled(): bool {
        $config = self::getApiConfig();
        return $config !== null
            && !empty($config['url'])
            && !empty($config['token']);
    }

    /**
     * Get pilots from Flug-Dienstbuch API.
     * Returns array of items with keys: id, vorname, nachname, dashboard_id (compatible with Personal list)
     */
    public static function getPilots(): array {
        $response = self::makeApiRequest('/api/pilots.php?action=list', 'GET');
        if (!$response['success'] || empty($response['data']['pilots'])) {
            return [];
        }
        $list = [];
        foreach ($response['data']['pilots'] as $p) {
            $name = trim($p['name'] ?? '');
            $parts = explode(' ', $name, 2);
            $vorname = trim($parts[0] ?? '');
            $nachname = trim($parts[1] ?? '');
            $list[] = [
                'id' => (int) $p['id'],
                'vorname' => $vorname,
                'nachname' => $nachname,
                'dashboard_id' => (int) $p['id'],
            ];
        }
        usort($list, function ($a, $b) {
            $c = strcmp($a['nachname'], $b['nachname']);
            return $c !== 0 ? $c : strcmp($a['vorname'], $b['vorname']);
        });
        return $list;
    }

    /**
     * Get drones from Flug-Dienstbuch API.
     * Returns array of items with keys: id, name (compatible with Drohne list)
     */
    public static function getDrones(): array {
        $response = self::makeApiRequest('/api/drones.php?action=list', 'GET');
        if (!$response['success'] || empty($response['data']['drones'])) {
            return [];
        }
        $list = [];
        foreach ($response['data']['drones'] as $d) {
            $list[] = [
                'id' => (int) $d['id'],
                'name' => trim($d['drone_name'] ?? ''),
            ];
        }
        usort($list, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $list;
    }

    /**
     * Get flight locations from Flug-Dienstbuch API.
     * Returns array of items with at least: id, location_name
     */
    public static function getLocations(): array {
        $response = self::makeApiRequest('/api/locations.php?action=list', 'GET');
        if (!$response['success'] || empty($response['data']['locations'])) {
            return [];
        }
        return $response['data']['locations'];
    }

    /**
     * Make HTTP request to Dashboard API
     *
     * @param string $endpoint Path + query (e.g. /api/pilots.php?action=list)
     * @param string $method GET or POST
     * @param array|null $data For POST, optional body data (will be JSON-encoded)
     * @return array ['success' => bool, 'data' => array, 'error' => string, 'http_code' => int]
     */
    public static function makeApiRequest(string $endpoint, string $method = 'GET', ?array $data = null): array {
        $config = self::getApiConfig();
        if (!$config || empty($config['url']) || empty($config['token'])) {
            return ['success' => false, 'error' => 'API not configured', 'data' => [], 'http_code' => 0];
        }
        $baseUrl = rtrim($config['url'], '/');
        $url = $baseUrl . (strpos($endpoint, '/') === 0 ? $endpoint : '/' . $endpoint);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config['token'],
            'Content-Type: application/json',
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            return [
                'success' => false,
                'error' => $curlError ?: 'Request failed',
                'data' => [],
                'http_code' => $httpCode,
            ];
        }
        $decoded = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300
            && is_array($decoded)
            && !empty($decoded['success']);
        return [
            'success' => $success,
            'data' => $decoded['data'] ?? $decoded,
            'error' => $decoded['error'] ?? null,
            'http_code' => $httpCode,
        ];
    }

    /**
     * Get API config (url + token) from config file
     *
     * @return array|null ['url' => string, 'token' => string] or null
     */
    /**
     * @return array{url?: string, token?: string} Empty array if not configured
     */
    private static function getApiConfig(): array {
        static $config = null;
        if ($config !== null) {
            return $config;
        }
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        if (!file_exists($configPath)) {
            $config = [];
            return $config;
        }
        $cfg = include $configPath;
        if (!is_array($cfg)) {
            $config = [];
            return $config;
        }
        $url = trim($cfg['dashboard_api_url'] ?? '');
        $token = trim($cfg['dashboard_api_token'] ?? '');
        $config = ($url !== '' && $token !== '') ? ['url' => $url, 'token' => $token] : [];
        return $config;
    }
}
