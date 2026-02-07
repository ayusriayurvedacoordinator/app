<?php
/**
 * Application Configuration
 * Centralized configuration management
 */

// Load environment variables if available
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Database configuration - defaults can be overridden by environment variables
defined('DB_HOST') or define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_USER') or define('DB_USER', getenv('DB_USER') ?: 'root');
defined('DB_PASS') or define('DB_PASS', getenv('DB_PASS') ?: '');
defined('DB_NAME') or define('DB_NAME', getenv('DB_NAME') ?: 'stock_receive');
defined('DB_PORT') or define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Application settings
defined('APP_TIMEZONE') or define('APP_TIMEZONE', 'Asia/Colombo');
defined('APP_CURRENCY') or define('APP_CURRENCY', 'LKR');
defined('APP_NAME') or define('APP_NAME', 'Stock Receive System');
defined('TIMEZONE_OFFSET') or define('TIMEZONE_OFFSET', '+05:30'); // Sri Lanka Standard Time

// Security settings
defined('CSRF_TOKEN_LIFETIME') or define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
defined('SESSION_LIFETIME') or define('SESSION_LIFETIME', 3600); // 1 hour
defined('MAX_LOGIN_ATTEMPTS') or define('MAX_LOGIN_ATTEMPTS', 5);
defined('LOCKOUT_DURATION') or define('LOCKOUT_DURATION', 900); // 15 minutes

// Pagination settings
defined('DEFAULT_PAGE_SIZE') or define('DEFAULT_PAGE_SIZE', 20);
defined('MAX_PAGE_SIZE') or define('MAX_PAGE_SIZE', 100);

// Validation settings
defined('PHONE_NUMBER_PATTERN') or define('PHONE_NUMBER_PATTERN', '/^0\d{9}$/'); // 10 digits, starts with 0
defined('EMAIL_MAX_LENGTH') or define('EMAIL_MAX_LENGTH', 255);
defined('NAME_MAX_LENGTH') or define('NAME_MAX_LENGTH', 255);
defined('ADDRESS_MAX_LENGTH') or define('ADDRESS_MAX_LENGTH', 500);

// File upload settings (if needed in future)
defined('MAX_FILE_SIZE') or define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
defined('ALLOWED_FILE_TYPES') or define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);
?>