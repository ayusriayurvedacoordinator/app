<?php
$page_title = "Dashboard - Stock Receive System";
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>Stock Receive System</h1>
        <p class="lead">Track invoices from vendors and monitor product prices and costs.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Vendors</div>
            <div class="card-body">
                <?php
                include 'config/database.php';
                $result = $conn->query("SELECT COUNT(*) as count FROM vendors");
                $count = $result->fetch_assoc()['count'];
                ?>
                <h4 class="card-title"><?php echo $count; ?></h4>
                <a href="vendors/index.php" class="btn btn-light btn-sm">Manage</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Invoices</div>
            <div class="card-body">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM invoices");
                $count = $result->fetch_assoc()['count'];
                ?>
                <h4 class="card-title"><?php echo $count; ?></h4>
                <a href="invoices/index.php" class="btn btn-light btn-sm">Manage</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Invoice Items</div>
            <div class="card-body">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM invoice_items");
                $count = $result->fetch_assoc()['count'];
                ?>
                <h4 class="card-title"><?php echo $count; ?></h4>
                <a href="invoices/index.php" class="btn btn-light btn-sm">View</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Stock Recounts</div>
            <div class="card-body">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM stock_recounts");
                $count = $result->fetch_assoc()['count'];
                ?>
                <h4 class="card-title"><?php echo $count; ?></h4>
                <a href="stock_recounts/index.php" class="btn btn-light btn-sm">Manage</a>
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
                $result = $conn->query("SELECT i.invoice_date, i.invoice_number, v.name as vendor_name, i.total_amount
                                        FROM invoices i 
                                        JOIN vendors v ON i.vendor_id = v.id 
                                        ORDER BY i.invoice_date DESC LIMIT 1");
                $latest = $result->fetch_assoc();
                
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
                $result = $conn->query("SELECT recount_date, counted_by, notes
                                        FROM stock_recounts 
                                        ORDER BY recount_date DESC LIMIT 1");
                $latest = $result->fetch_assoc();
                
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
                $result = $conn->query("
                    SELECT i.*, v.name as vendor_name, (i.total_amount - i.discount) as net_amount
                    FROM invoices i
                    JOIN vendors v ON i.vendor_id = v.id
                    ORDER BY i.invoice_date DESC
                    LIMIT 5
                ");
                
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
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
?>