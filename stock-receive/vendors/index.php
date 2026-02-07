<?php
$page_title = "Vendors - Stock Receive System";
require_once '../config/database.php'; // Include database connection first
require_once '../includes/sanitize.php'; // Include sanitization utilities
require_once '../models/Vendor.php'; // Include Vendor model
require_once '../includes/header.php'; // Include header from parent directory

$vendorModel = new Vendor();
$message = '';
$message_type = '';

// Handle delete request
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Sanitize the ID

    try {
        if($vendorModel->delete($id)) {
            $message = 'Vendor deleted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error deleting vendor.';
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . sanitize_output($e->getMessage());
        $message_type = 'danger';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Vendors</h2>
    <a href="add.php" class="btn btn-primary">Add New Vendor</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
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
        <?php
        $vendors = $vendorModel->getAll('name', 'ASC');
        
        if(!empty($vendors)) {
            foreach($vendors as $row) {
                echo "<tr>";
                echo "<td>".sanitize_output($row['id'])."</td>";
                echo "<td>".sanitize_output($row['name'])."</td>";
                echo "<td>".sanitize_output($row['phone_number'])."</td>";
                echo "<td>".sanitize_output($row['email'])."</td>";
                echo "<td>".sanitize_output($row['address'])."</td>";
                echo "<td>".date('M j, Y', strtotime($row['created_at']))."</td>";
                echo "<td>
                        <a href='edit.php?id=".urlencode($row['id'])."' class='btn btn-sm btn-outline-primary'>Edit</a>
                        <a href='?delete=".urlencode($row['id'])."' class='btn btn-sm btn-outline-danger' onclick='return confirmDelete();'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>No vendors found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>