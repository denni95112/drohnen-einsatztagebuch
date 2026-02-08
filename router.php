<?php
/**
 * Router script for PHP built-in server when run from project root (development).
 * Routes /api/v1/* to the API so JSON endpoints work; other paths are served as files.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ($uri !== false && strpos($uri, '/api/v1/') === 0) {
    require __DIR__ . '/api/router.php';
    return true;
}
return false;
