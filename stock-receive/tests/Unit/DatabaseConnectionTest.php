<?php
/**
 * Database Connection Test
 * Verifies that the database connection works properly in tests
 */

declare(strict_types=1);

require_once __DIR__ . '/../BaseTestCase.php';

class DatabaseConnectionTest extends BaseTestCase
{
    public function testDatabaseConnectionExists(): void
    {
        // Check that the connection variable exists
        $this->assertNotNull($this->conn);
        
        // Check that it's a mysqli object
        $this->assertInstanceOf(mysqli::class, $this->conn);
        
        // Test a simple query
        $result = $this->conn->query("SELECT 1 as test");
        $this->assertNotNull($result);
        
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['test']);
    }

    public function testDatabaseHasExpectedTables(): void
    {
        $tables = ['vendors', 'invoices', 'invoice_items', 'stock_recounts', 'stock_recount_items', 'categories', 'audit_trail'];
        
        foreach ($tables as $table) {
            $result = $this->conn->query("SHOW TABLES LIKE '$table'");
            $this->assertNotNull($result);
            $this->assertGreaterThan(0, $result->num_rows, "Table $table should exist");
        }
    }
}