<?php
$page_title = "Edit Invoice - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/audit_helper.php'; // Include audit helper functions
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

// Fetch invoice items
$items_result = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY product_name");

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vendor_id = $_POST['vendor_id'] ?? $invoice['vendor_id'];
    $invoice_number = trim($_POST['invoice_number'] ?? $invoice['invoice_number']);
    $invoice_date = $_POST['invoice_date'] ?? $invoice['invoice_date'];
    $received_date = $_POST['received_date'] ?? $invoice['received_date'];
    $total_amount = $_POST['total_amount'] ?? $invoice['total_amount'];
    $discount = $_POST['discount'] ?? $invoice['discount'];
    $notes = trim($_POST['notes'] ?? $invoice['notes']);
    
    // Get old values for audit trail
    $old_invoice_data = [
        'vendor_id' => $invoice['vendor_id'],
        'invoice_number' => $invoice['invoice_number'],
        'invoice_date' => $invoice['invoice_date'],
        'total_amount' => $invoice['total_amount'],
        'discount' => $invoice['discount'],
        'received_date' => $invoice['received_date'],
        'notes' => $invoice['notes']
    ];
    
    // Get submitted items
    $submitted_product_names = $_POST['product_name'] ?? [];
    $submitted_quantities = $_POST['quantity'] ?? [];
    $submitted_rates = $_POST['rate'] ?? [];
    $submitted_amounts = $_POST['amount'] ?? [];
    $submitted_is_foc = $_POST['is_foc'] ?? [];
    $submitted_category_ids = $_POST['category_id'] ?? [];

    // Calculate total amount from items instead of form submission
    $calculated_total = 0;
    foreach ($submitted_amounts as $amount) {
        $calculated_total += floatval($amount);
    }
    $total_amount = $calculated_total;

    // Validate required fields
    if ($vendor_id && $invoice_date && $received_date && $total_amount >= 0) {
        $stmt = $conn->prepare("UPDATE invoices SET vendor_id=?, invoice_number=?, invoice_date=?, total_amount=?, discount=?, received_date=?, notes=? WHERE id=?");
        $stmt->bind_param("isddsssi", $vendor_id, $invoice_number, $invoice_date, $total_amount, $discount, $received_date, $notes, $id);
        
        if ($stmt->execute()) {
            // Log the update to audit trail
            $new_invoice_data = [
                'vendor_id' => $vendor_id,
                'invoice_number' => $invoice_number,
                'invoice_date' => $invoice_date,
                'total_amount' => $total_amount,
                'discount' => $discount,
                'received_date' => $received_date,
                'notes' => $notes
            ];
            log_update('invoices', $id, $old_invoice_data, $new_invoice_data);
            
            // Get old items for audit trail
            $old_items_result = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY product_name");
            $old_items = [];
            while ($old_item = $old_items_result->fetch_assoc()) {
                $old_items[] = $old_item;
            }
            
            // Delete existing items
            $delete_stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            // Add updated items
            for ($i = 0; $i < count($submitted_product_names); $i++) {
                $product_name = trim($submitted_product_names[$i]);
                if (!empty($product_name)) {
                    $quantity = $submitted_quantities[$i] ?? 0;
                    $rate = $submitted_rates[$i] ?? 0;
                    $amount = $submitted_amounts[$i] ?? 0;
                    $is_foc = isset($submitted_is_foc[$i]) ? 1 : 0;
                    $category_id = $submitted_category_ids[$i] ?? null;
                    
                    $item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_name, quantity, rate, amount, is_free_of_charge, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $item_stmt->bind_param("isiddddi", $id, $product_name, $quantity, $rate, $amount, $is_foc, $category_id);
                    $item_stmt->execute();
                    
                    // Log the item insert to audit trail
                    $new_item_data = [
                        'invoice_id' => $id,
                        'product_name' => $product_name,
                        'quantity' => $quantity,
                        'rate' => $rate,
                        'amount' => $amount,
                        'is_free_of_charge' => $is_foc,
                        'category_id' => $category_id
                    ];
                    log_insert('invoice_items', $conn->insert_id, $new_item_data);
                    
                    $item_stmt->close();
                }
            }
            
            $message = "Invoice updated successfully!";
            $message_type = "success";
            
            // Refresh invoice data
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
            
            // Refresh items
            $items_result = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY product_name");
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "All required fields must be filled.";
        $message_type = "warning";
    }
} else {
    // Set default values from the database if not a POST request
    $vendor_id = $invoice['vendor_id'];
    $invoice_number = $invoice['invoice_number'];
    $invoice_date = $invoice['invoice_date'];
    $received_date = $invoice['received_date'];
    $total_amount = $invoice['total_amount'];
    $discount = $invoice['discount'];
    $notes = $invoice['notes'];
    
    // Get items for display
    $items_result = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY product_name");
    $submitted_product_names = [];
    $submitted_quantities = [];
    $submitted_rates = [];
    $submitted_amounts = [];
    $submitted_is_foc = [];
    $submitted_category_ids = [];
    
    while ($item = $items_result->fetch_assoc()) {
        $submitted_product_names[] = $item['product_name'];
        $submitted_quantities[] = $item['quantity'];
        $submitted_rates[] = $item['rate'];
        $submitted_amounts[] = $item['amount'];
        $submitted_is_foc[] = $item['is_free_of_charge'];
        $submitted_category_ids[] = $item['category_id'];
    }
}
?>

