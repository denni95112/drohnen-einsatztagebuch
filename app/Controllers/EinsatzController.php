<?php
namespace App\Controllers;

use App\Models\Einsatz;
use App\Services\PDFService;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;

/**
 * Einsatz (Operation) controller
 */
class EinsatzController extends BaseController {
    /**
     * List all operations
     */
    public function index() {
        AuthMiddleware::handle(true); // Require admin
        
        $model = new Einsatz();
        $einsaetze = $model->getAllWithPersonal();
        
        $this->success($einsaetze);
    }
    
    /**
     * Get operation details
     */
    public function show($id) {
        AuthMiddleware::handle();
        
        $model = new Einsatz();
        $einsatz = $model->getWithPersonal($id);
        
        if (!$einsatz) {
            $this->error('Operation not found', 'NOT_FOUND', 404);
        }
        
        $einsatz['personal'] = $model->getPersonnel($id);
        
        $this->success($einsatz);
    }
    
    /**
     * Create new operation
     */
    public function create() {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        
        // Validation
        $errors = Validator::required($data, ['adresse', 'einsatzart', 'gruppenfuehrer_id', 'dokumentierende_id']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        if (!Validator::integer($data['gruppenfuehrer_id'], 1) || !Validator::integer($data['dokumentierende_id'], 1)) {
            $this->error('Invalid personnel IDs', 'VALIDATION_ERROR', 422);
        }
        
        if (isset($data['gps_lat']) && !Validator::gpsLatitude($data['gps_lat'])) {
            $this->error('Invalid GPS latitude', 'VALIDATION_ERROR', 422);
        }
        
        if (isset($data['gps_lng']) && !Validator::gpsLongitude($data['gps_lng'])) {
            $this->error('Invalid GPS longitude', 'VALIDATION_ERROR', 422);
        }
        
        $model = new Einsatz();
        
        $einsatzData = [
            'einsatznummer' => $data['einsatznummer'] ?? date('Ymd'),
            'adresse' => Validator::sanitizeString($data['adresse']),
            'gps_lat' => $data['gps_lat'] ?? '',
            'gps_lng' => $data['gps_lng'] ?? '',
            'einsatzart' => Validator::sanitizeString($data['einsatzart']),
            'gruppenfuehrer_id' => (int)$data['gruppenfuehrer_id'],
            'dokumentierende_id' => (int)$data['dokumentierende_id'],
            'startzeit' => date('Y-m-d H:i:s')
        ];
        
        $einsatzId = $model->create($einsatzData);
        
        // Add personnel if provided
        if (!empty($data['personal']) && is_array($data['personal'])) {
            $model->addPersonnel($einsatzId, $data['personal']);
        }
        
        $this->created(['id' => $einsatzId], 'Operation created successfully');
    }
    
    /**
     * Update operation
     */
    public function update($id) {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'PUT' && $this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Einsatz();
        $einsatz = $model->find($id);
        
        if (!$einsatz) {
            $this->error('Operation not found', 'NOT_FOUND', 404);
        }
        
        $data = $this->getRequestData();
        
        $updateData = [];
        if (isset($data['einsatznummer'])) {
            $updateData['einsatznummer'] = Validator::sanitizeString($data['einsatznummer']);
        }
        if (isset($data['adresse'])) {
            $updateData['adresse'] = Validator::sanitizeString($data['adresse']);
        }
        
        if (!empty($updateData)) {
            $model->update($id, $updateData);
        }
        
        // Update personnel if provided
        if (isset($data['personal']) && is_array($data['personal'])) {
            $model->updatePersonnel($id, $data['personal']);
        }
        
        $this->success(['id' => $id], 'Operation updated successfully');
    }
    
    /**
     * Complete operation
     */
    public function complete($id) {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Einsatz();
        $einsatz = $model->find($id);
        
        if (!$einsatz) {
            $this->error('Operation not found', 'NOT_FOUND', 404);
        }
        
        $model->complete($id);
        
        $this->success(['id' => $id], 'Operation completed successfully');
    }
    
    /**
     * Generate PDF
     */
    public function pdf($id) {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'GET') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        try {
            $dompdf = PDFService::generateReport($id);
            $dompdf->stream("einsatzbericht_" . $id . ".pdf", ["Attachment" => true]);
            exit;
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 'PDF_GENERATION_ERROR', 500);
        }
    }
}
