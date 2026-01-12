<footer>
    <?php
    // Simple path handling: if script is in updater directory, use ../ prefix
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = (strpos($scriptName, '/updater/') !== false || strpos($scriptName, '\\updater\\') !== false) ? '../' : '';
    ?>
    <p>MIT License - Erstellt von <a href="https://github.com/denni95112">Dennis Bögner</a></p>
    <p>Version <?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?> - <a href="<?php echo htmlspecialchars($basePath); ?>changelog.php">Changelog</a> - <a href="<?php echo htmlspecialchars($basePath); ?>about.php">Über</a></p>
    <p><a href="https://github.com/denni95112/drohnen-einsatztagebuch">GitHub</a></p>
    <?php 
    $buyMeACoffeePath = __DIR__ . '/includes/buy_me_a_coffee.php';
    if (file_exists($buyMeACoffeePath)) {
        include $buyMeACoffeePath;
    }
    ?>
</footer>

