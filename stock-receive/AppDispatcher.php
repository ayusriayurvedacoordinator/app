<?php
/**
 * Application Dispatcher
 * Handles routing requests to appropriate controllers while maintaining backward compatibility
 */

class AppDispatcher
{
    public function dispatch($path)
    {
        // Parse the path to determine which controller/action to call
        $path = trim($path, '/');

        if (empty($path)) {
            // Default to dashboard - load the main dashboard content
            // Don't include index.php again as it would cause infinite recursion
            // Instead, recreate the dashboard functionality here
            $this->showDashboard();
            return;
        }

        $segments = explode('/', $path);
        $resource = $segments[0] ?? '';
        $id = $segments[1] ?? null;
        $action = $segments[2] ?? null;

        switch ($resource) {
            case 'vendors':
                $this->handleVendorRequest($id, $action);
                break;
            case 'invoices':
                $this->handleInvoiceRequest($id, $action);
                break;
            case 'stock_recounts':
                $this->handleStockRecountRequest($id, $action);
                break;
            default:
                // For now, fall back to the original structure
                $this->fallbackToOriginal($path);
        }
    }

    private function showDashboard()
    {
        // Include necessary files for dashboard
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/app/Services/StatsService.php';
        require_once __DIR__ . '/includes/header.php';

        // Get dashboard stats
        $statsService = new StatsService();
        $dashboardStats = $statsService->getDashboardStats();

        // Set page title
        $page_title = "Dashboard - Stock Receive System";
        
        // Output the dashboard HTML directly
        ?>
        <div class="row">
            <div class="col-md-12">
                <h1>Stock Receive System</h1>
                <p class="lead">Track invoices from vendors and monitor product prices and costs.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">Vendors</div>
                    <div class="card-body">
                        <h4 class="card-title text-primary"><?php echo $dashboardStats['vendors_count']; ?></h4>
                        <a href="legacy/vendors/index.php" class="btn btn-outline-primary btn-sm">Manage</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">Invoices</div>
                    <div class="card-body">
                        <h4 class="card-title text-success"><?php echo $dashboardStats['invoices_count']; ?></h4>
                        <a href="legacy/invoices/index.php" class="btn btn-outline-success btn-sm">Manage</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">Invoice Items</div>
                    <div class="card-body">
                        <h4 class="card-title text-info"><?php echo $dashboardStats['invoice_items_count']; ?></h4>
                        <a href="legacy/invoices/index.php" class="btn btn-outline-info btn-sm">View</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">Stock Recounts</div>
                    <div class="card-body">
                        <h4 class="card-title text-warning"><?php echo $dashboardStats['stock_recounts_count']; ?></h4>
                        <a href="legacy/stock_recounts/index.php" class="btn btn-outline-warning btn-sm">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Latest Invoice</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $latest = $dashboardStats['latest_invoice'];

                        if($latest) {
                            echo "<p class='card-text'>".date('M j, Y', strtotime($latest['invoice_date']))."</p>";
                            echo "<small>".$latest['vendor_name']." - Invoice: ".($latest['invoice_number'] ?: 'N/A')."</small>";
                        } else {
                            echo "<p class='card-text'>-</p>";
                            echo "<small>No invoices yet</small>";
                        }
                        ?>
                        <a href="legacy/invoices/add.php" class="btn btn-primary btn-sm mt-2">Add Invoice</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Latest Stock Recount</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $latest = $dashboardStats['latest_stock_recount'];

                        if($latest) {
                            echo "<p class='card-text'>".date('M j, Y', strtotime($latest['recount_date']))."</p>";
                            echo "<small>Counted by: ".($latest['counted_by'] ?: 'N/A')."</small>";
                        } else {
                            echo "<p class='card-text'>-</p>";
                            echo "<small>No stock recounts yet</small>";
                        }
                        ?>
                        <a href="legacy/stock_recounts/add.php" class="btn btn-warning btn-sm mt-2">Add Recount</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Recent Invoices</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Invoice #</th>
                            <th>Total Amount</th>
                            <th>Discount</th>
                            <th>Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentInvoices = $dashboardStats['recent_invoices'];

                        if(!empty($recentInvoices)) {
                            foreach($recentInvoices as $row) {
                                echo "<tr>";
                                echo "<td>".date('M j, Y', strtotime($row['invoice_date']))."</td>";
                                echo "<td>".$row['vendor_name']."</td>";
                                echo "<td>".($row['invoice_number'] ?: 'N/A')."</td>";
                                echo "<td>$".$row['total_amount']."</td>";
                                echo "<td>$".$row['discount']."</td>";
                                echo "<td>$".$row['net_amount']."</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No invoices found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        include __DIR__ . '/includes/footer.php';
    }
    
    private function handleVendorRequest($id, $action)
    {
        require_once __DIR__ . '/app/Controllers/VendorController.php';
        $controller = new VendorController();
        
        if ($id === 'create' && !$action) {
            $controller->create();
        } elseif ($id && $action === 'edit') {
            $controller->edit($id);
        } elseif ($id && $action === 'update') {
            $controller->update($id);
        } elseif ($id && $action === 'delete') {
            $controller->delete($id);
        } elseif ($id) {
            // View specific vendor
            $vendor = $controller->vendorModel->getById((int)$id);
            include __DIR__ . '/app/Views/vendors/show.php';
        } else {
            $controller->index();
        }
    }
    
    private function handleInvoiceRequest($id, $action)
    {
        // For now, delegate to original invoice pages in legacy directory
        if ($id === 'create' && !$action) {
            include __DIR__ . '/legacy/invoices/add.php';
        } else {
            include __DIR__ . '/legacy/invoices/index.php';
        }
    }
    
    private function handleStockRecountRequest($id, $action)
    {
        // For now, delegate to original stock recount pages in legacy directory
        if ($id === 'create' && !$action) {
            include __DIR__ . '/legacy/stock_recounts/add.php';
        } else {
            include __DIR__ . '/legacy/stock_recounts/index.php';
        }
    }
    
    private function fallbackToOriginal($path)
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Try to include the original file if it exists
        $originalFile = __DIR__ . '/' . $path . '.php';
        
        // Special handling for vendor routes to redirect to new MVC structure
        if (strpos($path, 'vendors') === 0) {
            // Extract vendor ID and action if present
            $parts = explode('/', $path);
            $id = isset($parts[1]) && is_numeric($parts[1]) ? $parts[1] : null;
            $action = isset($parts[2]) ? $parts[2] : null;
            
            // Route to new controller
            $this->handleVendorRequest($id, $action);
            return;
        }
        
        // Check if it's a legacy feature route (from legacy/ directory)
        $legacyFile = __DIR__ . '/legacy/' . $path . '.php';
        if (file_exists($legacyFile)) {
            include $legacyFile;
            return;
        }
        
        if (file_exists($originalFile)) {
            include $originalFile;
        } else {
            // Show 404
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}