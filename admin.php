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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
    $fileType = $_FILES['logo']['type'];
    
    if (in_array($fileType, $allowedTypes)) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])) {
            @unlink(__DIR__ . '/' . $config['logo_path']);
        }
        
        $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
            $config['logo_path'] = 'uploads/' . $fileName;
            $tempPath = $configPath . '.tmp';
            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            if (file_put_contents($tempPath, $configContent) !== false) {
                if (rename($tempPath, $configPath)) {
                    header("Location: admin.php?success=logo_geaendert");
                    exit;
                } else {
                    @unlink($tempPath);
                    die("Fehler beim Speichern der Konfiguration");
                }
            } else {
                die("Fehler beim Schreiben der Konfiguration");
            }
        }
    }
}

if (isset($_POST['delete_logo']) && !empty($config['logo_path'])) {
    if (file_exists(__DIR__ . '/' . $config['logo_path'])) {
        @unlink(__DIR__ . '/' . $config['logo_path']);
    }
    unset($config['logo_path']);
    $tempPath = $configPath . '.tmp';
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($tempPath, $configContent) !== false) {
        if (rename($tempPath, $configPath)) {
            header("Location: admin.php?success=logo_geloescht");
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
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
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
            "passwort_geaendert" => "Passwort wurde geändert!",
            "logo_geaendert" => "Logo erfolgreich hochgeladen!",
            "logo_geloescht" => "Logo erfolgreich gelöscht!"
        ];
        echo $messages[$_GET['success']] ?? "Aktion erfolgreich!";
        ?>
    </div>
<?php endif; ?>

<div class="admin-sections">
    <!-- Datenbank Verwaltung -->
    <div class="admin-section">
        <h3>📊 Datenbank Verwaltung</h3>
        <div class="admin-actions">
            <form method="get" class="admin-action-item">
                <button type="submit" name="download_db" class="btn-action">
                    <span class="action-icon">⬇️</span>
                    <span>Datenbank herunterladen</span>
                </button>
            </form>
            
            <form method="post" enctype="multipart/form-data" class="admin-action-item">
                <label class="file-input-label">
                    <input type="file" name="db_upload" required class="file-input">
                    <span class="file-input-button">📤 Datei auswählen</span>
                    <span class="file-input-text">Keine Datei ausgewählt</span>
                </label>
                <button type="submit" class="btn-action">Datenbank hochladen</button>
            </form>
            
            <?php if (!file_exists($dbFile)): ?>
            <form method="post" class="admin-action-item">
                <button type="submit" name="create_db" class="btn-action">
                    <span class="action-icon">➕</span>
                    <span>Datenbank neu anlegen</span>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Einsätze Verwaltung -->
    <div class="admin-section admin-section-danger">
        <h3>⚠️ Einsätze Verwaltung</h3>
        <form method="post" onsubmit="return confirm('Wirklich alle Einsätze löschen? Diese Aktion kann nicht rückgängig gemacht werden!')">
            <button type="submit" name="delete_all_einsaetze" class="danger btn-full">
                🗑️ Alle Einsätze löschen
            </button>
        </form>
    </div>

    <!-- Einstellungen -->
    <div class="admin-section">
        <h3>⚙️ Einstellungen</h3>
        
        <form method="post" class="admin-form-item">
            <label>Name der Einheit:</label>
            <input type="text" name="einheit" value="<?= htmlspecialchars($config['navigation_title']) ?>" required>
            <button type="submit" name="update_unit" class="btn-action-inline">💾 Speichern</button>
        </form>

        <form method="post" class="admin-form-item">
            <label>Admin Passwort ändern:</label>
            <input type="password" name="admin_passwort" placeholder="Neues Passwort eingeben" required>
            <button type="submit" name="update_password" class="btn-action-inline">🔒 Passwort ändern</button>
        </form>
    </div>

    <!-- Logo Verwaltung -->
    <div class="admin-section">
        <h3>🖼️ Logo Verwaltung</h3>
        
        <?php if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
        <div class="current-logo">
            <p><strong>Aktuelles Logo:</strong></p>
            <div class="logo-preview">
                <img src="<?= htmlspecialchars($config['logo_path']) ?>" alt="Logo">
            </div>
        </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="admin-form-item">
            <label class="file-input-label">
                <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp" class="file-input">
                <span class="file-input-button">📁 Logo auswählen</span>
                <span class="file-input-text">Keine Datei ausgewählt</span>
            </label>
            <div class="logo-actions">
                <button type="submit" class="btn-action">📤 Logo hochladen</button>
                <?php if (!empty($config['logo_path']) && file_exists(__DIR__ . '/' . $config['logo_path'])): ?>
                    <button type="submit" name="delete_logo" class="danger btn-action" onclick="return confirm('Logo wirklich löschen?')">🗑️ Logo löschen</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('.file-input');
    
    fileInputs.forEach(input => {
        const textSpan = input.nextElementSibling.nextElementSibling;
        
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                textSpan.textContent = this.files[0].name;
                textSpan.style.color = '#10b981';
                textSpan.style.fontStyle = 'normal';
            } else {
                textSpan.textContent = 'Keine Datei ausgewählt';
                textSpan.style.color = '#64748b';
                textSpan.style.fontStyle = 'italic';
            }
        });
    });
});
</script>

</body>
</html>
