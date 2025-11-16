<?php
require_once 'db.php';
require_once 'lib/dompdf/autoload.inc.php';
$config = include __DIR__ . '/config/config.php';

use Dompdf\Dompdf;

if (!isset($_GET['einsatz_id'])) {
    die("Keine Einsatz-ID angegeben.");
}

$einsatz_id = (int)$_GET['einsatz_id'];

$stmt = $db->prepare("SELECT COUNT(*) FROM einsatz_dokumentation WHERE einsatz_id = ? AND text = ?");
$stmt->execute([$einsatz_id, 'Ende der Dokumentation']);

if ($stmt->fetchColumn() == 0) {
    $stmt = $db->prepare("SELECT MAX(zeilennummer) FROM einsatz_dokumentation WHERE einsatz_id = ?");
    $stmt->execute([$einsatz_id]);
    $zeilennummer = (int)$stmt->fetchColumn() + 1;

    $stmt = $db->prepare("INSERT INTO einsatz_dokumentation (einsatz_id, zeilennummer, zeitpunkt, text) VALUES (?, ?, datetime('now', 'localtime'), ?)");
    $stmt->execute([$einsatz_id, $zeilennummer, 'Ende der Dokumentation']);
}

$stmt = $db->prepare("SELECT endzeit FROM einsatz WHERE id = ?");
$stmt->execute([$einsatz_id]);
$currentEnd = $stmt->fetchColumn();

if (!$currentEnd) {
    $stmt = $db->prepare("UPDATE einsatz SET endzeit = datetime('now', 'localtime') WHERE id = ?");
    $stmt->execute([$einsatz_id]);
}

$stmt = $db->prepare("SELECT e.id, e.einsatznummer, e.adresse, e.gps_lat, e.gps_lng, e.einsatzart, e.gruppenfuehrer_id, e.dokumentierende_id, e.startzeit, e.endzeit,
       p1.vorname || ' ' || p1.nachname AS gruppenfuehrer,
       p2.vorname || ' ' || p2.nachname AS dokumentierende
FROM einsatz e
LEFT JOIN personal p1 ON e.gruppenfuehrer_id = p1.id
LEFT JOIN personal p2 ON e.dokumentierende_id = p2.id
WHERE e.id = ?");
$stmt->execute([$einsatz_id]);
$einsatz = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT p.vorname, p.nachname FROM personal p 
                      INNER JOIN einsatz_personal ep ON p.id = ep.personal_id 
                      WHERE ep.einsatz_id = ?");
$stmt->execute([$einsatz_id]);
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT id, einsatz_id, zeilennummer, zeitpunkt, text FROM einsatz_dokumentation WHERE einsatz_id = ? ORDER BY zeilennummer ASC");
$stmt->execute([$einsatz_id]);
$dokumentation = $stmt->fetchAll(PDO::FETCH_ASSOC);

$html = "
<h1>Einsatzbericht {$config['navigation_title']}</h1>
<p><strong>Einsatznummer:</strong> {$einsatz['einsatznummer']}</p>
<p><strong>Adresse:</strong> {$einsatz['adresse']}</p>
<p><strong>GPS-Koordinaten:</strong> {$einsatz['gps_lat']}, {$einsatz['gps_lng']}</p>
<p><strong>Art des Einsatzes:</strong> {$einsatz['einsatzart']}</p>
<p><strong>Gruppenführer:</strong> {$einsatz['gruppenfuehrer']}</p>
<p><strong>Dokumentierende Person:</strong> {$einsatz['dokumentierende']}</p>
<p><strong>Beginn:</strong> {$einsatz['startzeit']}</p>
<p><strong>Ende:</strong> {$einsatz['endzeit']}</p>
<hr>
<h2>Anwesendes Personal</h2>
<table width='100%' border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>Name</th>
</tr>";

$personalRows = [];
foreach ($personal as $p) {
    $personalRows[] = "<tr><td>{$p['vorname']} {$p['nachname']}</td></tr>";
}
$html .= implode('', $personalRows);

$html .= "</table>
<hr>
<h2>Dokumentation</h2>
<table width='100%' border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>#</th>
    <th>Zeitpunkt</th>
    <th>Text</th>
</tr>";

$dokumentationRows = [];
foreach ($dokumentation as $d) {
    $dokumentationRows[] = "<tr><td>{$d['zeilennummer']}</td><td>{$d['zeitpunkt']}</td><td>{$d['text']}</td></tr>";
}
$html .= implode('', $dokumentationRows);

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("einsatzbericht_" . $einsatz['einsatznummer'] . ".pdf", ["Attachment" => true]);

exit;
