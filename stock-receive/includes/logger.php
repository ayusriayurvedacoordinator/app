<?php
/**
 * Error Logging Utility
 * Provides centralized error logging functionality
 */

class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';
    
    /**
     * Initialize the logger
     */
    public static function init() {
        // Create logs directory if it doesn't exist
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Create log file if it doesn't exist
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0664);
        }
    }
    
    /**
     * Log an info message
     */
    public static function info($message) {
        self::log('INFO', $message);
    }
    
    /**
     * Log a warning message
     */
    public static function warning($message) {
        self::log('WARNING', $message);
    }
    
    /**
     * Log an error message
     */
    public static function error($message) {
        self::log('ERROR', $message);
    }
    
    /**
     * Log a debug message
     */
    public static function debug($message) {
        self::log('DEBUG', $message);
    }
    
    /**
     * Internal logging method
     */
    private static function log($level, $message) {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logMessage = "[{$timestamp}] [{$level}] [IP: {$ip}] [URI: {$uri}] - {$message}" . PHP_EOL;
        
        // Also add user agent for security-related logs
        if ($level === 'ERROR' || $level === 'WARNING') {
            $logMessage = "[{$timestamp}] [{$level}] [IP: {$ip}] [UA: {$userAgent}] [URI: {$uri}] - {$message}" . PHP_EOL;
        }
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log an exception
     */
    public static function exception($exception) {
        self::error(
            "Exception: " . $exception->getMessage() . 
            " in " . $exception->getFile() . 
            " on line " . $exception->getLine() .
            " Trace: " . $exception->getTraceAsString()
        );
    }
}

// Set up error handler to log PHP errors
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    Logger::error("PHP Error [{$severity}]: {$message} in {$file} on line {$line}");
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set up exception handler to log uncaught exceptions
set_exception_handler(function($exception) {
    Logger::exception($exception);
    // Don't expose detailed error info to users in production
    die("An error occurred. Please try again later.");
});

// Log fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR)) {
        Logger::error("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
    }
});
?>