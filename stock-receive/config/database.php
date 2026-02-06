<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your MySQL username
define('DB_PASS', '@yu5r14yurv3da@');     // Change this to your MySQL password
define('DB_NAME', 'stock_receive');

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include audit helper
include_once dirname(__DIR__) . '/includes/audit_helper.php';
?>