-- Seed data for stock receive application

USE stock_receive;

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

-- Insert sample vendors
INSERT IGNORE INTO vendors (name, contact_info, address) VALUES
('ABC Medical Supplies', '+94771234567', '123 Main Street, Colombo'),
('XYZ Pharma', '+94712345678', '456 Market Road, Kandy'),
('Health Plus Distributors', '+94789012345', '789 Hospital Avenue, Galle');