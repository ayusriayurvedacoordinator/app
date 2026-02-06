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

-- Add category_id column to invoice_items table
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL;
ALTER TABLE invoice_items ADD FOREIGN KEY IF NOT EXISTS (category_id) REFERENCES categories(id);

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