<?php
/**
 * Vendor Model
 * Handles vendor-related business logic
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/audit_helper.php';

class Vendor {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Get all vendors
     */
    public function getAll($orderBy = 'name', $orderDir = 'ASC', $limit = null, $offset = 0) {
        try {
            $allowedOrderBy = ['id', 'name', 'created_at'];
            if (!in_array(strtolower($orderBy), $allowedOrderBy)) {
                $orderBy = 'name';
            }
            
            $allowedOrderDir = ['ASC', 'DESC'];
            if (!in_array(strtoupper($orderDir), $allowedOrderDir)) {
                $orderDir = 'ASC';
            }
            
            $sql = "SELECT * FROM vendors ORDER BY {$orderBy} {$orderDir}";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            Logger::error("Error getting vendors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get vendor by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM vendors WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            Logger::error("Error getting vendor by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new vendor
     */
    public function create($data) {
        try {
            $name = trim($data['name'] ?? '');
            $phone_number = trim($data['phone_number'] ?? '');
            $email = trim($data['email'] ?? '');
            $address = trim($data['address'] ?? '');
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception("Vendor name is required");
            }
            
            // Validate phone number format
            if (!preg_match(PHONE_NUMBER_PATTERN, $phone_number)) {
                throw new Exception("Phone number must be 10 digits and start with 0");
            }
            
            // Validate input lengths
            if (strlen($name) > NAME_MAX_LENGTH) {
                throw new Exception("Vendor name exceeds maximum length of " . NAME_MAX_LENGTH . " characters");
            }
            
            if (!empty($email) && strlen($email) > EMAIL_MAX_LENGTH) {
                throw new Exception("Email address exceeds maximum length of " . EMAIL_MAX_LENGTH . " characters");
            }
            
            if (strlen($address) > ADDRESS_MAX_LENGTH) {
                throw new Exception("Address exceeds maximum length of " . ADDRESS_MAX_LENGTH . " characters");
            }
            
            $stmt = $this->conn->prepare("INSERT INTO vendors (name, phone_number, email, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $phone_number, $email, $address);
            
            if ($stmt->execute()) {
                $vendor_id = $this->conn->insert_id;
                
                // Log the insert to audit trail
                $new_vendor_data = [
                    'name' => $name,
                    'phone_number' => $phone_number,
                    'email' => $email,
                    'address' => $address
                ];
                log_insert('vendors', $vendor_id, $new_vendor_data);
                
                return $vendor_id;
            } else {
                throw new Exception("Error creating vendor: " . $stmt->error);
            }
        } catch (Exception $e) {
            Logger::error("Error creating vendor: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update a vendor
     */
    public function update($id, $data) {
        try {
            $name = trim($data['name'] ?? '');
            $phone_number = trim($data['phone_number'] ?? '');
            $email = trim($data['email'] ?? '');
            $address = trim($data['address'] ?? '');
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception("Vendor name is required");
            }
            
            // Validate phone number format
            if (!empty($phone_number) && !preg_match(PHONE_NUMBER_PATTERN, $phone_number)) {
                throw new Exception("Phone number must be 10 digits and start with 0");
            }
            
            // Validate input lengths
            if (strlen($name) > NAME_MAX_LENGTH) {
                throw new Exception("Vendor name exceeds maximum length of " . NAME_MAX_LENGTH . " characters");
            }
            
            if (!empty($email) && strlen($email) > EMAIL_MAX_LENGTH) {
                throw new Exception("Email address exceeds maximum length of " . EMAIL_MAX_LENGTH . " characters");
            }
            
            if (strlen($address) > ADDRESS_MAX_LENGTH) {
                throw new Exception("Address exceeds maximum length of " . ADDRESS_MAX_LENGTH . " characters");
            }
            
            $stmt = $this->conn->prepare("UPDATE vendors SET name=?, phone_number=?, email=?, address=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $phone_number, $email, $address, $id);
            
            if ($stmt->execute()) {
                // Get old values for audit trail
                $old_vendor = $this->getById($id);
                
                // Get new values for audit trail
                $new_vendor_data = [
                    'name' => $name,
                    'phone_number' => $phone_number,
                    'email' => $email,
                    'address' => $address
                ];
                
                log_update('vendors', $id, $old_vendor, $new_vendor_data);
                
                return true;
            } else {
                throw new Exception("Error updating vendor: " . $stmt->error);
            }
        } catch (Exception $e) {
            Logger::error("Error updating vendor: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete a vendor
     */
    public function delete($id) {
        try {
            // Get the vendor data before deletion for audit trail
            $vendor = $this->getById($id);
            
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }
            
            // Log the deletion to audit trail
            log_delete('vendors', $id, $vendor);
            
            $stmt = $this->conn->prepare("DELETE FROM vendors WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error deleting vendor: " . $stmt->error);
            }
        } catch (Exception $e) {
            Logger::error("Error deleting vendor: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get vendor count
     */
    public function getCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM vendors");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            Logger::error("Error getting vendor count: " . $e->getMessage());
            return 0;
        }
    }
}
?>