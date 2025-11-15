<?php
require_once 'db.php';
require 'auth.php';
requireAuth();

// Einsatz-ID aus URL
if (!isset($_GET['einsatz_id'])) {
    die("Keine Einsatz-ID angegeben.");
}
$einsatz_id = (int)$_GET['einsatz_id'];

// Einsatznummer aktualisieren
if (isset($_POST['einsatznummer_aktualisieren'])) {
    $einsatznummer = trim($_POST['einsatznummer'] ?? '');
    $stmt = $db->prepare("UPDATE einsatz SET einsatznummer = ? WHERE id = ?");
    $stmt->execute([$einsatznummer, $einsatz_id]);
    header("Location: dokumentation.php?einsatz_id=" . $einsatz_id);
    exit;
}

// Neuen Eintrag speichern
if (isset($_POST['eintrag_speichern']) && !empty($_POST['text'])) {
    $stmt = $db->prepare("SELECT MAX(zeilennummer) FROM einsatz_dokumentation WHERE einsatz_id = ?");
    $stmt->execute([$einsatz_id]);
    $zeilennummer = (int)$stmt->fetchColumn() + 1;

    $stmt = $db->prepare("INSERT INTO einsatz_dokumentation (einsatz_id, zeilennummer, zeitpunkt, text) VALUES (?, ?, datetime('now', 'localtime'), ?)");
    $stmt->execute([$einsatz_id, $zeilennummer, $_POST['text']]);

    header("Location: dokumentation.php?einsatz_id=" . $einsatz_id);
    exit;
}

// Einsatzdaten und Einträge abrufen
$stmt = $db->prepare("SELECT id, einsatznummer, adresse, gps_lat, gps_lng, einsatzart, gruppenfuehrer_id, dokumentierende_id, startzeit, endzeit FROM einsatz WHERE id = ?");
$stmt->execute([$einsatz_id]);
$einsatz = $stmt->fetch(PDO::FETCH_ASSOC);
$einsatz_abgeschlossen = !empty($einsatz['endzeit']);


// Personal abrufen
$stmt = $db->prepare("SELECT p.id, p.vorname, p.nachname, p.dashboard_id FROM personal p INNER JOIN einsatz_personal ep ON p.id = ep.personal_id WHERE ep.einsatz_id = ?");
$stmt->execute([$einsatz_id]);
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Drohnen abrufen
$drohnen = $db->query("SELECT id, name FROM drohnen")->fetchAll(PDO::FETCH_ASSOC);

