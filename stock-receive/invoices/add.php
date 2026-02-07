<?php
$page_title = "Add Invoice - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/audit_helper.php'; // Include audit helper functions
include '../includes/header.php'; // Include header from parent directory

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vendor_id = $_POST['vendor_id'] ?? 0;
    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $received_date = $_POST['received_date'] ?? date('Y-m-d');
    $total_amount = $_POST['total_amount'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $notes = trim($_POST['notes'] ?? '');

    // Get the submitted items
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

    // Validate date formats
    $date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($date_pattern, $invoice_date) || !preg_match($date_pattern, $received_date)) {
        $message = "Invalid date format. Please use YYYY-MM-DD format.";
        $message_type = "danger";
    } else if ($vendor_id && $invoice_date && $received_date && $total_amount >= 0) {
        $stmt = $conn->prepare("INSERT INTO invoices (vendor_id, invoice_number, invoice_date, total_amount, discount, received_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddss", $vendor_id, $invoice_number, $invoice_date, $total_amount, $discount, $received_date, $notes);
        
        if ($stmt->execute()) {
            $invoice_id = $conn->insert_id;
            
            // Log the insert to audit trail
            $new_invoice_data = [
                'vendor_id' => $vendor_id,
                'invoice_number' => $invoice_number,
                'invoice_date' => $invoice_date,
                'total_amount' => $total_amount,
                'discount' => $discount,
                'received_date' => $received_date,
                'notes' => $notes
            ];
            log_insert('invoices', $invoice_id, $new_invoice_data);
            
            // Process invoice items with validation
            $has_errors = false;
            $error_messages = [];

            for ($i = 0; $i < count($submitted_product_names); $i++) {
                $product_name = trim($submitted_product_names[$i]);
                if (!empty($product_name)) {
                    $quantity = $submitted_quantities[$i] ?? 0;
                    $rate = $submitted_rates[$i] ?? 0;
                    $amount = $submitted_amounts[$i] ?? 0;
                    $is_foc = isset($submitted_is_foc[$i]) ? 1 : 0;
                    $category_id = $submitted_category_ids[$i] ?? null;

                    // Validate quantity
                    if ($quantity <= 0) {
                        $has_errors = true;
                        $error_messages[] = "Quantity for '$product_name' must be greater than 0.";
                        continue;
                    }

                    $item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_name, quantity, rate, amount, is_free_of_charge, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $item_stmt->bind_param("isiddii", $invoice_id, $product_name, $quantity, $rate, $amount, $is_foc, $category_id);

                    if (!$item_stmt->execute()) {
                        $has_errors = true;
                        $error_messages[] = "Error adding item '$product_name': " . $item_stmt->error;
                    } else {
                        // Log the item insert to audit trail
                        $new_item_data = [
                            'invoice_id' => $invoice_id,
                            'product_name' => $product_name,
                            'quantity' => $quantity,
                            'rate' => $rate,
                            'amount' => $amount,
                            'is_free_of_charge' => $is_foc,
                            'category_id' => $category_id
                        ];
                        log_insert('invoice_items', $conn->insert_id, $new_item_data);
                    }

                    $item_stmt->close();
                }
            }

            if (!$has_errors) {
                $message = "Invoice added successfully! Invoice ID: " . $invoice_id;
                $message_type = "success";
                
                // Clear form values after successful submission
                $vendor_id = 0;
                $invoice_number = '';
                $invoice_date = $received_date = date('Y-m-d');
                $total_amount = $discount = 0;
                $notes = '';
                $submitted_product_names = [''];
                $submitted_quantities = [''];
                $submitted_rates = [''];
                $submitted_amounts = [''];
                $submitted_is_foc = [0];
                $submitted_category_ids = [0];
            } else {
                $message = implode('<br>', $error_messages);
                $message_type = "danger";
            }
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
    // Set default values for GET request or preserve POST values on validation error
    $vendor_id = $_POST['vendor_id'] ?? 0;
    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $received_date = $_POST['received_date'] ?? date('Y-m-d');
    $total_amount = $_POST['total_amount'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $notes = trim($_POST['notes'] ?? '');

    // Get the submitted items
    $submitted_product_names = $_POST['product_name'] ?? [];
    $submitted_quantities = $_POST['quantity'] ?? [];
    $submitted_rates = $_POST['rate'] ?? [];
    $submitted_amounts = $_POST['amount'] ?? [];
    $submitted_is_foc = $_POST['is_foc'] ?? [];
    $submitted_category_ids = $_POST['category_id'] ?? [];

    // Initialize with one empty row if no items were submitted
    if (empty($submitted_product_names)) {
        $submitted_product_names = [''];
        $submitted_quantities = [''];
        $submitted_rates = [''];
        $submitted_amounts = [''];
        $submitted_is_foc = [0];
        $submitted_category_ids = [0];
    }
}
?>

<h2>Add New Invoice</h2>

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
        // Display submitted items or initialize with one empty row
        $item_count = max(count($submitted_product_names), 1);
        for ($i = 0; $i < $item_count; $i++) {
            $product_name = $submitted_product_names[$i] ?? '';
            $quantity = $submitted_quantities[$i] ?? '';
            $rate = $submitted_rates[$i] ?? '';
            $amount = $submitted_amounts[$i] ?? '';
            $is_foc = $submitted_is_foc[$i] ?? 0;
            $category_id = $submitted_category_ids[$i] ?? 0;
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
    <button type="submit" class="btn btn-primary">Add Invoice</button>
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
            recalculateTotal();
        } else {
            alert('At least one item is required.');
        }
    }
    
    // Recalculate total amount based on all items
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
    
    // Add event listener to discount field to recalculate total
    const discountField = document.getElementById('discount');
    if (discountField) {
        discountField.addEventListener('input', recalculateTotal);
    }
    
    // Initialize total calculation on page load
    recalculateTotal();
});
</script>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>