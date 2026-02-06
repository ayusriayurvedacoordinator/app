<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = "Debug Vendors Page";
include 'includes/header.php';

echo "<h1>Debug Info</h1>";

// Include the database connection
require_once 'config/database.php';

echo "<p>Database connection successful</p>";

// Handle delete request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()) {
        echo '<div class="alert alert-success">Vendor deleted successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error deleting vendor: '.$conn->error.'</div>';
    }
    $stmt->close();
}

echo "<p>After delete handling</p>";

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Vendors</h2>
    <a href="debug_add.php" class="btn btn-primary">Add New Vendor</a>
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
                        <a href='debug_edit.php?id=".$row['id']."' class='btn btn-sm btn-outline-primary'>Edit</a>
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
include 'includes/footer.php';
?>