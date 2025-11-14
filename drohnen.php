<?php
require_once 'db.php';
require 'auth.php';
requireAdminAuth();

// Drohne hinzufügen
if (isset($_POST['add'])) {
    $stmt = $db->prepare("INSERT INTO drohnen (name) VALUES (?)");
    $stmt->execute([$_POST['name']]);
    header("Location: drohnen.php");
    exit;
}

// Drohne löschen
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM drohnen WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: drohnen.php");
    exit;
}

// Drohnen abrufen
$drohnen = $db->query("SELECT id, name FROM drohnen ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Drohnen-Verwaltung</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/drohnen.css">
</head>
<body>

<div class="container">
    <h2>Drohnen-Verwaltung</h2>

    <form method="post">
        <div class="form-group">
            <input type="text" name="name" placeholder="Name der Drohne" required>
            <button type="submit" name="add" class="btn-add">Hinzufügen</button>
        </div>
    </form>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($drohnen as $drohne): ?>
            <tr>
                <td><?= $drohne['id'] ?></td>
                <td><?= htmlspecialchars($drohne['name']) ?></td>
                <td>
                    <a href="?delete=<?= $drohne['id'] ?>"
                       onclick="return confirm('Drohne wirklich löschen?')"
                       class="btn-delete">
                        Löschen
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="btn-container">
        <a href="index.php" class="back-btn">Zurück zur Übersicht</a>
    </div>
</div>

</body>
</html>
