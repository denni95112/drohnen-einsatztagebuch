<?php
namespace App\Controllers;

use App\Models\Personal;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;

/**
 * Personal (Personnel) controller
 */
class PersonalController extends BaseController {
    /**
     * List all personnel
     */
    public function index() {
        AuthMiddleware::handle(true); // Require admin
        
        $model = new Personal();
        $personal = $model->getAllOrdered();
        
        $this->success($personal);
    }
    
    /**
     * Get personnel details
     */
    public function show($id) {
        AuthMiddleware::handle(true);
        
        $model = new Personal();
        $person = $model->find($id);
        
        if (!$person) {
            $this->error('Personnel not found', 'NOT_FOUND', 404);
        }
        
        $this->success($person);
    }
    
    /**
     * Create personnel
     */
    public function create() {
        AuthMiddleware::handle(true);
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        
        $errors = Validator::required($data, ['vorname', 'nachname']);
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        $model = new Personal();
        
        $personData = [
            'vorname' => Validator::sanitizeString($data['vorname']),
            'nachname' => Validator::sanitizeString($data['nachname']),
            'dashboard_id' => !empty($data['dashboard_id']) ? (int)$data['dashboard_id'] : null
        ];
        
        $id = $model->create($personData);
        
        $this->created(['id' => $id], 'Personnel created successfully');
    }
    
    /**
     * Update personnel
     */
    public function update($id) {
        AuthMiddleware::handle(true);
        
        if ($this->getMethod() !== 'PUT' && $this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Personal();
        $person = $model->find($id);
        
        if (!$person) {
            $this->error('Personnel not found', 'NOT_FOUND', 404);
        }
        
        $data = $this->getRequestData();
        
        $errors = Validator::required($data, ['vorname', 'nachname']);
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        $updateData = [
            'vorname' => Validator::sanitizeString($data['vorname']),
            'nachname' => Validator::sanitizeString($data['nachname']),
            'dashboard_id' => !empty($data['dashboard_id']) ? (int)$data['dashboard_id'] : null
        ];
        
        $model->update($id, $updateData);
        
        $this->success(['id' => $id], 'Personnel updated successfully');
    }
    
    /**
     * Delete personnel
     */
    public function delete($id) {
        AuthMiddleware::handle(true);
        
        if ($this->getMethod() !== 'DELETE' && $this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Personal();
        $person = $model->find($id);
        
        if (!$person) {
            $this->error('Personnel not found', 'NOT_FOUND', 404);
        }
        
        $model->delete($id);
        
        $this->success(['id' => $id], 'Personnel deleted successfully');
    }
}
