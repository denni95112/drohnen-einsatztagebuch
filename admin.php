<?php
require_once 'db.php';
require_once 'utils.php';
require 'auth.php';
requireAdminAuth();

$configPath = __DIR__ . '/config/config.php';
$config = include $configPath;
$databasePath = isset($config['database_path']) ? $config['database_path'] : 'einsatzbuch.db';
if (is_absolute_path($databasePath)) {
    $dbFile = $databasePath;
} else {
    $dbFile = __DIR__ . '/' . $databasePath;
}

if (isset($_POST['delete_all_einsaetze'])) {
    $db->beginTransaction();
    try {
        $db->exec("DELETE FROM einsatz_dokumentation");
        $db->exec("DELETE FROM einsatz_personal");
        $db->exec("DELETE FROM einsatz");
        $db->commit();
        header("Location: admin.php?success=einsaetze_geloescht");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        die("Fehler beim Löschen: " . $e->getMessage());
    }
}

if (isset($_GET['download_db'])) {
    if (file_exists($dbFile)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="einsatztagebuch_backup.db"');
        readfile($dbFile);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['db_upload'])) {
    if ($_FILES['db_upload']['error'] == 0) {
        move_uploaded_file($_FILES['db_upload']['tmp_name'], $dbFile);
        header("Location: admin.php?success=db_hochgeladen");
        exit;
    }
}

if (isset($_POST['delete_db']) && file_exists($dbFile)) {
    unlink($dbFile);
    header("Location: admin.php?success=db_geloescht");
    exit;
}

if (isset($_POST['create_db']) && !file_exists($dbFile)) {
    require_once 'db.php';
    header("Location: admin.php?success=db_neu_erstellt");
    exit;
}

if (isset($_POST['update_unit'])) {
    $config['navigation_title'] = trim($_POST['einheit']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: admin.php?success=einheit_geaendert");
            exit;
        } else {
            @unlink($tempPath);
            die("Fehler beim Speichern der Konfiguration");
        }
    } else {
        die("Fehler beim Schreiben der Konfiguration");
    }
}

if (isset($_POST['update_password'])) {
    $config['password_hash'] = hash('sha256', $_POST['admin_passwort']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: admin.php?success=passwort_geaendert");
            exit;
        } else {
            @unlink($tempPath);
            die("Fehler beim Speichern der Konfiguration");
        }
    } else {
        die("Fehler beim Schreiben der Konfiguration");
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verwaltung</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'header.php'; ?>

<h2>Admin Verwaltung</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="success-message">
        <?php 
        $messages = [
            "einsaetze_geloescht" => "Alle Einsätze wurden gelöscht!",
            "db_hochgeladen" => "Datenbank erfolgreich hochgeladen!",
            "db_geloescht" => "Datenbankdatei gelöscht!",
            "db_neu_erstellt" => "Neue Datenbank angelegt!",
            "einheit_geaendert" => "Einheit erfolgreich geändert!",
            "passwort_geaendert" => "Passwort wurde geändert!"
        ];
        echo $messages[$_GET['success']] ?? "Aktion erfolgreich!";
        ?>
    </div>
<?php endif; ?>

<!-- Alle Einsätze löschen -->
<form method="post" onsubmit="return confirm('Wirklich alle Einsätze löschen?')">
    <button type="submit" name="delete_all_einsaetze" class="danger">Alle Einsätze löschen</button>
</form>

<!-- Datenbank herunterladen -->
<form method="get">
    <button type="submit" name="download_db">Datenbank herunterladen</button>
</form>

<!-- Datenbank hochladen -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="db_upload" required>
    <button type="submit">Datenbank hochladen</button>
</form>

<!-- Datenbank löschen -->
<!-- <form method="post" onsubmit="return confirm('Wirklich die gesamte Datenbank löschen?')">
    <button type="submit" name="delete_db" class="danger">Datenbankdatei löschen</button>
</form> -->

<!-- Datenbank neu anlegen -->
<?php if (!file_exists($dbFile)): ?>
<form method="post">
    <button type="submit" name="create_db">Datenbank neu anlegen</button>
</form>
<?php endif; ?>

<!-- Name der Einheit ändern -->
<form method="post">
    <label>Neuer Name der Einheit:</label>
    <input type="text" name="einheit" value="<?= htmlspecialchars($config['navigation_title']) ?>" required>
    <button type="submit" name="update_unit">Speichern</button>
</form>

<!-- Passwort ändern -->
<form method="post">
    <label>Neues Passwort:</label>
    <input type="password" name="admin_passwort" required>
    <button type="submit" name="update_password">Passwort ändern</button>
</form>

<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

<?php include 'footer.php'; ?>

</body>
</html>
