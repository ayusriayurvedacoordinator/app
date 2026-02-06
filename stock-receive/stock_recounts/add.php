<?php
$page_title = "Add Stock Recount - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recount_date = $_POST['recount_date'] ?? date('Y-m-d');
    $counted_by = trim($_POST['counted_by'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Get the submitted items
    $submitted_product_names = $_POST['product_name'] ?? [];
    $submitted_counted_quantities = $_POST['counted_quantity'] ?? [];
    
    // Validate required fields
    if ($recount_date && count(array_filter($submitted_product_names)) > 0) {
        // Start transaction
        $conn->autocommit(FALSE);
        
        try {
            // Insert the recount record
            $stmt = $conn->prepare("INSERT INTO stock_recounts (recount_date, counted_by, notes) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $recount_date, $counted_by, $notes);
            
            if ($stmt->execute()) {
                $recount_id = $conn->insert_id;
                
                // Log the insert to audit trail
                $new_recount_data = [
                    'recount_date' => $recount_date,
                    'counted_by' => $counted_by,
                    'notes' => $notes
                ];
                log_insert('stock_recounts', $recount_id, $new_recount_data);
                
                // Process recount items
                for ($i = 0; $i < count($submitted_product_names); $i++) {
                    $product_name = trim($submitted_product_names[$i]);
                    if (!empty($product_name)) {
                        $counted_quantity = intval($submitted_counted_quantities[$i] ?? 0);
                        
                        $item_stmt = $conn->prepare("INSERT INTO stock_recount_items (recount_id, product_name, counted_quantity) VALUES (?, ?, ?)");
                        $item_stmt->bind_param("isi", $recount_id, $product_name, $counted_quantity);
                        
                        if (!$item_stmt->execute()) {
                            throw new Exception("Error inserting recount item: " . $item_stmt->error);
                        }
                        
                        // Log the item insert to audit trail
                        $new_item_data = [
                            'recount_id' => $recount_id,
                            'product_name' => $product_name,
                            'counted_quantity' => $counted_quantity
                        ];
                        log_insert('stock_recount_items', $conn->insert_id, $new_item_data);
                        
                        $item_stmt->close();
                        
                        // Update the actual invoice_items quantities based on recount
                        $update_stmt = $conn->prepare("
                            UPDATE invoice_items 
                            SET quantity = ? 
                            WHERE product_name = ? 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        ");
                        $update_stmt->bind_param("is", $counted_quantity, $product_name);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Redirect to the newly created recount view page
                header("Location: view.php?id=" . $recount_id);
                exit();
                
            } else {
                throw new Exception("Error inserting recount: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
        
        $conn->autocommit(TRUE);
    } else {
        $message = "Recount date and at least one item must be provided.";
        $message_type = "warning";
    }
} else {
    // Set default date to today
    $recount_date = date('Y-m-d');
    $submitted_product_names = [''];
    $submitted_counted_quantities = [''];
}
?>

<h2>Add New Stock Recount</h2>

<?php if ($message != ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<form method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="recount_date" class="form-label">Recount Date *</label>
                <input type="date" class="form-control" id="recount_date" name="recount_date" value="<?php echo $recount_date; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="counted_by" class="form-label">Counted By</label>
                <input type="text" class="form-control" id="counted_by" name="counted_by" value="<?php echo htmlspecialchars($counted_by); ?>" placeholder="Person who conducted the recount">
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($notes); ?></textarea>
            </div>
        </div>
    </div>
    
    <hr>
    <h4>Stock Count Items</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Product Name</th>
                    <th>Current Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="recount-items">
                <?php 
                // Display submitted items or initialize with one empty row
                $item_count = max(count($submitted_product_names), 1);
                for ($i = 0; $i < $item_count; $i++) {
                    $product_name = $submitted_product_names[$i] ?? '';
                    $counted_quantity = $submitted_counted_quantities[$i] ?? '';
                ?>
                <tr class="item-row">
                    <td>
                        <input type="text" class="form-control product-name" name="product_name[]" value="<?php echo htmlspecialchars($product_name); ?>" placeholder="Product Name" required>
                    </td>
                    <td>
                        <input type="number" class="form-control counted-quantity" name="counted_quantity[]" value="<?php echo $counted_quantity; ?>" placeholder="Current Qty" min="0" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">Remove</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-secondary mt-2" id="add-item">Add Another Item</button>
    
    <hr>
    <button type="submit" class="btn btn-primary">Add Stock Recount</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add another item row
    document.getElementById('add-item').addEventListener('click', function() {
        const tbody = document.getElementById('recount-items');
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control product-name" name="product_name[]" placeholder="Product Name" required>
            </td>
            <td>
                <input type="number" class="form-control counted-quantity" name="counted_quantity[]" placeholder="Current Qty" min="0" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">Remove</button>
            </td>
        `;
        tbody.appendChild(newRow);
        
        // Add event listeners to the new elements
        newRow.querySelector('.remove-item').addEventListener('click', removeItem);
    });
    
    // Add event listeners to existing items
    document.querySelectorAll('.item-row').forEach(function(row) {
        row.querySelector('.remove-item').addEventListener('click', removeItem);
    });
    
    function removeItem(e) {
        const row = e.target.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
        } else {
            alert('At least one item is required.');
        }
    }
});
</script>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>