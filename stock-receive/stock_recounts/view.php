<?php
$page_title = "View Stock Recount - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

$id = $_GET['id'];

// Fetch recount data
$stmt = $conn->prepare("SELECT * FROM stock_recounts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recount = $result->fetch_assoc();

if (!$recount) {
    header("Location: index.php");
    exit();
}

// Fetch recount items
$items_result = $conn->query("SELECT * FROM stock_recount_items WHERE recount_id = $id ORDER BY product_name");
?>

<h2>Stock Recount Details</h2>

<div class="card mb-4">
    <div class="card-header">
        <h4>Recount Information</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Recount Date:</strong> <?php echo date('M j, Y', strtotime($recount['recount_date'])); ?></p>
                <p><strong>Counted By:</strong> <?php echo htmlspecialchars($recount['counted_by'] ?: 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Created At:</strong> <?php echo date('M j, Y g:i A', strtotime($recount['created_at'])); ?></p>
                <p><strong>Updated At:</strong> <?php echo date('M j, Y g:i A', strtotime($recount['updated_at'])); ?></p>
            </div>
        </div>
        <?php if ($recount['notes']): ?>
            <div class="row">
                <div class="col-md-12">
                    <p><strong>Notes:</strong> <?php echo htmlspecialchars($recount['notes']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<h3>Recounted Items</h3>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Current Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($items_result->num_rows > 0) {
                while($item = $items_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".$item['product_name']."</td>";
                    echo "<td>".$item['counted_quantity']."</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2' class='text-center'>No items found for this recount</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<a href="index.php" class="btn btn-secondary">Back to Recounts</a>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>