<h2>Edit Invoice</h2>

<?php if ($message != ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<form method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="vendor_id" class="form-label">Vendor *</label>
                <select class="form-select" id="vendor_id" name="vendor_id" required>
                    <option value="">Select Vendor</option>
                    <?php
                    $vendors = $conn->query("SELECT * FROM vendors ORDER BY name ASC");
                    while($vendor = $vendors->fetch_assoc()) {
                        $selected = ($vendor_id == $vendor['id']) ? 'selected' : '';
                        echo "<option value='".$vendor['id']."' $selected>".$vendor['name']."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="invoice_number" class="form-label">Invoice Number</label>
                <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?php echo htmlspecialchars($invoice_number); ?>" placeholder="e.g., INV-001">
            </div>
            
            <div class="mb-3">
                <label for="invoice_date" class="form-label">Invoice Date *</label>
                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?php echo $invoice_date; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="received_date" class="form-label">Received Date *</label>
                <input type="date" class="form-control" id="received_date" name="received_date" value="<?php echo $received_date; ?>" required>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="total_amount" class="form-label">Calculated Total Amount ($)</label>
                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label for="discount" class="form-label">Discount ($) </label>
                <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="<?php echo $discount; ?>">
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($notes); ?></textarea>
            </div>
        </div>
    </div>
    
    <hr>
    <h4>Invoice Items</h4>
    <div id="invoice-items">
        <?php 
        // Display submitted items or existing items
        $item_count = max(count($submitted_product_names), 1);
        for ($i = 0; $i < $item_count; $i++) {
            $product_name = $submitted_product_names[$i] ?? '';
            $quantity = $submitted_quantities[$i] ?? '';
            $rate = $submitted_rates[$i] ?? '';
            $amount = $submitted_amounts[$i] ?? '';
            $is_foc = $submitted_is_foc[$i] ?? 0;
        ?>
        <div class="item-row row mb-2">
            <div class="col-md-3">
                <input type="text" class="form-control product-name" name="product_name[]" value="<?php echo htmlspecialchars($product_name); ?>" placeholder="Product Name" required>
            </div>
            <div class="col-md-2">
                <select class="form-select category-select" name="category_id[]">
                    <option value="">Select Category</option>
                    <?php
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                    while($category = $categories->fetch_assoc()) {
                        $selected = ($category_id == $category['id']) ? 'selected' : '';
                        echo "<option value='".$category['id']."' $selected>".$category['name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control quantity" name="quantity[]" value="<?php echo $quantity; ?>" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control rate" name="rate[]" value="<?php echo $rate; ?>" placeholder="Rate (LKR)" min="0" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control amount" name="amount[]" value="<?php echo $amount; ?>" placeholder="Amount (LKR)" min="0" readonly>
            </div>
            <div class="col-md-1">
                <div class="form-check">
                    <input class="form-check-input is-foc" type="checkbox" name="is_foc[]" value="1" <?php echo $is_foc ? 'checked' : ''; ?>>
                    <label class="form-check-label">FOC</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1">Remove</button>
            </div>
        </div>
        <?php } ?>
    </div>
    <button type="button" class="btn btn-secondary mt-2" id="add-item">Add Another Item</button>
    
    <hr>
    <button type="submit" class="btn btn-primary">Update Invoice</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add another item row
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('invoice-items');
        const newRow = document.createElement('div');
        newRow.className = 'item-row row mb-2';
        newRow.innerHTML = `
            <div class="col-md-3">
                <input type="text" class="form-control product-name" name="product_name[]" placeholder="Product Name" required>
            </div>
            <div class="col-md-2">
                <select class="form-select category-select" name="category_id[]">
                    <option value="">Select Category</option>
                    <?php
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                    while($category = $categories->fetch_assoc()) {
                        echo "<option value='".$category['id']."'>".$category['name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control quantity" name="quantity[]" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control rate" name="rate[]" placeholder="Rate (LKR)" min="0" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="Amount (LKR)" min="0" readonly>
            </div>
            <div class="col-md-1">
                <div class="form-check">
                    <input class="form-check-input is-foc" type="checkbox" name="is_foc[]" value="1">
                    <label class="form-check-label">FOC</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1">Remove</button>
            </div>
        `;
        container.appendChild(newRow);
        
        // Add event listeners to the new elements
        newRow.querySelector('.quantity').addEventListener('input', calculateAmount);
        newRow.querySelector('.rate').addEventListener('input', calculateAmount);
        newRow.querySelector('.is-foc').addEventListener('change', toggleFoc);
        newRow.querySelector('.remove-item').addEventListener('click', removeItem);
    });
    
    // Add event listeners to existing items
    document.querySelectorAll('.item-row').forEach(function(row) {
        row.querySelector('.quantity').addEventListener('input', calculateAmount);
        row.querySelector('.rate').addEventListener('input', calculateAmount);
        row.querySelector('.is-foc').addEventListener('change', toggleFoc);
        row.querySelector('.remove-item').addEventListener('click', removeItem);
    });
    
    // Add event listener to discount field to recalculate total
    const discountField = document.getElementById('discount');
    if (discountField) {
        discountField.addEventListener('input', recalculateTotal);
    }
    
    function calculateAmount(e) {
        const row = e.target.closest('.item-row');
        const qty = parseFloat(row.querySelector('.quantity').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const amountField = row.querySelector('.amount');
        const isFoc = row.querySelector('.is-foc').checked;

        if (isFoc) {
            amountField.value = 0;
        } else {
            amountField.value = (qty * rate).toFixed(2);
        }

        // Recalculate total amount
        recalculateTotal();
    }
    
    function toggleFoc(e) {
        const row = e.target.closest('.item-row');
        const qty = parseFloat(row.querySelector('.quantity').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const amountField = row.querySelector('.amount');

        if (e.target.checked) {
            amountField.value = 0;
        } else {
            amountField.value = (qty * rate).toFixed(2);
        }

        // Recalculate total amount
        recalculateTotal();
    }
    
    function removeItem(e) {
        const row = e.target.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            // Recalculate total amount
            recalculateTotal();
        } else {
            alert('At least one item is required.');
        }
    }
    
    function recalculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            const amount = parseFloat(row.querySelector('.amount').value) || 0;
            subtotal += amount;
        });
        
        // Apply discount if any
        const discount = parseFloat(document.getElementById('discount')?.value) || 0;
        const finalTotal = subtotal - discount;
        
        // Update the total amount field
        const totalField = document.getElementById('total_amount');
        if (totalField) {
            totalField.value = finalTotal.toFixed(2);
        }
    }
    
    // Initialize total calculation on page load
    recalculateTotal();
});
</script>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>