<?php
namespace App\Services;

use App\Models\Flight;
use App\Models\Personal;

/**
 * Dashboard integration service
 */
class DashboardIntegrationService {
    /**
     * Insert flight data to dashboard
     */
    public static function insertFlight($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd) {
        // Parse pilot name
        $pilotParts = explode(' ', $pilotName, 2);
        $vorname = trim($pilotParts[0] ?? '');
        $nachname = trim($pilotParts[1] ?? '');
        
        if (empty($vorname) || empty($nachname)) {
            throw new \Exception('Ungültiger Pilot-Name');
        }
        
        // Find pilot in personal table
        $personalModel = new Personal();
        $pilot = $personalModel->findByName($vorname, $nachname);
        
        if (!$pilot || empty($pilot['dashboard_id'])) {
            throw new \Exception("Pilot '{$vorname} {$nachname}' nicht in Personal-Tabelle gefunden");
        }
        
        // Validate battery number
        if ($batteryNumber < 1 || $batteryNumber > 999) {
            throw new \Exception('Ungültige Akku-Nummer');
        }
        
        // Get last location ID
        $flightModel = new Flight();
        $locationId = $flightModel->getLastLocationId();
        
        // Insert flight
        $flightModel->insert([
            'pilot_id' => $pilot['dashboard_id'],
            'flight_date' => $flightStart,
            'flight_end_date' => $flightEnd,
            'flight_location_id' => $locationId,
            'drone_id' => $droneId,
            'battery_number' => $batteryNumber
        ]);
        
        return true;
    }
}
