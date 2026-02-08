<?php
namespace App\Models;

/**
 * Drohne (Drone) model
 */
class Drohne extends BaseModel {
    protected $table = 'drohnen';
    
    /**
     * Get all drones ordered by name
     */
    public function getAllOrdered() {
        return $this->findAll('name ASC');
    }
}
