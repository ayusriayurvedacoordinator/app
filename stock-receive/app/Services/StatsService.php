<?php
/**
 * Statistics Service
 * Provides efficient methods to get application statistics
 */

require_once __DIR__ . '/../config/database.php';

class StatsService {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Get all dashboard statistics in a single call
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Get counts efficiently in a single transaction
        $queries = [
            'vendors_count' => "SELECT COUNT(*) as count FROM vendors",
            'invoices_count' => "SELECT COUNT(*) as count FROM invoices",
            'invoice_items_count' => "SELECT COUNT(*) as count FROM invoice_items",
            'stock_recounts_count' => "SELECT COUNT(*) as count FROM stock_recounts",
            'latest_invoice' => "SELECT i.invoice_date, i.invoice_number, v.name as vendor_name, i.total_amount
                                FROM invoices i
                                JOIN vendors v ON i.vendor_id = v.id
                                ORDER BY i.invoice_date DESC LIMIT 1",
            'latest_stock_recount' => "SELECT recount_date, counted_by, notes
                                     FROM stock_recounts
                                     ORDER BY recount_date DESC LIMIT 1",
            'recent_invoices' => "SELECT i.*, v.name as vendor_name, (i.total_amount - i.discount) as net_amount
                                 FROM invoices i
                                 JOIN vendors v ON i.vendor_id = v.id
                                 ORDER BY i.invoice_date DESC
                                 LIMIT 5"
        ];
        
        foreach ($queries as $key => $sql) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($key === 'recent_invoices') {
                $stats[$key] = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $row = $result->fetch_assoc();
                $stats[$key] = $row ? $row['count'] ?? $row : null;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get vendor count
     */
    public function getVendorCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM vendors");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
    
    /**
     * Get invoice count
     */
    public function getInvoiceCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM invoices");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
    
    /**
     * Get invoice item count
     */
    public function getInvoiceItemCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM invoice_items");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
    
    /**
     * Get stock recount count
     */
    public function getStockRecountCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM stock_recounts");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
}
?>