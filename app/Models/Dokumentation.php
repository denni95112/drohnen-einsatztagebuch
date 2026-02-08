<?php
namespace App\Models;

use PDO;

/**
 * Dokumentation (Documentation) model
 */
class Dokumentation extends BaseModel {
    protected $table = 'einsatz_dokumentation';
    
    /**
     * Get documentation entries for operation
     */
    public function getByEinsatzId($einsatzId, $order = 'DESC') {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE einsatz_id = ? ORDER BY zeilennummer {$order}");
        $stmt->execute([$einsatzId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add documentation entry
     */
    public function addEntry($einsatzId, $text) {
        // Get next line number
        $stmt = $this->db->prepare("SELECT MAX(zeilennummer) FROM {$this->table} WHERE einsatz_id = ?");
        $stmt->execute([$einsatzId]);
        $zeilennummer = (int)$stmt->fetchColumn() + 1;
        
        $zeitpunkt = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (einsatz_id, zeilennummer, zeitpunkt, text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$einsatzId, $zeilennummer, $zeitpunkt, $text]);
        
        return [
            'id' => $this->db->lastInsertId(),
            'zeilennummer' => $zeilennummer,
            'zeitpunkt' => $zeitpunkt,
            'text' => $text
        ];
    }
}
