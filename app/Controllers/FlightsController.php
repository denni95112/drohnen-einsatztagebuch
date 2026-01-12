<?php
namespace App\Controllers;

use App\Services\DashboardIntegrationService;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;

/**
 * Flights controller (for dashboard integration)
 */
class FlightsController extends BaseController {
    /**
     * Insert flight data
     */
    public function create() {
        AuthMiddleware::handle();
        
        if ($this->getMethod() !== 'POST') {
            $this->error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
        }
        
        $data = $this->getRequestData();
        
        $errors = Validator::required($data, ['pilot', 'drone_id', 'battery_number', 'flight_start', 'flight_end']);
        if (!empty($errors)) {
            $this->error('Validation failed', 'VALIDATION_ERROR', 422, $errors);
        }
        
        try {
            DashboardIntegrationService::insertFlight(
                $data['pilot'],
                $data['copilot'] ?? '',
                (int)$data['drone_id'],
                (int)$data['battery_number'],
                $data['flight_start'],
                $data['flight_end']
            );
            
            $this->created(null, 'Flight inserted successfully');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 'FLIGHT_INSERT_ERROR', 400);
        }
    }
}
