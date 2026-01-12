<?php
/**
 * Dokumentation page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;
use App\Models\Einsatz;
use App\Models\Dokumentation;
use App\Models\Personal;
use App\Models\Drohne;

AuthService::requireAuth();

$config = include dirname(__DIR__, 2) . '/config/config.php';
$dashboardEnabled = !empty($config['path_to_dashboard_db']);

if (!isset($_GET['einsatz_id'])) {
    die("Keine Einsatz-ID angegeben.");
}
$einsatz_id = (int)$_GET['einsatz_id'];

// Handle form submissions via API
if (isset($_POST['einsatznummer_aktualisieren'])) {
    $einsatznummer = trim($_POST['einsatznummer'] ?? '');
    $einsatzModel = new Einsatz();
    $einsatzModel->update($einsatz_id, ['einsatznummer' => $einsatznummer]);
    header("Location: /public/index.php?page=dokumentation&einsatz_id=" . $einsatz_id);
    exit;
}

if (isset($_POST['eintrag_speichern']) && !empty($_POST['text'])) {
    $dokumentationModel = new Dokumentation();
    $dokumentationModel->addEntry($einsatz_id, trim($_POST['text']));
    header("Location: /public/index.php?page=dokumentation&einsatz_id=" . $einsatz_id);
    exit;
}

$einsatzModel = new Einsatz();
$einsatz = $einsatzModel->getWithPersonal($einsatz_id);
if (!$einsatz) {
    die("Einsatz nicht gefunden.");
}
$einsatz_abgeschlossen = !empty($einsatz['endzeit']);

$personal = $einsatzModel->getPersonnel($einsatz_id);

$drohnenModel = new Drohne();
$drohnen = $drohnenModel->getAllOrdered();

$dokumentationModel = new Dokumentation();
$eintraege = $dokumentationModel->getByEinsatzId($einsatz_id, 'DESC');

if (isset($_POST['personal_aktualisieren'])) {
    $personalIds = $_POST['personal'] ?? [];
    $einsatzModel->updatePersonnel($einsatz_id, $personalIds);
    header("Location: /public/index.php?page=dokumentation&einsatz_id=" . $einsatz_id);
    exit;
}

$personalModel = new Personal();
$personal_gesamt = $personalModel->getAllOrdered();

$personal_anwesend_ids = array_column($personal, 'id');
$anwesendMap = array_flip($personal_anwesend_ids);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatz-Dokumentation</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(getCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body data-einsatz-id="<?= htmlspecialchars($einsatz_id) ?>" data-dashboard-enabled="<?= $dashboardEnabled ? '1' : '0' ?>">
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<h2>Einsatz-Dokumentation (#<?= htmlspecialchars($einsatz['einsatznummer']) ?>)</h2>

<?php if (!$einsatz_abgeschlossen): ?>

    <div class="accordion-tabs-container">
        <button class="accordion">Einsatznummer aktualisieren</button>
        <button class="accordion">Anwesendes Personal aktualisieren</button>
    </div>
    
    <div class="panel">
        <form method="post">
            <input type="text" name="einsatznummer" value="<?= htmlspecialchars($einsatz['einsatznummer']) ?>" required>
            <button type="submit" name="einsatznummer_aktualisieren">Speichern</button>
        </form>
    </div>

    <div class="panel">
        <form method="post">
            <?php 
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
        <img src="/img/flugzeug_start.png" data-status="gelandet" onclick="toggleFlight(this)">
        <img src="/img/personensuche.png" onclick="insertQuickText(getDrohnentext(this, ' meldet Person gefunden.'))">
        <img src="/img/warnung.png" onclick="insertQuickText(getDrohnentext(this, ' meldet technische Störung.'))">

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
    <a class="abschluss-link" href="/api/v1/einsatz/<?= htmlspecialchars($einsatz_id) ?>/complete" onclick="return confirm('Einsatz wirklich abschließen?')">Einsatz abschließen</a>
    <a class="abschluss-link" href="/api/v1/einsatz/<?= htmlspecialchars($einsatz_id) ?>/pdf">PDF erstellen</a>
<?php else: ?>
    <a class="abschluss-link" href="/api/v1/einsatz/<?= htmlspecialchars($einsatz_id) ?>/pdf">PDF erneut herunterladen (Einsatz abgeschlossen)</a>
<?php endif; ?>

<br><br><br><br>
<a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

<script src="/js/dokumentation.js"></script>

</body>
</html>
