<?php
// Capture the content of this view
ob_start();

$page_title = $page_title ?? 'Vendors - Stock Receive System';
?>

<h2>Vendors</h2>

<a href="/stock-receive/vendors/create" class="btn btn-primary mb-3">Add New Vendor</a>

<?php if (empty($vendors)): ?>
    <p>No vendors found.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendors as $vendor): ?>
                <tr>
                    <td><?php echo $vendor['id']; ?></td>
                    <td><?php echo htmlspecialchars($vendor['name']); ?></td>
                    <td><?php echo htmlspecialchars($vendor['phone_number'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($vendor['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($vendor['address'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($vendor['created_at']); ?></td>
                    <td>
                        <a href="/stock-receive/vendors/<?php echo $vendor['id']; ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="#" 
                           onclick="confirmDelete(event, '/stock-receive/vendors/<?php echo $vendor['id']; ?>/delete')" 
                           class="btn btn-sm btn-outline-danger">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function confirmDelete(event, url) {
    event.preventDefault();
    if (confirm('Are you sure you want to delete this vendor?')) {
        window.location.href = url;
    }
}
</script>

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/layout.php';