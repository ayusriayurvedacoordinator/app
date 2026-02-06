<?php
$page_title = "Vendor Audit Trail - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Get vendor-specific audit trail records
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM audit_trail WHERE table_name = 'vendors'");
$stmt->execute();
$count_result = $stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_records / $limit);

$stmt = $conn->prepare("SELECT * FROM audit_trail WHERE table_name = 'vendors' ORDER BY changed_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$audit_trail = [];
while ($row = $result->fetch_assoc()) {
    $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
    $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
    $audit_trail[] = $row;
}
$stmt->close();
?>

<h2>Vendor Audit Trail</h2>
<p>This page shows all changes made to vendors.</p>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Record ID</th>
                <th>Action</th>
                <th>Changed At</th>
                <th>Changed By</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($audit_trail as $log): ?>
            <tr>
                <td><?php echo $log['record_id']; ?></td>
                <td><span class="badge bg-<?php echo $log['action'] === 'INSERT' ? 'success' : ($log['action'] === 'UPDATE' ? 'warning' : 'danger'); ?>"><?php echo $log['action']; ?></span></td>
                <td><?php echo date('M j, Y g:i A', strtotime($log['changed_at'])); ?></td>
                <td><?php echo htmlspecialchars($log['changed_by'] ?: 'System'); ?></td>
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
</div>

<!-- Pagination -->
<nav aria-label="Audit trail pagination">
    <ul class="pagination">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>