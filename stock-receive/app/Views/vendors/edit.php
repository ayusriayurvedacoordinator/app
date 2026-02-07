<?php
// Capture the content of this view
ob_start();

$page_title = $page_title ?? 'Edit Vendor - Stock Receive System';
?>

<h2>Edit Vendor</h2>

<?php if (isset($vendor)): ?>
    <form method="post" action="/stock-receive/vendors/<?php echo $vendor['id']; ?>/update">
        <?php require_once __DIR__ . '/../../includes/csrf.php'; ?>
        <?php echo csrf_input_field(); ?>
        
        <div class="mb-3">
            <label for="name" class="form-label">Vendor Name *</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($vendor['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number *</label>
            <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($vendor['phone_number'] ?? ''); ?>" placeholder="e.g., 0771234567" required>
            <div class="form-text">Phone number must be 10 digits and start with 0</div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($vendor['email'] ?? ''); ?>" placeholder="e.g., vendor@example.com">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($vendor['address'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Vendor</button>
        <a href="/stock-receive/vendors" class="btn btn-secondary">Cancel</a>
    </form>
<?php else: ?>
    <p>Vendor not found.</p>
    <a href="/stock-receive/vendors" class="btn btn-secondary">Back to Vendors</a>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/../layout.php';