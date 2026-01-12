<?php
namespace App\Services;

use App\Models\Einsatz;
use Dompdf\Dompdf;

/**
 * PDF generation service
 */
class PDFService {
    /**
     * Generate PDF report for operation
     */
    public static function generateReport($einsatzId) {
        $einsatzModel = new Einsatz();
        $einsatz = $einsatzModel->getWithPersonal($einsatzId);
        
        if (!$einsatz) {
            throw new \Exception('Einsatz nicht gefunden');
        }
        
        $personal = $einsatzModel->getPersonnel($einsatzId);
        
        $dokumentationModel = new \App\Models\Dokumentation();
        $dokumentationEntries = $dokumentationModel->getByEinsatzId($einsatzId, 'ASC');
        
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $config = include $configPath;
        
        $html = self::buildHTML($einsatz, $personal, $dokumentationEntries, $config);
        
        require_once dirname(__DIR__, 2) . '/lib/dompdf/autoload.inc.php';
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf;
    }
    
    /**
     * Build HTML for PDF
     */
    private static function buildHTML($einsatz, $personal, $dokumentation, $config) {
        $html = "<h1>Einsatzbericht {$config['navigation_title']}</h1>";
        $html .= "<p><strong>Einsatznummer:</strong> {$einsatz['einsatznummer']}</p>";
        $html .= "<p><strong>Adresse:</strong> {$einsatz['adresse']}</p>";
        $html .= "<p><strong>GPS-Koordinaten:</strong> {$einsatz['gps_lat']}, {$einsatz['gps_lng']}</p>";
        $html .= "<p><strong>Art des Einsatzes:</strong> {$einsatz['einsatzart']}</p>";
        $html .= "<p><strong>Gruppenführer:</strong> {$einsatz['gruppenfuehrer']}</p>";
        $html .= "<p><strong>Dokumentierende Person:</strong> {$einsatz['dokumentierende']}</p>";
        $html .= "<p><strong>Beginn:</strong> {$einsatz['startzeit']}</p>";
        $html .= "<p><strong>Ende:</strong> {$einsatz['endzeit']}</p>";
        $html .= "<hr>";
        $html .= "<h2>Anwesendes Personal</h2>";
        $html .= "<table width='100%' border='1' cellpadding='5' cellspacing='0'><tr><th>Name</th></tr>";
        
        foreach ($personal as $p) {
            $html .= "<tr><td>{$p['vorname']} {$p['nachname']}</td></tr>";
        }
        
        $html .= "</table><hr><h2>Dokumentation</h2>";
        $html .= "<table width='100%' border='1' cellpadding='5' cellspacing='0'><tr><th>#</th><th>Zeitpunkt</th><th>Text</th></tr>";
        
        foreach ($dokumentation as $d) {
            $html .= "<tr><td>{$d['zeilennummer']}</td><td>{$d['zeitpunkt']}</td><td>{$d['text']}</td></tr>";
        }
        
        $html .= "</table>";
        
        return $html;
    }
}
