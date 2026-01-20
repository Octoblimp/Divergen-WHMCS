<?php
namespace OpenWHM\Core;

use PDO;
use PDOException;

/**
 * Database Connection Handler
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $prefix;
    
    private function __construct()
    {
        $this->prefix = DB_PREFIX;
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Connect to database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            Logger::error("Database connection failed: " . $e->getMessage());
            throw new \Exception("Database connection failed");
        }
    }
    
    /**
     * Get PDO instance
     */
    public function getPdo()
    {
        return $this->pdo;
    }
    
    /**
     * Get table name with prefix
     */
    public function table($name)
    {
        return $this->prefix . $name;
    }
    
    /**
     * Execute a query
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error("Query failed: " . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Fetch all results
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch single row
     */
    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Fetch single value
     */
    public function fetchValue($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchColumn();
    }
    
    /**
     * Insert a row
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table($table)} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update rows
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table($table)} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Delete rows
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$this->table($table)} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Count rows
     */
    public function count($table, $where = '1', $params = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table($table)} WHERE {$where}";
        return (int) $this->fetchValue($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
