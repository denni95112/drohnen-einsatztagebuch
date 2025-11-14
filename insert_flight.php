<?php
require_once 'utils.php';

try {
    $config = include __DIR__ . '/config/config.php';
    
    // Validate required config
    if (empty($config['path_to_dashboard_db'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Dashboard-Datenbank-Pfad nicht konfiguriert']);
        exit;
    }
    
    try {
        $dashboard_db = new PDO('sqlite:' . $config["path_to_dashboard_db"]);
        $dashboard_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fehler beim Verbinden zur Dashboard-Datenbank']);
        exit;
    }
    
    // Get database path from config, fallback to default
    $databasePath = isset($config['database_path']) ? $config['database_path'] : 'einsatzbuch.db';
    // Handle relative paths
    if (!is_absolute_path($databasePath)) {
        $databasePath = __DIR__ . '/' . $databasePath;
    }
    
    try {
        $db = new PDO('sqlite:' . $databasePath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fehler beim Verbinden zur Hauptdatenbank']);
        exit;
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    $pilot = $input['pilot'] ?? '';
    $copilot = $input['copilot'] ?? '';
    $drone_id = intval($input['drone_id']);
    $battery_number = intval($input['battery_number']);
    $flight_start = $input['flight_start'];
    $flight_end = $input['flight_end'];

    // Get the latest location_id from flight_locations
    $stmtLoc = $dashboard_db->prepare("SELECT id FROM flight_locations ORDER BY id DESC LIMIT 1");
    $stmtLoc->execute();
    $location_row = $stmtLoc->fetch(PDO::FETCH_ASSOC);
    $location_id = $location_row['id'] ?? null;

    // Split pilot into vorname and nachname
    $pilotParts = explode(' ', $pilot, 2);
    $vorname = trim($pilotParts[0] ?? '');
    $nachname = trim($pilotParts[1] ?? '');

    // Validate input
    if (empty($vorname) || empty($nachname)) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültiger Pilot-Name']);
        exit;
    }

    // Validate battery number range
    if ($battery_number < 1 || $battery_number > 999) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültige Akku-Nummer']);
        exit;
    }

    // Look up dashboard_id from personal table (using PDO)
    $stmtLookup = $db->prepare("SELECT dashboard_id FROM personal WHERE vorname = :vorname AND nachname = :nachname");
    if (!$stmtLookup) {
        http_response_code(500);
        echo json_encode(['error' => 'Datenbankfehler']);
        exit;
    }
    
    $stmtLookup->bindParam(':vorname', $vorname);
    $stmtLookup->bindParam(':nachname', $nachname);
    $stmtLookup->execute();
    $row = $stmtLookup->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['dashboard_id'])) {
        http_response_code(404);
        echo json_encode(['error' => "Pilot '$vorname $nachname' nicht in Personal-Tabelle gefunden"]);
        exit;
    }

    $pilot_id = $row['dashboard_id'];

    // Insert into flights table (using PDO)
    $stmt = $dashboard_db->prepare("INSERT INTO flights (pilot_id, flight_date, flight_end_date, flight_location_id, drone_id, battery_number)
                          VALUES (:pilot_id, :flight_date, :flight_end_date, :flight_location_id, :drone_id, :battery_number)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Datenbankfehler beim Vorbereiten']);
        exit;
    }
    
    $stmt->bindValue(':pilot_id', $pilot_id, PDO::PARAM_INT);
    $stmt->bindValue(':flight_date', $flight_start, PDO::PARAM_STR);
    $stmt->bindValue(':flight_end_date', $flight_end, PDO::PARAM_STR);
    $stmt->bindValue(':flight_location_id', $location_id, PDO::PARAM_INT);
    $stmt->bindValue(':drone_id', $drone_id, PDO::PARAM_INT);
    $stmt->bindValue(':battery_number', $battery_number, PDO::PARAM_INT);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Flug erfolgreich eingefügt']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Einfügen des Fluges']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>
