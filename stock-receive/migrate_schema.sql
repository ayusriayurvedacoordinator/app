-- Migration script to update database schema for invoices

USE stock_receive;

-- Drop the old inventory_records table
DROP TABLE IF EXISTS inventory_records;

-- Create invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    invoice_number VARCHAR(100),
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    received_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id)
);

-- Create invoice_items table (for products within invoices)
CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    rate DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_free_of_charge BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Insert sample invoices
INSERT INTO invoices (vendor_id, invoice_number, invoice_date, total_amount, discount, received_date, notes) VALUES
(1, 'INV-001', '2026-01-15', 8500.00, 0.00, '2026-01-15', 'Bulk order for new employees'),
(2, 'INV-002', '2026-01-20', 8200.00, 100.00, '2026-01-20', 'Competitive pricing'),
(1, 'INV-003', '2026-01-15', 750.00, 0.00, '2026-01-15', 'Executive office chairs'),
(2, 'INV-004', '2026-01-18', 110.00, 0.00, '2026-01-18', 'Monthly supply');

-- Insert sample invoice items
INSERT INTO invoice_items (invoice_id, product_name, quantity, rate, amount, is_free_of_charge) VALUES
(1, 'Laptop Computer', 10, 850.00, 8500.00, FALSE),
(2, 'Laptop Computer', 10, 820.00, 8200.00, FALSE),
(3, 'Office Chair', 5, 150.00, 750.00, FALSE),
(4, 'Printer Paper', 20, 5.50, 110.00, FALSE),
(1, 'Mouse Pad (Free)', 10, 0.00, 0.00, TRUE); -- Free of charge item

-- Create indexes for better performance
CREATE INDEX idx_invoice_vendor ON invoices(vendor_id);
CREATE INDEX idx_invoice_date ON invoices(invoice_date);
CREATE INDEX idx_invoice_items_invoice ON invoice_items(invoice_id);