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

    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Categories table exists and has " . $row['count'] . " records.<br>";
    } else {
        echo "Error querying categories table: " . $conn->error . "<br>";
    }

    $result = $conn->query("SELECT COUNT(*) as count FROM invoices");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Invoices table exists and has " . $row['count'] . " records.<br>";
    } else {
        echo "Error querying invoices table: " . $conn->error . "<br>";
    }

    $conn->close();
} else {
    echo "Database connection failed!<br>";
}
?>