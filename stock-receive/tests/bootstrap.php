<?php
/**
 * PHPUnit Bootstrap
 * Sets up the testing environment
 */

// Define the base path for the application
define('BASE_PATH', dirname(__DIR__));

// Include the autoloader or manually include necessary files
require_once BASE_PATH . '/config/config.php';

// Set application to test mode
define('APP_ENV', 'testing');

// Include necessary application files
// This will initialize the database connection as $conn
require_once BASE_PATH . '/config/database.php';

// At this point, the global $conn should be available
// Make sure it's accessible
global $conn;

// Include other necessary files after database connection is established
require_once BASE_PATH . '/includes/sanitize.php';
require_once BASE_PATH . '/includes/logger.php';
require_once BASE_PATH . '/includes/audit_helper.php';
require_once BASE_PATH . '/includes/csrf.php';