-- Test data for stock receive application

USE stock_receive;

-- Insert test vendors
INSERT INTO vendors (name, contact_info, address) VALUES
('Test Medical Supply Co', '+94777777777', '777 Test Street, Matara'),
('Demo Pharma Ltd', '+94766666666', '666 Demo Road, Jaffna');

-- Insert test invoices
INSERT INTO invoices (vendor_id, invoice_number, invoice_date, total_amount, discount, received_date, notes) VALUES
(4, 'INV-001', '2023-01-15', 15000.00, 500.00, '2023-01-20', 'New year purchase'),
(5, 'INV-002', '2023-02-10', 8500.75, 0.00, '2023-02-12', 'Monthly supplies');

-- Insert test invoice items
INSERT INTO invoice_items (invoice_id, product_name, quantity, rate, amount, is_free_of_charge, category_id) VALUES
(1, 'Herbal Balm Extra Strength', 50, 200.00, 10000.00, FALSE, 1),
(1, 'Coconut Oil Organic', 30, 150.00, 4500.00, FALSE, 2),
(2, 'Vitamin C Tablets', 100, 85.00, 8500.00, FALSE, 6),
(2, 'Children Cough Syrup', 25, 203.03, 5075.75, TRUE, 7); -- FOC item

-- Insert test stock recounts
INSERT INTO stock_recounts (recount_date, counted_by, notes) VALUES
('2023-03-01', 'John Doe', 'Quarterly stock recount'),
('2023-03-15', 'Jane Smith', 'Special recount after delivery');

-- Insert test stock recount items
INSERT INTO stock_recount_items (recount_id, product_name, counted_quantity, previous_quantity, variance, variance_reason) VALUES
(1, 'Herbal Balm Extra Strength', 45, 50, -5, 'Sold 5 units'),
(1, 'Coconut Oil Organic', 28, 30, -2, 'Used for samples'),
(2, 'Vitamin C Tablets', 95, 100, -5, 'Damaged goods returned');