// Dokumentation abrufen
$stmt = $db->prepare("SELECT id, einsatz_id, zeilennummer, zeitpunkt, text FROM einsatz_dokumentation WHERE einsatz_id = ? ORDER BY zeilennummer DESC");
$stmt->execute([$einsatz_id]);
$eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Personal aktualisieren
if (isset($_POST['personal_aktualisieren'])) {
    $stmt = $db->prepare("DELETE FROM einsatz_personal WHERE einsatz_id = ?");
    $stmt->execute([$einsatz_id]);
    if(isset($_POST['personal']) && !empty($_POST['personal'])){
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO einsatz_personal (einsatz_id, personal_id) VALUES (?, ?)");
            foreach ($_POST['personal'] as $pid) {
                $stmt->execute([$einsatz_id, (int)$pid]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            die("Fehler beim Speichern des Personals: " . $e->getMessage());
        }
    }
    header("Location: dokumentation.php?einsatz_id=" . $einsatz_id);
    exit;
}

// Personal gesamt abrufen
$personal_gesamt = $db->query("SELECT id, vorname, nachname, dashboard_id FROM personal ORDER BY nachname, vorname")->fetchAll(PDO::FETCH_ASSOC);

// IDs anwesenden Personals abrufen - use array_flip for O(1) lookup
$stmt = $db->prepare("SELECT personal_id FROM einsatz_personal WHERE einsatz_id = ?");
$stmt->execute([$einsatz_id]);
$personal_anwesend_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
$anwesendMap = array_flip($personal_anwesend_ids);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatz-Dokumentation</title>
    <link rel="stylesheet" href="css/styles.css">

</head>
<body data-einsatz-id="<?= htmlspecialchars($einsatz_id) ?>">
<h2>Einsatz-Dokumentation (#<?= htmlspecialchars($einsatz['einsatznummer']) ?>)</h2>

<?php if (!$einsatz_abgeschlossen): ?>

    <button class="accordion">Einsatznummer aktualisieren</button>
    <div class="panel">
        <form method="post">
            <input type="text" name="einsatznummer" value="<?= htmlspecialchars($einsatz['einsatznummer']) ?>" required>
            <button type="submit" name="einsatznummer_aktualisieren">Speichern</button>
        </form>
    </div>

    <button class="accordion">Anwesendes Personal aktualisieren</button>
    <div class="panel">
        <form method="post">
            <?php 
            // Reuse $personal_gesamt from line 75, use optimized array_flip for O(1) lookup
            foreach($personal_gesamt as $p): ?>
                <label>
                    <input type="checkbox" name="personal[]" value="<?= $p['id'] ?>"
                    <?= isset($anwesendMap[$p['id']]) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($p['vorname'].' '.$p['nachname']) ?>
                </label>
            <?php endforeach; ?>
            <button type="submit" name="personal_aktualisieren">Personal speichern</button>
        </form>
    </div>


    <h3>Quick Actions</h3>
    <?php 
    // Cache personal options HTML to avoid repeated iterations
    $personalOptions = '';
    foreach ($personal as $p) {
        $name = htmlspecialchars($p['vorname'] . ' ' . $p['nachname']);
        $personalOptions .= "<option value=\"{$name}\">{$name}</option>\n            ";
    }
    ?>
    <?php foreach ($drohnen as $drohne): ?>
        <div class="quick-action" id="drohne-<?= $drohne['id'] ?>" data-drohne-id="<?= $drohne['id'] ?>" data-drohne-name="<?= htmlspecialchars($drohne['name']) ?>">
        <strong><?= htmlspecialchars($drohne['name']) ?></strong><br>

        Pilot:<br>
        <select class="pilot" onchange="saveQuickData(this)">
            <?= $personalOptions ?>
        </select><br>

        Co-Pilot:<br>
        <select class="copilot" onchange="saveQuickData(this)">
            <?= $personalOptions ?>
        </select><br>

        Akku:<br>
        <input type="text" class="akku" placeholder="Akku-Nummer" oninput="saveQuickData(this)"><br>
        <br>   
        <img src="./img/flugzeug_start.png" data-status="gelandet" onclick="toggleFlight(this)">
        <img src="./img/personensuche.png" onclick="insertQuickText(getDrohnentext(this, ' meldet Person gefunden.'))">
        <img src="./img/warnung.png" onclick="insertQuickText(getDrohnentext(this, ' meldet technische Störung.'))">

        <div class="flugdauer">Flugdauer: 00:00</div>
    </div>

    <?php endforeach; ?>


    <br><br>

    <form id="newEntryForm">
        <textarea name="text" id="textEntry" required placeholder="Neuer Eintrag"></textarea>
        <button type="submit">Eintrag hinzufügen</button>
    </form>

    <br><br>
<?php endif; ?>
<table id="eintraegeTabelle">
    <thead>
        <tr>
            <th>#</th>
            <th onclick="sortTable(1)">Zeitpunkt 🔽</th>
            <th>Text</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($eintraege as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['zeilennummer']) ?></td>
            <td><?= htmlspecialchars($e['zeitpunkt']) ?></td>
            <td><?= htmlspecialchars($e['text']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php if (!$einsatz_abgeschlossen): ?>
    <a class="abschluss-link" href="einsatz_abschluss.php?einsatz_id=<?= htmlspecialchars($einsatz_id) ?>" onclick="return confirm('Einsatz wirklich abschließen?')">Einsatz abschließen & PDF erstellen</a>
<?php else: ?>
    <a class="abschluss-link" href="einsatz_abschluss.php?einsatz_id=<?= htmlspecialchars($einsatz_id) ?>">PDF erneut herunterladen (Einsatz abgeschlossen)</a>
<?php endif; ?>

<br><br><br><br>
<a href="index.php" class="back-btn">Zurück zur Übersicht</a>

<script src="js/dokumentation.js"></script>

</body>
</html>
