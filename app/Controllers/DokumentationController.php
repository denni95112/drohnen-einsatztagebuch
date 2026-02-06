<?php
namespace App\Controllers;

use App\Models\Dokumentation;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;

/**
 * Documentation controller
 */
class DokumentationController extends BaseController {
    /**
     * Get documentation entries for operation
     */
    public function getByEinsatz($einsatzId) {
        // Read-only access, no auth required if token is valid
        // This will be handled by the API endpoint
        
        $model = new Dokumentation();
        $entries = $model->getByEinsatzId($einsatzId, 'DESC');
        
        $this->success($entries);
    }
    
    /**
     * Add documentation entry
     */
    public function create() {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        $einsatzId = $data['einsatz_id'] ?? null;
        $text = isset($data['text']) ? (string) $data['text'] : '';
        
        if (!$einsatzId) {
            $this->error('einsatz_id is required', 'VALIDATION_ERROR', 400);
        }
        
        if (trim($text) === '') {
            $this->error('Text cannot be empty', 'VALIDATION_ERROR', 400);
        }
        
        $text = Validator::sanitizeString($text);
        
        $model = new Dokumentation();
        $entry = $model->addEntry($einsatzId, $text);
        
        $this->created($entry, 'Documentation entry added successfully');
    }
}
