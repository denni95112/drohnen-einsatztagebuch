<?php
namespace App\Models;

use App\Utils\Database;
use PDO;

/**
 * Base model class
 */
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find all
     */
    public function findAll($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create record
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $fields = array_keys($data);
        $set = implode(', ', array_map(function($field) {
            return "{$field} = ?";
        }, $fields));
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get database connection
     */
    protected function getDb() {
        return $this->db;
    }
}
