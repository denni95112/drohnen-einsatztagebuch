<?php
require_once 'utils.php';
$config = include __DIR__ . '/config/config.php';
$error = '';

require 'auth.php';

if(isAuthenticated()){
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $passwordHash = hash('sha256', $password);
    
    if ($passwordHash === $config['password_hash'] || $passwordHash === $config['admin_password_hash']) { 
        $_SESSION['loggedin'] = true;
        
        // Set admin session if admin password
        if ($passwordHash === $config['admin_password_hash']) {
            $_SESSION['adminloggedin'] = true;
        }
        
        // Set cookie before redirect
        setLoginCookie($password);
        
        // Ensure session is written before redirect
        session_write_close();
        
        header('Location: index.php');
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
        // Display logo if configured
        if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
            <img src="<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo" class="login-logo">
        <?php endif; ?>
        <h1>Login</h1>
        <h3><?php echo $config['navigation_title'] ?></h3>
        <form method="post" action="login.php" class="login-form">
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