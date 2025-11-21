<?php
$config = include __DIR__ . '/config/config.php';
session_start();
$error = '';

include('auth.php');

if(isAuthenticated()){
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    if (hash('sha256', $password) === $config['password_hash'] || hash('sha256', $password) === $config['admin_password_hash']) { 
        $_SESSION['loggedin'] = true;
        setLoginCookie($password); 
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
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
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
        <?php include 'footer.php'; ?>
    </div>
</body>
</html>