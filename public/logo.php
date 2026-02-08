<?php
/**
 * Serves the configured logo from project uploads directory.
 * Works regardless of document root (public/ vs project root).
 */
$configPath = dirname(__DIR__) . '/config/config.php';
if (!file_exists($configPath)) {
    http_response_code(404);
    exit;
}
$config = include $configPath;
$logoPath = $config['logo_path'] ?? '';
if (empty($logoPath) || strpos($logoPath, '..') !== false) {
    http_response_code(404);
    exit;
}
// Only allow paths under uploads/
if (strpos(ltrim($logoPath, '/'), 'uploads/') !== 0 && $logoPath !== 'uploads') {
    http_response_code(404);
    exit;
}
$fullPath = dirname(__DIR__) . '/' . ltrim($logoPath, '/');
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}
$mimes = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'webp' => 'image/webp',
];
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mime = $mimes[$ext] ?? 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Cache-Control: private, max-age=3600');
readfile($fullPath);
