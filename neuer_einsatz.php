<?php
require 'db.php';
require 'auth.php';
requireAuth();


// Einsatz starten
if (isset($_POST['einsatz_starten'])) {
    // Input validation
    $einsatznummer = !empty($_POST['einsatznummer']) ? trim($_POST['einsatznummer']) : date('Ymd');
    $adresse = trim($_POST['adresse'] ?? '');
    $gps_lat = trim($_POST['gps_lat'] ?? '');
    $gps_lng = trim($_POST['gps_lng'] ?? '');
    $einsatzart = trim($_POST['einsatzart'] ?? '');
    $gruppenfuehrer_id = (int)($_POST['gruppenfuehrer_id'] ?? 0);
    $dokumentierende_id = (int)($_POST['dokumentierende_id'] ?? 0);

    // Validate required fields
    if (empty($adresse)) {
        die("Fehler: Adresse ist erforderlich.");
    }
    if (empty($einsatzart)) {
        die("Fehler: Einsatzart ist erforderlich.");
    }
    if ($gruppenfuehrer_id <= 0 || $dokumentierende_id <= 0) {
        die("Fehler: Gruppenführer und dokumentierende Person müssen ausgewählt werden.");
    }
    
    // Validate GPS coordinates if provided
    if (!empty($gps_lat) && (!is_numeric($gps_lat) || $gps_lat < -90 || $gps_lat > 90)) {
        die("Fehler: Ungültige GPS-Breitengrad.");
    }
    if (!empty($gps_lng) && (!is_numeric($gps_lng) || $gps_lng < -180 || $gps_lng > 180)) {
        die("Fehler: Ungültige GPS-Längengrad.");
    }

    $stmt = $db->prepare("INSERT INTO einsatz (einsatznummer, adresse, gps_lat, gps_lng, einsatzart, gruppenfuehrer_id, dokumentierende_id, startzeit) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
    $stmt->execute([
        $einsatznummer,
        $adresse,
        $gps_lat,
        $gps_lng,
        $einsatzart,
        $gruppenfuehrer_id,
        $dokumentierende_id
    ]);

    $einsatz_id = $db->lastInsertId();

    // Batch insert for personnel
    if (!empty($_POST['personal']) && is_array($_POST['personal'])) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO einsatz_personal (einsatz_id, personal_id) VALUES (?, ?)");
            foreach ($_POST['personal'] as $personal_id) {
                $stmt->execute([$einsatz_id, (int)$personal_id]);
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

$personal = $db->query("SELECT id, vorname, nachname, dashboard_id FROM personal ORDER BY nachname, vorname")->fetchAll(PDO::FETCH_ASSOC);
$einsatzarten = ["Brandeinsatz", "Ölspur", "Öl auf Gewässer", "Personensuche", "Erkundung", "sonst. TH", "Übung"];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuer Einsatz starten</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/neuer_einsatz.js"></script>
</head>
<body>
<h2>Neuen Einsatz starten</h2>
<form method="post">
    <label>Einsatznummer:
        <input type="text" name="einsatznummer" placeholder="(optional, sonst automatisch)">
    </label>

    <label>Adresse des Einsatzortes:
        <input type="text" name="adresse" id="adresse" required>
        <button type="button" class="gps-btn" onclick="getAddress()">Adresse per GPS ermitteln</button>
    </label>

    <input type="hidden" id="gps_lat" name="gps_lat">
    <input type="hidden" id="gps_lng" name="gps_lng">

    <label>Art des Einsatzes:
        <select name="einsatzart" required>
            <option value="">Bitte wählen oder eingeben...</option>
            <?php foreach ($einsatzarten as $art): 
                $artEscaped = htmlspecialchars($art); ?>
                <option value="<?= $artEscaped ?>"><?= $artEscaped ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Gruppenführer:
        <select name="gruppenfuehrer_id" required>
            <?php foreach ($personal as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['vorname'] . ' ' . $p['nachname']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Dokumentierende Person:
        <select name="dokumentierende_id" required>
            <?php foreach ($personal as $p): ?>
                <option value="<?= $p['id'] ?>"> <?= htmlspecialchars($p['vorname'] . ' ' . $p['nachname']) ?> </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Anwesendes Personal:<br><br>
        <?php foreach ($personal as $p): ?>
            <input type="checkbox" name="personal[]" value="<?= $p['id'] ?>">
            <?= htmlspecialchars($p['vorname'] . ' ' . $p['nachname']) ?><br><br>
        <?php endforeach; ?>
    </label>

    <div class="centered-btn">
        <button type="submit" name="einsatz_starten">Einsatz starten</button>
    </div>
</form>
<br><br>
<a href="index.php" class="back-btn">Zurück zur Übersicht</a>
</body>
</html>