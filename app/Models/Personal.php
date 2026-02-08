<?php
namespace App\Models;

/**
 * Personal (Personnel) model
 */
class Personal extends BaseModel {
    protected $table = 'personal';
    
    /**
     * Get all personnel ordered by name
     */
    public function getAllOrdered() {
        return $this->findAll('nachname, vorname');
    }
    
    /**
     * Find by dashboard ID
     */
    public function findByDashboardId($dashboardId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE dashboard_id = ?");
        $stmt->execute([$dashboardId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find by name
     */
    public function findByName($vorname, $nachname) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE vorname = ? AND nachname = ?");
        $stmt->execute([$vorname, $nachname]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
