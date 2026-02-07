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
ALTER TABLE vendors ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create indexes if they don't exist
-- We'll use a stored procedure to handle index creation safely
DELIMITER $$

CREATE PROCEDURE CreateIndexIfNotExists(IN indexName VARCHAR(64), IN tableName VARCHAR(64), IN columnName VARCHAR(64))
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = tableName 
        AND index_name = indexName
    ) THEN
        SET @sql = CONCAT('CREATE INDEX ', indexName, ' ON ', tableName, '(', columnName, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Call the procedure to create indexes safely
CALL CreateIndexIfNotExists('idx_invoice_vendor', 'invoices', 'vendor_id');
CALL CreateIndexIfNotExists('idx_invoice_date', 'invoices', 'invoice_date');
CALL CreateIndexIfNotExists('idx_invoice_items_invoice', 'invoice_items', 'invoice_id');
CALL CreateIndexIfNotExists('idx_audit_table_record', 'audit_trail', 'table_name, record_id');
CALL CreateIndexIfNotExists('idx_audit_changed_at', 'audit_trail', 'changed_at');

-- Drop the procedure
DROP PROCEDURE IF EXISTS CreateIndexIfNotExists;