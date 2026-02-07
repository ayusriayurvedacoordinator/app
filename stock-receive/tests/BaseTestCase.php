<?php
/**
 * Base Test Case
 * Provides common functionality for all tests
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected $conn;

    protected function setUp(): void
    {
        // Initialize the database connection directly
        // Check if it's already available as a global
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $this->conn = $GLOBALS['conn'];
        } else {
            // If not, create a new connection using the same configuration
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $username = defined('DB_USER') ? DB_USER : 'root';
            $password = defined('DB_PASS') ? DB_PASS : '';
            $dbname = defined('DB_NAME') ? DB_NAME : 'stock_receive';
            $port = defined('DB_PORT') ? (int)DB_PORT : 3306;

            $this->conn = new mysqli($host, $username, $password, $dbname, $port);

            if ($this->conn->connect_error) {
                throw new \Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Set session timezone to match application timezone
            $this->conn->query("SET time_zone = '+05:30'"); // Sri Lanka Standard Time
        }
        
        // Make sure the global $conn variable is available for models and services
        $GLOBALS['conn'] = $this->conn;
        global $conn;
        $conn = $this->conn;
        
        parent::setUp();
    }
}