<?php
// Capture the content of this view
ob_start();

$page_title = $page_title ?? 'View Vendor - Stock Receive System';
?>

<h2>Vendor Details</h2>

<?php if (isset($vendor)): ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($vendor['name']); ?></h5>
            <p class="card-text"><strong>Phone:</strong> <?php echo htmlspecialchars($vendor['phone_number'] ?? 'N/A'); ?></p>
            <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($vendor['email'] ?? 'N/A'); ?></p>
            <p class="card-text"><strong>Address:</strong> <?php echo htmlspecialchars($vendor['address'] ?? 'N/A'); ?></p>
            <p class="card-text"><small class="text-muted">Created: <?php echo htmlspecialchars($vendor['created_at']); ?></small></p>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="/stock-receive/vendors/<?php echo $vendor['id']; ?>/edit" class="btn btn-primary">Edit</a>
        <a href="/stock-receive/vendors" class="btn btn-secondary">Back to Vendors</a>
    </div>
<?php else: ?>
    <p>Vendor not found.</p>
    <a href="/stock-receive/vendors" class="btn btn-secondary">Back to Vendors</a>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/views/layout.php';