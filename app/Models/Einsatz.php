<?php
namespace App\Models;

use PDO;

/**
 * Einsatz (Operation) model
 */
class Einsatz extends BaseModel {
    protected $table = 'einsatz';
    
    /**
     * Get all operations with personal info
     */
    public function getAllWithPersonal() {
        $sql = "SELECT e.*, 
                p1.vorname || ' ' || p1.nachname AS gruppenfuehrer,
                p2.vorname || ' ' || p2.nachname AS dokumentierende
                FROM einsatz e
                LEFT JOIN personal p1 ON e.gruppenfuehrer_id = p1.id
                LEFT JOIN personal p2 ON e.dokumentierende_id = p2.id
                ORDER BY e.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get operation with personal info
     */
    public function getWithPersonal($id) {
        $stmt = $this->db->prepare("SELECT e.*, 
                p1.vorname || ' ' || p1.nachname AS gruppenfuehrer,
                p2.vorname || ' ' || p2.nachname AS dokumentierende
                FROM einsatz e
                LEFT JOIN personal p1 ON e.gruppenfuehrer_id = p1.id
                LEFT JOIN personal p2 ON e.dokumentierende_id = p2.id
                WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get personnel for operation
     */
    public function getPersonnel($einsatzId) {
        $stmt = $this->db->prepare("SELECT p.* FROM personal p 
                INNER JOIN einsatz_personal ep ON p.id = ep.personal_id 
                WHERE ep.einsatz_id = ?");
        $stmt->execute([$einsatzId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add personnel to operation
     */
    public function addPersonnel($einsatzId, $personalIds) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO einsatz_personal (einsatz_id, personal_id) VALUES (?, ?)");
            foreach ($personalIds as $personalId) {
                $stmt->execute([$einsatzId, (int)$personalId]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Update personnel for operation
     */
    public function updatePersonnel($einsatzId, $personalIds) {
        $this->db->beginTransaction();
        try {
            // Delete existing
            $stmt = $this->db->prepare("DELETE FROM einsatz_personal WHERE einsatz_id = ?");
            $stmt->execute([$einsatzId]);
            
            // Insert new
            if (!empty($personalIds)) {
                $stmt = $this->db->prepare("INSERT INTO einsatz_personal (einsatz_id, personal_id) VALUES (?, ?)");
                foreach ($personalIds as $personalId) {
                    $stmt->execute([$einsatzId, (int)$personalId]);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Complete operation
     */
    public function complete($id) {
        // Add "Ende der Dokumentation" entry if not exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM einsatz_dokumentation WHERE einsatz_id = ? AND text = ?");
        $stmt->execute([$id, 'Ende der Dokumentation']);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare("SELECT MAX(zeilennummer) FROM einsatz_dokumentation WHERE einsatz_id = ?");
            $stmt->execute([$id]);
            $zeilennummer = (int)$stmt->fetchColumn() + 1;
            
            $stmt = $this->db->prepare("INSERT INTO einsatz_dokumentation (einsatz_id, zeilennummer, zeitpunkt, text) VALUES (?, ?, datetime('now', 'localtime'), ?)");
            $stmt->execute([$id, $zeilennummer, 'Ende der Dokumentation']);
        }
        
        // Set end time
        $stmt = $this->db->prepare("SELECT endzeit FROM einsatz WHERE id = ?");
        $stmt->execute([$id]);
        $currentEnd = $stmt->fetchColumn();
        
        if (!$currentEnd) {
            $stmt = $this->db->prepare("UPDATE einsatz SET endzeit = datetime('now', 'localtime') WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        return true;
    }
    
    /**
     * Get last operation ID
     */
    public function getLastId() {
        $stmt = $this->db->query("SELECT id FROM einsatz ORDER BY id DESC LIMIT 1");
        return $stmt->fetchColumn();
    }
}
