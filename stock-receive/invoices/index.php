<?php
$page_title = "Invoices - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Handle delete request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get the invoice data before deletion for audit trail
    $get_stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
    $get_stmt->bind_param("i", $id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $invoice = $result->fetch_assoc();
    $get_stmt->close();
    
    if($invoice) {
        // Log the deletion to audit trail
        log_delete('invoices', $id, $invoice);
        
        // Delete associated items first
        $del_items_stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $del_items_stmt->bind_param("i", $id);
        $del_items_stmt->execute();
        $del_items_stmt->close();
        
        // Then delete the invoice
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Invoice deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error deleting invoice: '.$conn->error.'</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Invoice not found.</div>';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Invoices</h2>
    <a href="add.php" class="btn btn-primary">Add New Invoice</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Invoice Number</th>
            <th>Vendor</th>
            <th>Invoice Date</th>
            <th>Received Date</th>
            <th>Total Amount</th>
            <th>Discount</th>
            <th>Net Amount</th>
<th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("
            SELECT i.*, v.name as vendor_name, (i.total_amount - i.discount) as net_amount
            FROM invoices i
            JOIN vendors v ON i.vendor_id = v.id
            ORDER BY i.invoice_date DESC
        ");
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".($row['invoice_number'] ?: 'N/A')."</td>";
                echo "<td>".$row['vendor_name']."</td>";
                echo "<td>".date('M j, Y', strtotime($row['invoice_date']))."</td>";
                echo "<td>".date('M j, Y', strtotime($row['received_date']))."</td>";
                echo "<td>$".$row['total_amount']."</td>";
                echo "<td>$".$row['discount']."</td>";
                echo "<td>$".$row['net_amount']."</td>";
                echo "<td>
                        <a href='view.php?id=".$row['id']."' class='btn btn-sm btn-outline-info'>View</a>
                        <a href='edit.php?id=".$row['id']."' class='btn btn-sm btn-outline-primary'>Edit</a>
                        <a href='audit.php?id=".$row['id']."' class='btn btn-sm btn-outline-secondary'>Audit</a>
                        <a href='?delete=".$row['id']."' class='btn btn-sm btn-outline-danger' onclick='return confirmDelete();'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='text-center'>No invoices found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>