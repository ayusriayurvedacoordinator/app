<?php
$page_title = "Add Vendor - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $contact_info = trim($_POST['contact_info']);
    $address = trim($_POST['address']);
    
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO vendors (name, contact_info, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $contact_info, $address);
        
        if ($stmt->execute()) {
            $vendor_id = $conn->insert_id;
            $message = "Vendor added successfully!";
            $message_type = "success";
            
            // Log the insert to audit trail
            $new_vendor_data = [
                'name' => $name,
                'contact_info' => $contact_info,
                'address' => $address
            ];
            log_insert('vendors', $vendor_id, $new_vendor_data);
            
            // Clear form values
            $name = $contact_info = $address = '';
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

<h2>Add New Vendor</h2>

<?php if ($message != ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label for="name" class="form-label">Vendor Name *</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="contact_info" class="form-label">Contact Information</label>
        <textarea class="form-control" id="contact_info" name="contact_info" rows="3"><?php echo isset($contact_info) ? htmlspecialchars($contact_info) : ''; ?></textarea>
    </div>
    
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Add Vendor</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>