-- Add categories table and update invoice_items table

USE stock_receive;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add category_id column to invoice_items table if it doesn't exist
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = 'stock_receive' 
                     AND TABLE_NAME = 'invoice_items' 
                     AND COLUMN_NAME = 'category_id');

SET @sql = IF(@column_exists = 0, 
              'ALTER TABLE invoice_items ADD COLUMN category_id INT DEFAULT NULL, ADD FOREIGN KEY (category_id) REFERENCES categories(id)',
              'SELECT "Column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert default categories
INSERT IGNORE INTO categories (name, description) VALUES
('Balm', 'Topical medicinal preparations'),
('Oil', 'Medicinal oils'),
('Kwatha', 'Decoctions or herbal teas'),
('Paanta', 'Medicinal decoctions'),
('Churna', 'Medicinal powders'),
('Tablet', 'Tablets and capsules'),
('Syrup', 'Liquid medicines'),
('Cream', 'Topical creams and ointments');