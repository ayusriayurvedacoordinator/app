<?php
$page_title = "Edit Vendor - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$id = $_GET['id'];

// Fetch vendor data
$stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$vendor = $result->fetch_assoc();

if (!$vendor) {
    header("Location: index.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $contact_info = trim($_POST['contact_info']);
    $address = trim($_POST['address']);
    
    // Get old values for audit trail
    $old_vendor_data = [
        'name' => $vendor['name'],
        'contact_info' => $vendor['contact_info'],
        'address' => $vendor['address']
    ];
    
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE vendors SET name=?, contact_info=?, address=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $contact_info, $address, $id);
        
        if ($stmt->execute()) {
            $message = "Vendor updated successfully!";
            $message_type = "success";
            
            // Log the update to audit trail
            $new_vendor_data = [
                'name' => $name,
                'contact_info' => $contact_info,
                'address' => $address
            ];
            log_update('vendors', $id, $old_vendor_data, $new_vendor_data);
            
            // Refresh vendor data
            $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $vendor = $result->fetch_assoc();
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Vendor name is required.";
        $message_type = "warning";
    }
}
?>

<h2>Edit Vendor</h2>

<?php if ($message != ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label for="name" class="form-label">Vendor Name *</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($vendor['name']); ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="contact_info" class="form-label">Contact Information</label>
        <textarea class="form-control" id="contact_info" name="contact_info" rows="3"><?php echo htmlspecialchars($vendor['contact_info']); ?></textarea>
    </div>
    
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($vendor['address']); ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Update Vendor</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>