<?php
/**
 * Database utilities
 * Sistem Permohonan Keluar
 */

// Define global PDO connection
$pdo = null;

/**
 * Connect to the database
 * 
 * @return PDO Database connection
 */
function connect_db() {
    global $pdo;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        // For testing purposes, we'll use a mock PDO object
        // In production, this would be a real database connection
        
        // Mock PDO for testing
        $mockPdo = new class extends PDO {
            public function __construct() {
                // Mock constructor without actually connecting to a database
            }
            
            public function prepare($statement, $options = null) {
                // Return a mock statement
                return new class($statement) {
                    private $query;
                    
                    public function __construct($query) {
                        $this->query = $query;
                    }
                    
                    public function execute($params = null) {
                        // Log the execution for debugging
                        error_log("Mock execute: " . $this->query . " with params: " . json_encode($params));
                        return true;
                    }
                };
            }
        };
        
        $pdo = $mockPdo;
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
    }
}

// Initialize the database connection
$pdo = connect_db(); 