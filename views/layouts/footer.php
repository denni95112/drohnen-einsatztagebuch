<footer>
    <?php
    // Simple path handling: if script is in updater directory, use ../ prefix
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = (strpos($scriptName, '/updater/') !== false || strpos($scriptName, '\\updater\\') !== false) ? '../' : '';
    ?>
    <p>MIT License - Erstellt von <a href="https://github.com/denni95112">Dennis Bögner</a></p>
    <?php
    // Determine correct path based on context - always use absolute paths
    if (strpos($scriptName, '/updater/') !== false || strpos($scriptName, '\\updater\\') !== false) {
        $changelogUrl = '../public/index.php?page=changelog';
        $aboutUrl = '../public/index.php?page=about';
    } else {
        $changelogUrl = '/public/index.php?page=changelog';
        $aboutUrl = '/public/index.php?page=about';
    }
    ?>
    <p>Version <?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?> - <a href="<?php echo htmlspecialchars($changelogUrl); ?>">Changelog</a> - <a href="<?php echo htmlspecialchars($aboutUrl); ?>">Über</a></p>
    <p><a href="https://open-drone-tools.de/">open-drone-tools.de</a></p>
    <p><a href="https://github.com/denni95112/drohnen-einsatztagebuch">GitHub</a></p>
    <?php 
    $buyMeACoffeePath = dirname(__DIR__, 2) . '/includes/buy_me_a_coffee.php';
    if (!file_exists($buyMeACoffeePath)) {
        $buyMeACoffeePath = __DIR__ . '/../components/buy_me_a_coffee.php';
    }
    if (file_exists($buyMeACoffeePath)) {
        include $buyMeACoffeePath;
    }
    ?>
</footer>

