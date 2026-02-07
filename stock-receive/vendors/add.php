<?php
$page_title = "Add Vendor - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/csrf.php'; // Include CSRF protection
include '../includes/sanitize.php'; // Include sanitization utilities
include '../includes/audit_helper.php'; // Include audit helper functions
include '../includes/header.php'; // Include header from parent directory

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request. Please try again.";
        $message_type = "danger";
    } else {
        $name = trim(sanitize_input($_POST['name']));
        $phone_number = trim(sanitize_input($_POST['phone_number']));
        $email = trim(sanitize_input($_POST['email']));
        $address = trim(sanitize_input($_POST['address']));

        // Validate phone number (10 digits, starts with 0)
        if (!preg_match(PHONE_NUMBER_PATTERN, $phone_number)) {
            $message = "Phone number must be 10 digits and start with 0.";
            $message_type = "warning";
        } else if (!empty($name)) {
            // Validate input lengths
            if (strlen($name) > NAME_MAX_LENGTH) {
                $message = "Vendor name exceeds maximum length of " . NAME_MAX_LENGTH . " characters.";
                $message_type = "warning";
            } elseif (strlen($email) > EMAIL_MAX_LENGTH) {
                $message = "Email address exceeds maximum length of " . EMAIL_MAX_LENGTH . " characters.";
                $message_type = "warning";
            } elseif (strlen($address) > ADDRESS_MAX_LENGTH) {
                $message = "Address exceeds maximum length of " . ADDRESS_MAX_LENGTH . " characters.";
                $message_type = "warning";
            } else {
                $stmt = $conn->prepare("INSERT INTO vendors (name, phone_number, email, address) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $phone_number, $email, $address);

                if ($stmt->execute()) {
                    $vendor_id = $conn->insert_id;
                    $message = "Vendor added successfully! Vendor ID: " . $vendor_id;
                    $message_type = "success";

                    // Log the insert to audit trail
                    $new_vendor_data = [
                        'name' => $name,
                        'phone_number' => $phone_number,
                        'email' => $email,
                        'address' => $address
                    ];
                    log_insert('vendors', $vendor_id, $new_vendor_data);

                    // Clear form values
                    $name = $phone_number = $email = $address = '';
                } else {
                    $message = "Error: " . sanitize_output($stmt->error);
                    $message_type = "danger";
                }
                $stmt->close();
            }
        } else {
            $message = "Vendor name is required.";
            $message_type = "warning";
        }
    }
}
?>

<h2>Add New Vendor</h2>

<?php if ($message != ''): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<form method="post">
    <?php echo csrf_input_field(); ?>
    <div class="mb-3">
        <label for="name" class="form-label">Vendor Name *</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
    </div>

    <div class="mb-3">
        <label for="phone_number" class="form-label">Phone Number *</label>
        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo isset($phone_number) ? htmlspecialchars($phone_number) : ''; ?>" placeholder="e.g., 0771234567" required>
        <div class="form-text">Phone number must be 10 digits and start with 0</div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="e.g., vendor@example.com">
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