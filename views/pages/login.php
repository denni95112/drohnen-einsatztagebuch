<?php
/**
 * Login page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;

$error = '';

if (isAuthenticated()) {
    header('Location: /public/index.php?page=index');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (AuthService::login($password)) {
        header('Location: /public/index.php?page=index');
        exit();
    } else {
        $error = 'Falsches Passwort!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $config['navigation_title'] ?></title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/login.css') ?>">
</head>
<body>
    <div class="login-container">
        <?php 
        $logoPath = dirname(__DIR__, 2) . '/' . ($config['logo_path'] ?? '');
        $logoUrl = function_exists('getLogoUrl') ? getLogoUrl() : '';
        if (!empty($config['logo_path']) && file_exists($logoPath)): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="login-logo">
        <?php endif; ?>
        <h1>Login</h1>
        <h3><?php echo $config['navigation_title'] ?></h3>
        <form method="post" action="/public/index.php?page=login" class="login-form">
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Passwort eingeben" required>
            </div>
            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit" class="btn-login">Einloggen</button>
        </form>
        <footer>
            <p>MIT License - Erstellt von <a href="https://github.com/denni95112">Dennis Bögner</a></p>
            <p>Version <?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?></p>
            <p><a href="https://github.com/denni95112/drohnen-einsatztagebuch">GitHub</a></p>
        </footer>
    </div>
</body>
</html>
