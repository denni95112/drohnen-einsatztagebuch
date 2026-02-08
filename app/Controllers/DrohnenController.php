<?php
namespace App\Controllers;

use App\Models\Drohne;
use App\Services\DashboardApiService;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;

/**
 * Drohnen (Drones) controller
 */
class DrohnenController extends BaseController {
    /**
     * List all drones
     */
    public function index() {
        AuthMiddleware::handle(true);
        
        if (DashboardApiService::isApiEnabled()) {
            $drohnen = DashboardApiService::getDrones();
            $this->success($drohnen);
            return;
        }
        
        $model = new Drohne();
        $drohnen = $model->getAllOrdered();
        
        $this->success($drohnen);
    }
    
    /**
     * Get drone details
     */
    public function show($id) {
        AuthMiddleware::handle(true);
        
        $model = new Drohne();
        $drohne = $model->find($id);
        
        if (!$drohne) {
            $this->error('Drone not found', 'NOT_FOUND', 404);
        }
        
        $this->success($drohne);
    }
    
    /**
     * Create drone
     */
    public function create() {
        AuthMiddleware::handle(true);
        
        if (DashboardApiService::isApiEnabled()) {
            $this->error('Drohnen werden über das Flug-Dienstbuch verwaltet.', 'API_MANAGED', 403);
            return;
        }
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        
        $errors = Validator::required($data, ['name']);
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        $model = new Drohne();
        
        $drohneData = [
            'name' => Validator::sanitizeString($data['name'])
        ];
        
        $id = $model->create($drohneData);
        
        $this->created(['id' => $id], 'Drone created successfully');
    }
    
    /**
     * Update drone
     */
    public function update($id) {
        AuthMiddleware::handle(true);
        
        if (DashboardApiService::isApiEnabled()) {
            $this->error('Drohnen werden über das Flug-Dienstbuch verwaltet.', 'API_MANAGED', 403);
            return;
        }
        
        if ($this->getMethod() !== 'PUT' && $this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Drohne();
        $drohne = $model->find($id);
        
        if (!$drohne) {
            $this->error('Drone not found', 'NOT_FOUND', 404);
        }
        
        $data = $this->getRequestData();
        
        $errors = Validator::required($data, ['name']);
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        $updateData = [
            'name' => Validator::sanitizeString($data['name'])
        ];
        
        $model->update($id, $updateData);
        
        $this->success(['id' => $id], 'Drone updated successfully');
    }
    
    /**
     * Delete drone
     */
    public function delete($id) {
        AuthMiddleware::handle(true);
        
        if (DashboardApiService::isApiEnabled()) {
            $this->error('Drohnen werden über das Flug-Dienstbuch verwaltet.', 'API_MANAGED', 403);
            return;
        }
        
        if ($this->getMethod() !== 'DELETE' && $this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $model = new Drohne();
        $drohne = $model->find($id);
        
        if (!$drohne) {
            $this->error('Drone not found', 'NOT_FOUND', 404);
        }
        
        $model->delete($id);
        
        $this->success(['id' => $id], 'Drone deleted successfully');
    }
}
