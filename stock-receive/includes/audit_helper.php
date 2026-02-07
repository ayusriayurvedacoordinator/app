<?php
/**
 * Audit Trail Helper Functions
 * This file contains functions to log changes to the audit trail table
 */

/**
 * Log an insert operation to the audit trail
 */
function log_insert($table_name, $record_id, $new_values, $changed_by = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO audit_trail (table_name, record_id, action, new_values, changed_by) VALUES (?, ?, 'INSERT', ?, ?)");
    $new_values_json = json_encode($new_values);
    $changed_by = $changed_by ?: $_SESSION['username'] ?? 'system';
    $stmt->bind_param("siss", $table_name, $record_id, $new_values_json, $changed_by);
    $stmt->execute();
    $stmt->close();
}

/**
 * Log an update operation to the audit trail
 */
function log_update($table_name, $record_id, $old_values, $new_values, $changed_by = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO audit_trail (table_name, record_id, action, old_values, new_values, changed_by) VALUES (?, ?, 'UPDATE', ?, ?, ?)");
    $old_values_json = json_encode($old_values);
    $new_values_json = json_encode($new_values);
    $changed_by = $changed_by ?: $_SESSION['username'] ?? 'system';
    $stmt->bind_param("sisss", $table_name, $record_id, $old_values_json, $new_values_json, $changed_by);
    $stmt->execute();
    $stmt->close();
}

/**
 * Log a delete operation to the audit trail
 */
function log_delete($table_name, $record_id, $old_values, $changed_by = null) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO audit_trail (table_name, record_id, action, old_values, changed_by) VALUES (?, ?, 'DELETE', ?, ?)");
    $old_values_json = json_encode($old_values);
    $changed_by = $changed_by ?: $_SESSION['username'] ?? 'system';
    $stmt->bind_param("siss", $table_name, $record_id, $old_values_json, $changed_by);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get audit trail for a specific record
 */
function get_audit_trail($table_name, $record_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM audit_trail WHERE table_name = ? AND record_id = ? ORDER BY changed_at DESC");
    $stmt->bind_param("si", $table_name, $record_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trail = [];
    while ($row = $result->fetch_assoc()) {
        $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
        $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        $trail[] = $row;
    }
    
    $stmt->close();
    return $trail;
}

/**
 * Get audit trail for a specific table
 */
function get_table_audit_trail($table_name, $limit = 50) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM audit_trail WHERE table_name = ? ORDER BY changed_at DESC LIMIT ?");
    $stmt->bind_param("si", $table_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trail = [];
    while ($row = $result->fetch_assoc()) {
        $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
        $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        $trail[] = $row;
    }
    
    $stmt->close();
    return $trail;
}
?>