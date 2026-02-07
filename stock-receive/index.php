<?php
/**
 * Main Application Entry Point
 * Handles routing requests to appropriate controllers or displays dashboard
 */

// Check if we have a path parameter (from nginx rewrite rules)
$path = $_GET['path'] ?? '';

if (!empty($path)) {
    // Route the request through the dispatcher
    require_once __DIR__ . '/AppDispatcher.php';
    $dispatcher = new AppDispatcher();
    $dispatcher->dispatch($path);
} else {
    // Default to dashboard if no path specified
    /**
     * Dashboard Page
     * Main dashboard showing key metrics and recent activity
     */

    $page_title = "Dashboard - Stock Receive System";
    include 'config/database.php';
    require_once 'services/StatsService.php';
    include 'includes/header.php';

    $statsService = new StatsService();
    $dashboardStats = $statsService->getDashboardStats();
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
                    <a href="vendors/index.php" class="btn btn-outline-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Invoices</div>
                <div class="card-body">
                    <h4 class="card-title text-success"><?php echo $dashboardStats['invoices_count']; ?></h4>
                    <a href="invoices/index.php" class="btn btn-outline-success btn-sm">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">Invoice Items</div>
                <div class="card-body">
                    <h4 class="card-title text-info"><?php echo $dashboardStats['invoice_items_count']; ?></h4>
                    <a href="invoices/index.php" class="btn btn-outline-info btn-sm">View</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">Stock Recounts</div>
                <div class="card-body">
                    <h4 class="card-title text-warning"><?php echo $dashboardStats['stock_recounts_count']; ?></h4>
                    <a href="stock_recounts/index.php" class="btn btn-outline-warning btn-sm">Manage</a>
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
                    <a href="invoices/add.php" class="btn btn-primary btn-sm mt-2">Add Invoice</a>
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
                    <a href="stock_recounts/add.php" class="btn btn-warning btn-sm mt-2">Add Recount</a>
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
    include 'includes/footer.php';
}
?>