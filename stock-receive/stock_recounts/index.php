<?php
$page_title = "Stock Recounts - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Handle delete request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get the recount data before deletion for audit trail
    $get_stmt = $conn->prepare("SELECT * FROM stock_recounts WHERE id = ?");
    $get_stmt->bind_param("i", $id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $recount = $result->fetch_assoc();
    $get_stmt->close();
    
    if($recount) {
        // Log the deletion to audit trail
        log_delete('stock_recounts', $id, $recount);
        
        $stmt = $conn->prepare("DELETE FROM stock_recounts WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Stock recount deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error deleting stock recount: '.$conn->error.'</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Stock recount not found.</div>';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Stock Recounts</h2>
    <a href="add.php" class="btn btn-primary">Add New Recount</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Recount Date</th>
            <th>Counted By</th>
            <th>Items Count</th>
            <th>Notes</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("
            SELECT sr.*, 
                   (SELECT COUNT(*) FROM stock_recount_items sri WHERE sri.recount_id = sr.id) as items_count
            FROM stock_recounts sr
            ORDER BY sr.recount_date DESC
        ");
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".date('M j, Y', strtotime($row['recount_date']))."</td>";
                echo "<td>".$row['counted_by']."</td>";
                echo "<td>".$row['items_count']."</td>";
                echo "<td>".substr($row['notes'], 0, 50).((strlen($row['notes']) > 50) ? '...' : '')."</td>";
                echo "<td>".date('M j, Y g:i A', strtotime($row['created_at']))."</td>";
                echo "<td>
                        <a href='view.php?id=".$row['id']."' class='btn btn-sm btn-outline-info'>View</a>
                        <a href='edit.php?id=".$row['id']."' class='btn btn-sm btn-outline-primary'>Edit</a>
                        <a href='?delete=".$row['id']."' class='btn btn-sm btn-outline-danger' onclick='return confirmDelete();'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>No stock recounts found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>