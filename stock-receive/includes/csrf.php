<?php
/**
 * CSRF Protection Utilities
 * Implements Cross-Site Request Forgery protection
 */

session_start();

/**
 * Generate a CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check CSRF token from POST request
 */
function check_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? null;
        
        if (!$token || !validate_csrf_token($token)) {
            error_log("CSRF token validation failed");
            die("CSRF token validation failed");
        }
    }
}

/**
 * Generate hidden input field with CSRF token
 */
function csrf_input_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}
?>