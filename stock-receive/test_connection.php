<?php
// Test database connection
require_once 'config/database.php';

if ($conn) {
    echo "Database connection successful!<br>";
    
    // Test query to verify tables exist
    $result = $conn->query("SELECT COUNT(*) as count FROM vendors");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Vendors table exists and has " . $row['count'] . " records.<br>";
    } else {
        echo "Error querying vendors table: " . $conn->error . "<br>";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM items");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Items table exists and has " . $row['count'] . " records.<br>";
    } else {
        echo "Error querying items table: " . $conn->error . "<br>";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM inventory_records");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Inventory records table exists and has " . $row['count'] . " records.<br>";
    } else {
        echo "Error querying inventory records table: " . $conn->error . "<br>";
    }
    
    $conn->close();
} else {
    echo "Database connection failed!<br>";
}
?>