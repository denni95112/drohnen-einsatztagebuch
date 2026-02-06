<?php
namespace App\Models;

use App\Utils\Database;
use PDO;

/**
 * Flight model (for dashboard integration).
 * Used only when Flug-Dienstbuch is connected via path_to_dashboard_db (direct DB).
 * When Dashboard API is configured, flights are created via API; this model is not used for inserts.
 * @deprecated Prefer API integration (DashboardApiService + DashboardIntegrationService::insertFlightViaApi)
 */
class Flight {
    private $db;
    
    public function __construct() {
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $config = include $configPath;
        
        if (empty($config['path_to_dashboard_db'])) {
            throw new \Exception('Dashboard database path not configured');
        }
        
        $this->db = new PDO('sqlite:' . $config['path_to_dashboard_db']);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * Insert flight data
     */
    public function insert($data) {
        $stmt = $this->db->prepare("INSERT INTO flights (pilot_id, flight_date, flight_end_date, flight_location_id, drone_id, battery_number)
                          VALUES (:pilot_id, :flight_date, :flight_end_date, :flight_location_id, :drone_id, :battery_number)");
        
        $stmt->bindValue(':pilot_id', $data['pilot_id'], PDO::PARAM_INT);
        $stmt->bindValue(':flight_date', $data['flight_date'], PDO::PARAM_STR);
        $stmt->bindValue(':flight_end_date', $data['flight_end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':flight_location_id', $data['flight_location_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':drone_id', $data['drone_id'], PDO::PARAM_INT);
        $stmt->bindValue(':battery_number', $data['battery_number'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get last location ID
     */
    public function getLastLocationId() {
        $stmt = $this->db->query("SELECT id FROM flight_locations ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'] ?? null;
    }
}
