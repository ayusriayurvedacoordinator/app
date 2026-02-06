<?php
$page_title = "View Invoice - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$id = $_GET['id'];

// Fetch invoice data
$stmt = $conn->prepare("
    SELECT i.*, v.name as vendor_name 
    FROM invoices i
    JOIN vendors v ON i.vendor_id = v.id
    WHERE i.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    header("Location: index.php");
    exit();
}

// Fetch invoice items with category information
$items_result = $conn->query("
    SELECT ii.*, c.name as category_name 
    FROM invoice_items ii
    LEFT JOIN categories c ON ii.category_id = c.id
    WHERE ii.invoice_id = $id 
    ORDER BY ii.product_name
");
?>

<h2>Invoice Details</h2>

<div class="card mb-4">
    <div class="card-header">
        <h4>Invoice Information</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Invoice Number:</strong> <?php echo $invoice['invoice_number'] ?: 'N/A'; ?></p>
                <p><strong>Vendor:</strong> <?php echo $invoice['vendor_name']; ?></p>
                <p><strong>Invoice Date:</strong> <?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Received Date:</strong> <?php echo date('M j, Y', strtotime($invoice['received_date'])); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p>
                <p><strong>Discount:</strong> $<?php echo number_format($invoice['discount'], 2); ?></p>
                <p><strong>Net Amount:</strong> $<?php echo number_format($invoice['total_amount'] - $invoice['discount'], 2); ?></p>
            </div>
        </div>
        <?php if ($invoice['notes']): ?>
            <div class="row">
                <div class="col-md-12">
                    <p><strong>Notes:</strong> <?php echo htmlspecialchars($invoice['notes']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<h3>Invoice Items</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Rate</th>
            <th>Amount</th>
            <th>Category</th>
<th>Free of Charge</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if($items_result->num_rows > 0) {
            while($item = $items_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$item['product_name']."</td>";
                echo "<td>".$item['quantity']."</td>";
                echo "<td>$".$item['rate']."</td>";
                echo "<td>$".$item['amount']."</td>";
                echo "<td>".($item['category_name'] ?: 'N/A')."</td>";
                echo "<td>".($item['is_free_of_charge'] ? 'Yes' : 'No')."</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No items found for this invoice</td></tr>";
        }
        ?>
    </tbody>
</table>

<a href="index.php" class="btn btn-secondary">Back to Invoices</a>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>