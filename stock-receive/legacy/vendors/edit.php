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
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    // Validate phone number (10 digits, starts with 0)
    if (!preg_match('/^0\d{9}$/', $phone_number)) {
        $message = "Phone number must be 10 digits and start with 0.";
        $message_type = "warning";
    } else if (!empty($name)) {
        // Get old values for audit trail
        $old_vendor_data = [
            'name' => $vendor['name'],
            'phone_number' => $vendor['phone_number'],
            'email' => $vendor['email'],
            'address' => $vendor['address']
        ];
        
        $stmt = $conn->prepare("UPDATE vendors SET name=?, phone_number=?, email=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $phone_number, $email, $address, $id);
        
        if ($stmt->execute()) {
            $message = "Vendor updated successfully!";
            $message_type = "success";
            
            // Log the update to audit trail
            $new_vendor_data = [
                'name' => $name,
                'phone_number' => $phone_number,
                'email' => $email,
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
        <label for="phone_number" class="form-label">Phone Number *</label>
        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($vendor['phone_number']); ?>" placeholder="e.g., 0771234567" required>
        <div class="form-text">Phone number must be 10 digits and start with 0</div>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($vendor['email']); ?>" placeholder="e.g., vendor@example.com">
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