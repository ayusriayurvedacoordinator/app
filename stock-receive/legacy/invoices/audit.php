<?php
$page_title = "Invoice Audit Trail - Stock Receive System";
include '../config/database.php'; // Include database connection first
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

// Get audit trail for this invoice
$audit_trail = get_audit_trail('invoices', $id);

// Also get audit trail for items in this invoice
$stmt = $conn->prepare("SELECT id FROM invoice_items WHERE invoice_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$item_ids = [];
while ($row = $result->fetch_assoc()) {
    $item_ids[] = $row['id'];
}

$item_audit_trail = [];
if (!empty($item_ids)) {
    $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM audit_trail WHERE table_name = 'invoice_items' AND record_id IN ($placeholders) ORDER BY changed_at DESC");
    $stmt->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
        $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        $item_audit_trail[] = $row;
    }
    $stmt->close();
}
?>

<h2>Audit Trail for Invoice #<?php echo $invoice['invoice_number'] ?: 'N/A'; ?> (ID: <?php echo $id; ?>)</h2>

<div class="card mb-4">
    <div class="card-header">
        <h4>Invoice Information</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Vendor:</strong> <?php echo $invoice['vendor_name']; ?></p>
                <p><strong>Invoice Date:</strong> <?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></p>
                <p><strong>Received Date:</strong> <?php echo date('M j, Y', strtotime($invoice['received_date'])); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Total Amount:</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p>
                <p><strong>Discount:</strong> $<?php echo number_format($invoice['discount'], 2); ?></p>
                <p><strong>Net Amount:</strong> $<?php echo number_format($invoice['total_amount'] - $invoice['discount'], 2); ?></p>
            </div>
        </div>
    </div>
</div>

<h3>Invoice Changes</h3>
<?php if (count($audit_trail) > 0): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Changed At</th>
            <th>Changed By</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($audit_trail as $log): ?>
        <tr>
            <td><span class="badge bg-<?php echo $log['action'] === 'INSERT' ? 'success' : ($log['action'] === 'UPDATE' ? 'warning' : 'danger'); ?>"><?php echo $log['action']; ?></span></td>
            <td><?php echo date('M j, Y g:i A', strtotime($log['changed_at'])); ?></td>
            <td><?php echo $log['changed_by'] ?: 'System'; ?></td>
            <td>
                <?php if ($log['action'] === 'UPDATE'): ?>
                    <details>
                        <summary>Changes</summary>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Old Values:</strong><br>
                                <?php foreach ($log['old_values'] as $key => $value): ?>
                                    <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>New Values:</strong><br>
                                <?php foreach ($log['new_values'] as $key => $value): ?>
                                    <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </details>
                <?php elseif ($log['action'] === 'INSERT'): ?>
                    <details>
                        <summary>New Record</summary>
                        <?php foreach ($log['new_values'] as $key => $value): ?>
                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </details>
                <?php else: ?>
                    <details>
                        <summary>Deleted Record</summary>
                        <?php foreach ($log['old_values'] as $key => $value): ?>
                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </details>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="alert alert-info">No audit trail found for this invoice.</div>
<?php endif; ?>

<h3>Invoice Items Changes</h3>
<?php if (count($item_audit_trail) > 0): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Item ID</th>
            <th>Action</th>
            <th>Changed At</th>
            <th>Changed By</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($item_audit_trail as $log): ?>
        <tr>
            <td><?php echo $log['record_id']; ?></td>
            <td><span class="badge bg-<?php echo $log['action'] === 'INSERT' ? 'success' : ($log['action'] === 'UPDATE' ? 'warning' : 'danger'); ?>"><?php echo $log['action']; ?></span></td>
            <td><?php echo date('M j, Y g:i A', strtotime($log['changed_at'])); ?></td>
            <td><?php echo $log['changed_by'] ?: 'System'; ?></td>
            <td>
                <?php if ($log['action'] === 'UPDATE'): ?>
                    <details>
                        <summary>Changes</summary>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Old Values:</strong><br>
                                <?php foreach ($log['old_values'] as $key => $value): ?>
                                    <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>New Values:</strong><br>
                                <?php foreach ($log['new_values'] as $key => $value): ?>
                                    <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </details>
                <?php elseif ($log['action'] === 'INSERT'): ?>
                    <details>
                        <summary>New Record</summary>
                        <?php foreach ($log['new_values'] as $key => $value): ?>
                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </details>
                <?php else: ?>
                    <details>
                        <summary>Deleted Record</summary>
                        <?php foreach ($log['old_values'] as $key => $value): ?>
                            <?php echo ucfirst(str_replace('_', ' ', $key)); ?>: <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </details>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="alert alert-info">No audit trail found for items in this invoice.</div>
<?php endif; ?>

<a href="index.php" class="btn btn-secondary">Back to Invoices</a>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>