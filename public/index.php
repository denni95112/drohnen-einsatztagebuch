<?php
/**
 * Front controller for web pages
 * Routes requests to appropriate views
 */

// Load autoloader
require_once dirname(__DIR__) . '/app/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load includes
require_once dirname(__DIR__) . '/includes/error_reporting.php';
require_once dirname(__DIR__) . '/includes/security_headers.php';
require_once dirname(__DIR__) . '/includes/version.php';
require_once dirname(__DIR__) . '/app/Services/AuthService.php';
require_once dirname(__DIR__) . '/app/Utils/Database.php';
require_once dirname(__DIR__) . '/utils.php'; // Load utility functions (getVersionedAsset, etc.)
require_once dirname(__DIR__) . '/version_check.php'; // Load version check function

use App\Services\AuthService;
use App\Models\Einsatz;

// Check config
if (!file_exists(dirname(__DIR__) . '/config/config.php')) {
    // Redirect to setup
    $page = $_GET['page'] ?? '';
    if ($page !== 'setup') {
        header('Location: /public/index.php?page=setup');
        exit;
    }
}

// Get requested page
$page = $_GET['page'] ?? 'index';

// Pages that don't require authentication
$publicPages = ['login', 'setup', 'read_only', 'about', 'changelog'];

// Require authentication for protected pages
if (!in_array($page, $publicPages)) {
    AuthService::requireAuth();
}

// Map page names to view files
$pageMap = [
    'index' => 'index.php',
    'login' => 'login.php',
    'about' => 'about.php',
    'admin' => 'admin.php',
    'changelog' => 'changelog.php',
    'dokumentation' => 'dokumentation.php',
    'drohnen' => 'drohnen.php',
    'einsatzliste' => 'einsatzliste.php',
    'neuer_einsatz' => 'neuer_einsatz.php',
    'personal' => 'personal.php',
    'read_only' => 'read_only.php',
    'setup' => 'setup.php'
];

// Check if page exists
if (!isset($pageMap[$page])) {
    http_response_code(404);
    die('Page not found');
}

$viewFile = dirname(__DIR__) . '/views/pages/' . $pageMap[$page];

if (!file_exists($viewFile)) {
    http_response_code(404);
    die('View file not found');
}

// Load page-specific data (config may not exist during setup)
$configPath = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configPath) ? include $configPath : [];
$isAdmin = AuthService::isAdminAuthenticated();

// Load data for index page
if ($page === 'index') {
    $einsatzModel = new Einsatz();
    $letzter_einsatz = $einsatzModel->getLastId();
    $dashboardApiManaged = \App\Services\DashboardApiService::isApiEnabled();
}

// Include view
include $viewFile;
