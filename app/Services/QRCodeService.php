<?php
namespace App\Services;

/**
 * QR Code generation service
 */
class QRCodeService {
    /**
     * Generate QR code image
     */
    public static function generate($data) {
        $qrlibPath = dirname(__DIR__, 2) . '/lib/phpqrcode/qrlib.php';
        
        if (!file_exists($qrlibPath)) {
            throw new \Exception('phpqrcode library not found');
        }
        
        require_once $qrlibPath;
        
        // Output QR code directly (use global namespace)
        \QRcode::png($data, false, \QR_ECLEVEL_L, 10);
    }
    
    /**
     * Generate QR code URL for read-only mode
     */
    public static function generateReadOnlyUrl($einsatzId) {
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $config = include $configPath;
        
        $readOnlyUrl = $config['domain'] . "/read_only.php?einsatz_id=" . $einsatzId . "&token=" . $config['read_token'];
        return $readOnlyUrl;
    }
}
