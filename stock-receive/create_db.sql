#!/usr/bin/mysql -u root -p"@yu5r14yurv3da@"

-- Database setup for stock receive application

-- Create the database
CREATE DATABASE IF NOT EXISTS stock_receive;
USE stock_receive;

-- Create vendors table
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_info TEXT,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    unit VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create inventory_records table
CREATE TABLE inventory_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity_received DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    received_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Insert sample vendors
INSERT INTO vendors (name, contact_info, address) VALUES
('ABC Supplier', 'contact@abc.com, +1234567890', '123 Main St, City, State'),
('XYZ Distributors', 'info@xyz.com, +0987654321', '456 Oak Ave, Town, State');

-- Insert sample items
INSERT INTO items (name, description, category, unit) VALUES
('Laptop Computer', 'Standard business laptop', 'Electronics', 'unit'),
('Office Chair', 'Ergonomic office chair', 'Furniture', 'unit'),
('Printer Paper', 'A4 size, 80gsm', 'Office Supplies', 'ream');

-- Insert sample inventory records
INSERT INTO inventory_records (vendor_id, item_id, quantity_received, unit_price, total_cost, received_date, notes) VALUES
(1, 1, 10, 850.00, 8500.00, '2026-01-15', 'Bulk order for new employees'),
(2, 1, 10, 820.00, 8200.00, '2026-01-20', 'Competitive pricing'),
(1, 2, 5, 150.00, 750.00, '2026-01-15', 'Executive office chairs'),
(2, 3, 20, 5.50, 110.00, '2026-01-18', 'Monthly supply');