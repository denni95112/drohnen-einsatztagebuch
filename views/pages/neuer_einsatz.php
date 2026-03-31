<?php
/**
 * Neuer Einsatz page view
 */
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Services\AuthService;
use App\Services\DashboardApiService;
use App\Models\Personal;
use App\Utils\Validator;

AuthService::requireAuth();

$config = include dirname(__DIR__, 2) . '/config/config.php';
$dashboardApiManaged = DashboardApiService::isApiEnabled();

if (isset($_POST['einsatz_starten'])) {
    if ($dashboardApiManaged) {
        DashboardApiService::syncPilotsToLocalPersonal(new Personal());
    }
    $einsatznummer = !empty($_POST['einsatznummer']) ? trim($_POST['einsatznummer']) : date('Ymd');
    $adresse = trim($_POST['adresse'] ?? '');
    $gps_lat = str_replace(',', '.', trim($_POST['gps_lat'] ?? ''));
    $gps_lng = str_replace(',', '.', trim($_POST['gps_lng'] ?? ''));
    $einsatzart = trim($_POST['einsatzart'] ?? '');
    $gruppenfuehrer_id = (int)($_POST['gruppenfuehrer_id'] ?? 0);
    $dokumentierende_id = (int)($_POST['dokumentierende_id'] ?? 0);

    if (empty($adresse)) {
        die("Fehler: Adresse ist erforderlich.");
    }
    if (empty($einsatzart)) {
        die("Fehler: Einsatzart ist erforderlich.");
    }
    if ($gruppenfuehrer_id <= 0 || $dokumentierende_id <= 0) {
        die("Fehler: Gruppenführer und dokumentierende Person müssen ausgewählt werden.");
    }
    
    if (!empty($gps_lat) && !Validator::gpsLatitude($gps_lat)) {
        die("Fehler: Ungültige GPS-Breitengrad.");
    }
    if (!empty($gps_lng) && !Validator::gpsLongitude($gps_lng)) {
        die("Fehler: Ungültige GPS-Längengrad.");
    }

    $createFlugstandort = $dashboardApiManaged && !empty($_POST['create_flugstandort']);
    if ($createFlugstandort) {
        if ($gps_lat === '' || $gps_lng === '' || !Validator::gpsLatitude($gps_lat) || !Validator::gpsLongitude($gps_lng)) {
            die("Fehler: Für „Als Flugstandort anlegen“ sind gültige Koordinaten erforderlich (GPS oder Karte).");
        }
    }

    $einsatzModel = new \App\Models\Einsatz();
    
    $einsatzData = [
        'einsatznummer' => $einsatznummer,
        'adresse' => Validator::sanitizeString($adresse),
        'gps_lat' => $gps_lat,
        'gps_lng' => $gps_lng,
        'einsatzart' => Validator::sanitizeString($einsatzart),
        'gruppenfuehrer_id' => $gruppenfuehrer_id,
        'dokumentierende_id' => $dokumentierende_id,
        'startzeit' => date('Y-m-d H:i:s')
    ];
    
    $einsatz_id = $einsatzModel->create($einsatzData);

    if (!empty($_POST['personal']) && is_array($_POST['personal'])) {
        $einsatzModel->addPersonnel($einsatz_id, $_POST['personal']);
    }

    $standortApi = '';
    if ($createFlugstandort) {
        $lat = (float) $gps_lat;
        $lng = (float) $gps_lng;
        $desc = 'Einsatz ' . $einsatznummer . ' (Einsatzdoku #' . $einsatz_id . ')';
        $apiResult = DashboardApiService::createLocation(
            $adresse,
            $lat,
            $lng,
            $desc,
            false
        );
        $standortApi = $apiResult['success'] ? '1' : '0';
    }

    $redir = '/public/index.php?page=dokumentation&einsatz_id=' . $einsatz_id;
    if ($standortApi !== '') {
        $redir .= '&standort_api=' . $standortApi;
    }
    header('Location: ' . $redir);
    exit;
}

