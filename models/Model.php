<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Database;
use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Base Model Class
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get table name with prefix
     */
    protected function getTable()
    {
        return $this->db->table($this->table);
    }
    
    /**
     * Find by ID
     */
    public function find($id)
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->getTable()} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Find by field
     */
    public function findBy($field, $value)
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->getTable()} WHERE {$field} = ?",
            [$value]
        );
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->getTable()}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get records with conditions
     */
    public function where($conditions, $params = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$conditions}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get first record with conditions
     */
    public function first($conditions, $params = [])
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$conditions} LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Create a new record
     */
    public function create($data)
    {
        // Filter to fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update a record
     */
    public function update($id, $data)
    {
        // Filter to fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        // Add timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }
    
    /**
     * Delete a record
     */
    public function delete($id)
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }
    
    /**
     * Count records
     */
    public function count($conditions = '1', $params = [])
    {
        return $this->db->count($this->table, $conditions, $params);
    }
    
    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 20, $conditions = '1', $params = [], $orderBy = 'id DESC')
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions, $params);
        
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$conditions} ORDER BY {$orderBy} LIMIT {$offset}, {$perPage}";
        $items = $this->db->fetchAll($sql, $params);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_more' => $page * $perPage < $total
        ];
    }
}
