<?php
namespace App\Services;

use App\Models\Einsatz;

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
        
        if (!function_exists('mb_internal_encoding')) {
            throw new \Exception('Die PHP-Erweiterung "mbstring" ist erforderlich für die PDF-Erstellung. Bitte mbstring aktivieren (z. B. unter Linux: php-mbstring installieren).');
        }
        if (!class_exists('DOMImplementation')) {
            throw new \Exception('Die PHP-Erweiterung "dom" (DOM/XML) ist erforderlich für die PDF-Erstellung. Bitte die dom-Erweiterung aktivieren (z. B. unter Linux: php-xml installieren).');
        }
        
        $dompdfAutoload = dirname(__DIR__, 2) . '/lib/dompdf/autoload.inc.php';
        if (!is_file($dompdfAutoload)) {
            throw new \Exception('PDF-Bibliothek (dompdf) ist nicht installiert. Bitte in der Administration unter "Bibliotheken" installieren.');
        }
        require_once $dompdfAutoload;
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf;
    }
    
    /**
     * Build HTML for PDF
     */
    private static function buildHTML($einsatz, $personal, $dokumentation, $config) {
        $e = function ($key) use ($einsatz) {
            return htmlspecialchars((string)($einsatz[$key] ?? ''), ENT_QUOTES, 'UTF-8');
        };
        $navTitle = htmlspecialchars((string)($config['navigation_title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $html = '<h1>Einsatzbericht ' . $navTitle . '</h1>';
        $html .= '<p><strong>Einsatznummer:</strong> ' . $e('einsatznummer') . '</p>';
        $html .= '<p><strong>Adresse:</strong> ' . $e('adresse') . '</p>';
        $html .= '<p><strong>GPS-Koordinaten:</strong> ' . $e('gps_lat') . ', ' . $e('gps_lng') . '</p>';
        $html .= '<p><strong>Art des Einsatzes:</strong> ' . $e('einsatzart') . '</p>';
        $html .= '<p><strong>Gruppenführer:</strong> ' . $e('gruppenfuehrer') . '</p>';
        $html .= '<p><strong>Dokumentierende Person:</strong> ' . $e('dokumentierende') . '</p>';
        $html .= '<p><strong>Beginn:</strong> ' . $e('startzeit') . '</p>';
        $html .= '<p><strong>Ende:</strong> ' . $e('endzeit') . '</p>';
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
