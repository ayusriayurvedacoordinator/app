<?php
$page_title = "Invoice Analysis Report - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Get all vendors for the dropdown
$vendors_result = $conn->query("SELECT * FROM vendors ORDER BY name ASC");
?>

<h2>Invoice Analysis Report</h2>
<p>This report shows invoice details and analysis for selected vendors.</p>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <label for="vendor_id" class="form-label">Select Vendor:</label>
            <select name="vendor_id" id="vendor_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select a Vendor --</option>
                <?php
                while($vendor = $vendors_result->fetch_assoc()) {
                    $selected = (isset($_GET['vendor_id']) && $_GET['vendor_id'] == $vendor['id']) ? 'selected' : '';
                    echo "<option value='".$vendor['id']."' $selected>".$vendor['name']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
</form>

<?php if(isset($_GET['vendor_id']) && !empty($_GET['vendor_id'])): ?>
    <h4>Invoices for: 
        <?php 
        $vendor_query = $conn->prepare("SELECT name FROM vendors WHERE id = ?");
        $vendor_query->bind_param("i", $_GET['vendor_id']);
        $vendor_query->execute();
        $vendor_result = $vendor_query->get_result();
        $vendor = $vendor_result->fetch_assoc();
        echo $vendor['name'];
        ?>
    </h4>
    
    <table class="table table-striped report-table">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
                <th>Received Date</th>
                <th>Total Amount</th>
                <th>Discount</th>
                <th>Net Amount</th>
                <th>Items Count</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $analysis_sql = "
                SELECT i.*, 
                       (SELECT COUNT(*) FROM invoice_items WHERE invoice_id = i.id) as items_count,
                       (i.total_amount - i.discount) as net_amount
                FROM invoices i
                WHERE i.vendor_id = ?
                ORDER BY i.invoice_date DESC
            ";
            
            $analysis_stmt = $conn->prepare($analysis_sql);
            $analysis_stmt->bind_param("i", $_GET['vendor_id']);
            $analysis_stmt->execute();
            $analysis_result = $analysis_stmt->get_result();
            
            if($analysis_result->num_rows > 0) {
                while($row = $analysis_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".($row['invoice_number'] ?: 'N/A')."</td>";
                    echo "<td>".date('M j, Y', strtotime($row['invoice_date']))."</td>";
                    echo "<td>".date('M j, Y', strtotime($row['received_date']))."</td>";
                    echo "<td>$".$row['total_amount']."</td>";
                    echo "<td>$".$row['discount']."</td>";
                    echo "<td>$".$row['net_amount']."</td>";
                    echo "<td>".$row['items_count']."</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No invoices found for this vendor</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <h4>Products from this Vendor</h4>
    <table class="table table-striped report-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Free of Charge</th>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $products_sql = "
                SELECT ii.*, i.invoice_number, i.invoice_date
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.vendor_id = ?
                ORDER BY ii.product_name
            ";
            
            $products_stmt = $conn->prepare($products_sql);
            $products_stmt->bind_param("i", $_GET['vendor_id']);
            $products_stmt->execute();
            $products_result = $products_stmt->get_result();
            
            if($products_result->num_rows > 0) {
                while($row = $products_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".$row['product_name']."</td>";
                    echo "<td>".$row['quantity']."</td>";
                    echo "<td>$".$row['rate']."</td>";
                    echo "<td>$".$row['amount']."</td>";
                    echo "<td>".($row['is_free_of_charge'] ? 'Yes' : 'No')."</td>";
                    echo "<td>".($row['invoice_number'] ?: 'N/A')."</td>";
                    echo "<td>".date('M j, Y', strtotime($row['invoice_date']))."</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No products found for this vendor</td></tr>";
            }
            ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">Please select a vendor to view its invoice analysis.</div>
<?php endif; ?>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>