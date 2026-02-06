<?php
$page_title = "Vendors - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Handle delete request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get the vendor data before deletion for audit trail
    $get_stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
    $get_stmt->bind_param("i", $id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $vendor = $result->fetch_assoc();
    $get_stmt->close();
    
    if($vendor) {
        // Log the deletion to audit trail
        log_delete('vendors', $id, $vendor);
        
        $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Vendor deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error deleting vendor: '.$conn->error.'</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Vendor not found.</div>';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Vendors</h2>
    <a href="add.php" class="btn btn-primary">Add New Vendor</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact Info</th>
            <th>Address</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM vendors ORDER BY name ASC");
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['name']."</td>";
                echo "<td>".$row['contact_info']."</td>";
                echo "<td>".$row['address']."</td>";
                echo "<td>".date('M j, Y', strtotime($row['created_at']))."</td>";
                echo "<td>
                        <a href='edit.php?id=".$row['id']."' class='btn btn-sm btn-outline-primary'>Edit</a>
                        <a href='?delete=".$row['id']."' class='btn btn-sm btn-outline-danger' onclick='return confirmDelete();'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>No vendors found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>