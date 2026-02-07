-- Script to add audit trail to existing database

USE stock_receive;

-- Create audit_trail table if it doesn't exist
CREATE TABLE IF NOT EXISTS audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    changed_by VARCHAR(100), -- Could store user info if implemented
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add updated_at column to existing tables if not present
-- Check if updated_at column exists in vendors table
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = 'stock_receive' 
                     AND TABLE_NAME = 'vendors' 
                     AND COLUMN_NAME = 'updated_at');

SET @sql = IF(@column_exists = 0, 
              'ALTER TABLE vendors ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 
              'SELECT "Column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if updated_at column exists in invoices table
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = 'stock_receive' 
                     AND TABLE_NAME = 'invoices' 
                     AND COLUMN_NAME = 'updated_at');

SET @sql = IF(@column_exists = 0, 
              'ALTER TABLE invoices ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 
              'SELECT "Column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if updated_at column exists in invoice_items table
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = 'stock_receive' 
                     AND TABLE_NAME = 'invoice_items' 
                     AND COLUMN_NAME = 'updated_at');

SET @sql = IF(@column_exists = 0, 
              'ALTER TABLE invoice_items ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 
              'SELECT "Column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;