$personalModel = new Personal();
if ($dashboardApiManaged) {
    DashboardApiService::syncPilotsToLocalPersonal($personalModel);
}
$personal = $personalModel->getAllOrdered();
$einsatzarten = ["Brandeinsatz", "Ölspur", "Öl auf Gewässer", "Personensuche", "Erkundung", "sonst. TH", "Übung"];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuer Einsatz starten</title>
    <link rel="stylesheet" href="<?= getVersionedAsset('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= getVersionedAsset('vendor/leaflet/leaflet.css') ?>">
    <link rel="stylesheet" href="<?= getVersionedAsset('css/neuer_einsatz.css') ?>">
    <script src="<?= getVersionedAsset('vendor/leaflet/leaflet.js') ?>"></script>
    <script>
        window.neuerEinsatzConfig = {
            dashboardApi: <?= $dashboardApiManaged ? 'true' : 'false' ?>
        };
    </script>
    <script src="<?= getVersionedAsset('js/neuer_einsatz.js') ?>" defer></script>
</head>
<body>
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<h2>Neuen Einsatz starten</h2>
<form method="post" action="/public/index.php?page=neuer_einsatz">
    <label>Einsatznummer:
        <input type="text" name="einsatznummer" placeholder="(optional, sonst automatisch)">
    </label>

    <label>Adresse des Einsatzortes:
        <input type="text" name="adresse" id="adresse" required>
        <button type="button" class="gps-btn" id="gps-btn" onclick="getAddress()">
            <span id="gps-btn-text">Adresse per GPS ermitteln</span>
            <span id="gps-spinner" class="gps-spinner" style="display: none;"></span>
        </button>
        <button type="button" class="gps-btn map-picker-btn" id="map-picker-open">
            Position auf Karte wählen
        </button>
    </label>

    <input type="hidden" id="gps_lat" name="gps_lat">
    <input type="hidden" id="gps_lng" name="gps_lng">

    <?php if ($dashboardApiManaged): ?>
    <label class="checkbox-flugstandort">
        <input type="checkbox" name="create_flugstandort" id="create_flugstandort" value="1">
        Als Flugstandort im Flug-Dienstbuch anlegen (Koordinaten erforderlich)
    </label>
    <?php endif; ?>

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

    <div style="margin-top: 0.5rem;">
        <label style="margin-bottom: 1rem; display: block;">Anwesendes Personal:</label>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <?php foreach ($personal as $p): ?>
                <label style="margin-bottom: 0;">
                    <input type="checkbox" name="personal[]" value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['vorname'] . ' ' . $p['nachname']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="centered-btn">
        <button type="submit" name="einsatz_starten">Einsatz starten</button>
    </div>
</form>
<br><br>
<a href="/public/index.php?page=index" class="back-btn">Zurück zur Übersicht</a>

<div id="map-picker-modal" class="map-modal" role="dialog" aria-modal="true" aria-labelledby="map-modal-title" hidden>
    <div class="map-modal-backdrop" id="map-modal-backdrop"></div>
    <div class="map-modal-panel">
        <div class="map-modal-header">
            <h3 id="map-modal-title">Position auf der Karte</h3>
            <button type="button" class="map-modal-close" id="map-modal-close" aria-label="Schließen">&times;</button>
        </div>
        <p class="map-modal-hint">Ort oder Adresse suchen, oder auf die Karte tippen bzw. den Marker ziehen.</p>
        <div class="map-search-row">
            <label class="map-search-label" for="map-search-q">Suche</label>
            <div class="map-search-controls">
                <input type="search" id="map-search-q" class="map-search-input" placeholder="Stadt, PLZ oder Adresse…" autocomplete="off" maxlength="200">
                <button type="button" class="gps-btn" id="map-search-btn">Suchen</button>
            </div>
            <p id="map-search-msg" class="map-search-msg" hidden></p>
            <ul id="map-search-results" class="map-search-results" hidden role="listbox" aria-label="Suchergebnisse"></ul>
        </div>
        <div id="map-picker-container" class="map-picker-container"></div>
        <div class="map-modal-actions">
            <button type="button" class="gps-btn" id="map-modal-apply">Übernehmen</button>
            <button type="button" class="map-modal-cancel-btn" id="map-modal-cancel">Abbrechen</button>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

</body>
</html>
