<?php
/**
 * Database Class
 * Handles database connections and operations
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/logger.php';

class Database {
    private $connection;
    private $host;
    private $username;
    private $password;
    private $dbname;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->dbname = DB_NAME;
        
        $this->connect();
    }
    
    /**
     * Create database connection
     */
    private function connect() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        
        if ($this->connection->connect_error) {
            Logger::error("Database connection failed: " . $this->connection->connect_error);
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }
        
        // Set session timezone to match application timezone
        $this->connection->query("SET time_zone = '+05:30'"); // Sri Lanka Standard Time
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Create a global instance for backward compatibility
try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    Logger::error("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}
?>