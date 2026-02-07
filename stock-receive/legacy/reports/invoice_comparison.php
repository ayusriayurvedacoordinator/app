<?php
$page_title = "Invoice Comparison Report - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory
?>

<h2>Invoice Comparison Report</h2>
<p>This report shows price comparisons for the same products from different vendors.</p>

<table class="table table-striped report-table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Vendor</th>
            <th>Rate</th>
            <th>Invoice Date</th>
            <th>Invoice Number</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Query to get product comparison across vendors
        $sql = "
            SELECT ii.product_name, v.name as vendor_name, ii.rate, i.invoice_date, i.invoice_number
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            JOIN vendors v ON i.vendor_id = v.id
            WHERE ii.is_free_of_charge = 0  -- Exclude free items from comparison
            ORDER BY ii.product_name, ii.rate ASC
        ";
        
        $result = $conn->query($sql);
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['product_name']."</td>";
                echo "<td>".$row['vendor_name']."</td>";
                echo "<td>$".$row['rate']."</td>";
                echo "<td>".date('M j, Y', strtotime($row['invoice_date']))."</td>";
                echo "<td>".($row['invoice_number'] ?: 'N/A')."</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No data available</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>