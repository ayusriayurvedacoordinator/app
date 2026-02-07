-- Add stock_recounts table for periodic stock counting

USE stock_receive;

-- Create stock_recounts table to track physical stock counts
CREATE TABLE IF NOT EXISTS stock_recounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recount_date DATE NOT NULL,
    counted_by VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create stock_recount_items table to track individual item counts
CREATE TABLE IF NOT EXISTS stock_recount_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recount_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    counted_quantity INT NOT NULL,
    previous_quantity INT NOT NULL,
    variance INT NOT NULL, -- difference between counted and previous
    variance_reason TEXT, -- reason for the difference
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recount_id) REFERENCES stock_recounts(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_stock_recount_date ON stock_recounts(recount_date);
CREATE INDEX idx_stock_recount_items_recount ON stock_recount_items(recount_id);