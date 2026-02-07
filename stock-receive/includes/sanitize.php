<?php
/**
 * Input/Output Sanitization Utilities
 * Provides functions for sanitizing user input and output
 */

/**
 * Sanitize user input for display
 */
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize user input for storage
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Sanitize user input for database storage (more restrictive)
 */
function sanitize_for_db($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // Additional sanitization for database storage
    $data = str_replace(["'", "\"", "\\", "`"], "", $data);
    return $data;
}

/**
 * Sanitize HTML content (for rich text)
 */
function sanitize_html($data) {
    // Allow only safe HTML tags
    $allowed_tags = '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6>';
    return strip_tags($data, $allowed_tags);
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate URL
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Escape output for JavaScript
 */
function escape_js($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP);
}
?>