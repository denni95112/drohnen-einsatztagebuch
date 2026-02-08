<?php
namespace App\Services;

use App\Models\Flight;
use App\Models\Personal;

/**
 * Dashboard integration service.
 * When API is configured: creates flights via Flug-Dienstbuch API.
 * Otherwise: uses direct DB access (path_to_dashboard_db) for backward compatibility.
 */
class DashboardIntegrationService {

    /**
     * Insert flight data to dashboard (API or DB depending on config).
     *
     * @param int|null $locationId Optional flight location ID (e.g. from Flugstandort dropdown). If null, last location is used (DB path only).
     */
    public static function insertFlight($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd, $locationId = null) {
        if (DashboardApiService::isApiEnabled()) {
            return self::insertFlightViaApi($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd, $locationId);
        }
        return self::insertFlightViaDb($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd, $locationId);
    }

    /**
     * Create flight via Flug-Dienstbuch REST API.
     */
    private static function insertFlightViaApi($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd, $locationId): bool {
        $pilotName = trim($pilotName);
        if ($pilotName === '') {
            throw new \Exception('Ungültiger Pilot-Name');
        }
        if ($batteryNumber < 1 || $batteryNumber > 999) {
            throw new \Exception('Ungültige Akku-Nummer');
        }

        $pilots = DashboardApiService::getPilots();
        $pilotId = null;
        foreach ($pilots as $p) {
            $fullName = trim(($p['vorname'] ?? '') . ' ' . ($p['nachname'] ?? ''));
            if ($fullName === $pilotName) {
                $pilotId = (int) ($p['id'] ?? $p['dashboard_id'] ?? 0);
                break;
            }
        }
        if ($pilotId === null || $pilotId <= 0) {
            throw new \Exception("Pilot '{$pilotName}' nicht im Flug-Dienstbuch gefunden");
        }

        $payload = [
            'pilot_id' => $pilotId,
            'flight_date' => $flightStart,
            'flight_end_date' => $flightEnd,
            'drone_id' => (int) $droneId,
            'battery_number' => (int) $batteryNumber,
        ];
        if ($locationId !== null && $locationId > 0) {
            $payload['location_id'] = (int) $locationId;
        }

        $response = DashboardApiService::makeApiRequest('/api/flights.php?action=create', 'POST', $payload);
        if (!$response['success']) {
            $msg = $response['error'] ?? $response['data']['error'] ?? 'Flug konnte nicht eingetragen werden';
            throw new \Exception($msg);
        }
        return true;
    }

    /**
     * Create flight via direct database access (backward compatibility when API is not configured).
     */
    private static function insertFlightViaDb($pilotName, $copilotName, $droneId, $batteryNumber, $flightStart, $flightEnd, $locationId): bool {
        $pilotParts = explode(' ', $pilotName, 2);
        $vorname = trim($pilotParts[0] ?? '');
        $nachname = trim($pilotParts[1] ?? '');

        if (empty($vorname) || empty($nachname)) {
            throw new \Exception('Ungültiger Pilot-Name');
        }

        $personalModel = new Personal();
        $pilot = $personalModel->findByName($vorname, $nachname);
        if (!$pilot || empty($pilot['dashboard_id'])) {
            throw new \Exception("Pilot '{$vorname} {$nachname}' nicht in Personal-Tabelle gefunden");
        }
        if ($batteryNumber < 1 || $batteryNumber > 999) {
            throw new \Exception('Ungültige Akku-Nummer');
        }

        $flightModel = new Flight();
        if ($locationId === null) {
            $locationId = $flightModel->getLastLocationId();
        }

        $flightModel->insert([
            'pilot_id' => $pilot['dashboard_id'],
            'flight_date' => $flightStart,
            'flight_end_date' => $flightEnd,
            'flight_location_id' => $locationId,
            'drone_id' => $droneId,
            'battery_number' => $batteryNumber,
        ]);
        return true;
    }
}
