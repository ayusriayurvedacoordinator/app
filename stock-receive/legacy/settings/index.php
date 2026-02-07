<?php
$page_title = "Categories - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Handle delete request for categories
if(isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];
    
    // Check if category is being used in any invoice items
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoice_items WHERE category_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $count = $check_result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if($count > 0) {
        $message = "Cannot delete category: it is being used in invoice items.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            $message = "Category deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting category: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Handle add category request
if(isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    $description = trim($_POST['category_description']);
    
    if(!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if($stmt->execute()) {
            $message = "Category added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding category: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Category name is required.";
        $message_type = "warning";
    }
}

// Handle update category request
if(isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = trim($_POST['edit_category_name']);
    $description = trim($_POST['edit_category_description']);
    
    if(!empty($name)) {
        $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $id);
        
        if($stmt->execute()) {
            $message = "Category updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating category: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Category name is required.";
        $message_type = "warning";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Categories</h2>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Add New Category</h4>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="row">
                <div class="col-md-4">
                    <label for="category_name" class="form-label">Category Name *</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" required placeholder="e.g., Balm, Oil, Kwatha">
                </div>
                <div class="col-md-6">
                    <label for="category_description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="category_description" name="category_description" placeholder="Description of the category">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" name="add_category" class="btn btn-primary d-block w-100">Add Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4>Existing Categories</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                    
                    if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row['id']."</td>";
                            echo "<td><strong>".$row['name']."</strong></td>";
                            echo "<td>".$row['description']."</td>";
                            echo "<td>".date('M j, Y', strtotime($row['created_at']))."</td>";
                            echo "<td>
                                    <button type='button' class='btn btn-sm btn-outline-info' data-bs-toggle='modal' data-bs-target='#editModal".$row['id']."'>Edit</button>
                                    <a href='?delete_category=".$row['id']."' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete this category?\");'>Delete</a>
                                  </td>";
                            echo "</tr>";
                            
                            // Modal for editing category
                            echo '<div class="modal fade" id="editModal'.$row['id'].'" tabindex="-1" aria-labelledby="editModalLabel'.$row['id'].'" aria-hidden="true">';
                            echo '<div class="modal-dialog">';
                            echo '<div class="modal-content">';
                            echo '<div class="modal-header">';
                            echo '<h5 class="modal-title" id="editModalLabel'.$row['id'].'">Edit Category</h5>';
                            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                            echo '</div>';
                            echo '<form method="post">';
                            echo '<div class="modal-body">';
                            echo '<input type="hidden" name="category_id" value="'.$row['id'].'">';
                            echo '<div class="mb-3">';
                            echo '<label for="edit_category_name_'.$row['id'].'" class="form-label">Category Name *</label>';
                            echo '<input type="text" class="form-control" id="edit_category_name_'.$row['id'].'" name="edit_category_name" value="'.htmlspecialchars($row['name']).'" required>';
                            echo '</div>';
                            echo '<div class="mb-3">';
                            echo '<label for="edit_category_description_'.$row['id'].'" class="form-label">Description</label>';
                            echo '<input type="text" class="form-control" id="edit_category_description_'.$row['id'].'" name="edit_category_description" value="'.htmlspecialchars($row['description']).'">';
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="modal-footer">';
                            echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>';
                            echo '<button type="submit" name="update_category" class="btn btn-primary">Update Category</button>';
                            echo '</div>';
                            echo '</form>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No categories found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4>System Information</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Timezone:</strong> Asia/Colombo (LKR)</p>
                <p><strong>Currency:</strong> LKR (Sri Lankan Rupees)</p>
            </div>
            <div class="col-md-6">
                <p><strong>Current Server Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>