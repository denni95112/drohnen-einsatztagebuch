<?php
require_once 'db.php';
require 'auth.php';
requireAdminAuth();

try {
// Personal hinzufügen
if(isset($_POST['add'])){
    $vorname = $_POST['vorname'] ?? '';
    $nachname = $_POST['nachname'] ?? '';
    $dashboard_id = $_POST['dashboard_id'] ?? null;

    if($dashboard_id === '' || $dashboard_id === null){
        $dashboard_id = null; // Allow NULL
    } else {
        $dashboard_id = intval($dashboard_id); // Ensure integer
    }

    $stmt = $db->prepare("INSERT INTO personal (vorname, nachname, dashboard_id) VALUES (?, ?, ?)");
    if($stmt->execute([$vorname, $nachname, $dashboard_id])){
        header("Location: personal.php");
        exit;
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Datenbankfehler beim Einfügen: " . $errorInfo[2];
        exit;
    }
}

// Personal löschen
if(isset($_GET['delete'])){
    $stmt = $db->prepare("DELETE FROM personal WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: personal.php");
    exit;
}

// Personal bearbeiten (Formular anzeigen)
$edit_personal = null;
if(isset($_GET['edit'])){
    $stmt = $db->prepare("SELECT id, vorname, nachname, dashboard_id FROM personal WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_personal = $stmt->fetch();
}

// Personal bearbeiten (Speichern)
if(isset($_POST['update'])){
    $vorname = $_POST['vorname'] ?? '';
    $nachname = $_POST['nachname'] ?? '';
    $dashboard_id = $_POST['dashboard_id'] ?? null;
    $id = $_POST['id'] ?? null;

    if(empty($id) || empty($vorname) || empty($nachname)){
        echo "Fehler: ID, Vorname oder Nachname fehlen!"; exit;
    }

    if($dashboard_id === '' || $dashboard_id === null){
        $dashboard_id = null; // Allow NULL
    } else {
        $dashboard_id = intval($dashboard_id); // Ensure integer
    }

    $stmt = $db->prepare("UPDATE personal SET vorname=?, nachname=?, dashboard_id=? WHERE id=?");
    if($stmt->execute([$vorname, $nachname, $dashboard_id, $id])){
        header("Location: personal.php");
        exit;
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Datenbankfehler beim Aktualisieren: " . $errorInfo[2];
        exit;
    }
}


// Personal aus DB abrufen
$personal = $db->query("SELECT id, vorname, nachname, dashboard_id FROM personal ORDER BY nachname, vorname")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<pre>Datenbankfehler: " . $e->getMessage() . "</pre>";
    exit;
} catch (Exception $e) {
    echo "<pre>Allgemeiner Fehler: " . $e->getMessage() . "</pre>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Personalverwaltung</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/personal.css">
</head>
<body>
<h2>Personalverwaltung</h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $edit_personal['id'] ?? '' ?>">

    <input type="text" name="vorname" required placeholder="Vorname" value="<?= htmlspecialchars($edit_personal['vorname'] ?? '') ?>">

    <input type="text" name="nachname" required placeholder="Nachname" value="<?= htmlspecialchars($edit_personal['nachname'] ?? '') ?>">

    <input type="number" name="dashboard_id" required placeholder="Dashboard ID" value="<?= htmlspecialchars($edit_personal['dashboard_id'] ?? '') ?>">

    <?php if($edit_personal): ?>
        <button type="submit" name="update">Änderungen speichern</button>
        <a href="personal.php">Abbrechen</a>
    <?php else: ?>
        <button type="submit" name="add">Personal hinzufügen</button>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Dashboard ID</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($personal as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['id']) ?></td>
                <td><?= htmlspecialchars($p['vorname']) ?></td>
                <td><?= htmlspecialchars($p['nachname']) ?></td>
                <td><?= htmlspecialchars($p['dashboard_id']) ?></td>
                <td>
                    <a href="?edit=<?= $p['id'] ?>" class="action-btn">Bearbeiten</a>
                    <a href="?delete=<?= $p['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

</body>
</html>
