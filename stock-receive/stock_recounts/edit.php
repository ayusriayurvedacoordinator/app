<?php
$page_title = "Edit Stock Recount - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$id = $_GET['id'];

// Fetch recount data
$stmt = $conn->prepare("SELECT * FROM stock_recounts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recount = $result->fetch_assoc();

if (!$recount) {
    header("Location: index.php");
    exit();
}

// Fetch recount items
$items_result = $conn->query("SELECT * FROM stock_recount_items WHERE recount_id = $id ORDER BY product_name");

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recount_date = $_POST['recount_date'] ?? $recount['recount_date'];
    $counted_by = trim($_POST['counted_by'] ?? $recount['counted_by']);
    $notes = trim($_POST['notes'] ?? $recount['notes']);
    
    // Get old values for audit trail
    $old_recount_data = [
        'recount_date' => $recount['recount_date'],
        'counted_by' => $recount['counted_by'],
        'notes' => $recount['notes']
    ];
    
    // Get submitted items
    $submitted_product_names = $_POST['product_name'] ?? [];
    $submitted_counted_quantities = $_POST['counted_quantity'] ?? [];
    
    // Validate required fields
    if ($recount_date && count(array_filter($submitted_product_names)) > 0) {
        // Start transaction
        $conn->autocommit(FALSE);
        
        try {
            // Update the recount record
            $stmt = $conn->prepare("UPDATE stock_recounts SET recount_date=?, counted_by=?, notes=? WHERE id=?");
            $stmt->bind_param("sssi", $recount_date, $counted_by, $notes, $id);
            
            if ($stmt->execute()) {
                // Log the update to audit trail
                $new_recount_data = [
                    'recount_date' => $recount_date,
                    'counted_by' => $counted_by,
                    'notes' => $notes
                ];
                log_update('stock_recounts', $id, $old_recount_data, $new_recount_data);
                
                // Get old items for audit trail
                $old_items_result = $conn->query("SELECT * FROM stock_recount_items WHERE recount_id = $id ORDER BY product_name");
                $old_items = [];
                while ($old_item = $old_items_result->fetch_assoc()) {
                    $old_items[] = $old_item;
                }
                
                // Delete existing items
                $delete_stmt = $conn->prepare("DELETE FROM stock_recount_items WHERE recount_id = ?");
                $delete_stmt->bind_param("i", $id);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                // Add updated items
                for ($i = 0; $i < count($submitted_product_names); $i++) {
                    $product_name = trim($submitted_product_names[$i]);
                    if (!empty($product_name)) {
                        $counted_quantity = intval($submitted_counted_quantities[$i] ?? 0);
                        
                        $item_stmt = $conn->prepare("INSERT INTO stock_recount_items (recount_id, product_name, counted_quantity) VALUES (?, ?, ?)");
                        $item_stmt->bind_param("isi", $id, $product_name, $counted_quantity);
                        
                        if (!$item_stmt->execute()) {
                            throw new Exception("Error inserting recount item: " . $item_stmt->error);
                        }
                        
                        // Log the item insert to audit trail
                        $new_item_data = [
                            'recount_id' => $id,
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
                
                // Redirect to the updated recount view page
                header("Location: view.php?id=" . $id);
                exit();
                
            } else {
                throw new Exception("Error updating recount: " . $stmt->error);
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
    // Set default values from the database if not a POST request
    $recount_date = $recount['recount_date'];
    $counted_by = $recount['counted_by'];
    $notes = $recount['notes'];
    
    // Get items for display
    $items_result = $conn->query("SELECT * FROM stock_recount_items WHERE recount_id = $id ORDER BY product_name");
    $submitted_product_names = [];
    $submitted_counted_quantities = [];
    
    while ($item = $items_result->fetch_assoc()) {
        $submitted_product_names[] = $item['product_name'];
        $submitted_counted_quantities[] = $item['counted_quantity'];
    }
}
?>

<h2>Edit Stock Recount</h2>

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
                // Display submitted items or existing items
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
    <button type="submit" class="btn btn-primary">Update Stock Recount</button>
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