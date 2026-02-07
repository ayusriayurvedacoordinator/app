<?php
$page_title = "Stock Variance Report - Stock Receive System";
include '../config/database.php'; // Include database connection first
include '../includes/header.php'; // Include header from parent directory

// Get all stock recounts for the filter
$recounts_result = $conn->query("SELECT DISTINCT recount_date FROM stock_recounts ORDER BY recount_date DESC");
?>

<h2>Stock Variance Report</h2>
<p>This report shows variances between counted stock and recorded stock from recounts.</p>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <label for="recount_date" class="form-label">Select Recount Date:</label>
            <select name="recount_date" id="recount_date" class="form-select" onchange="this.form.submit()">
                <option value="">-- All Dates --</option>
                <?php
                while($recount = $recounts_result->fetch_assoc()) {
                    $selected = (isset($_GET['recount_date']) && $_GET['recount_date'] == $recount['recount_date']) ? 'selected' : '';
                    echo "<option value='".$recount['recount_date']."' $selected>".date('M j, Y', strtotime($recount['recount_date']))."</option>";
                }
                ?>
            </select>
        </div>
    </div>
</form>

<?php if(isset($_GET['recount_date']) && !empty($_GET['recount_date'])): ?>
    <h4>Stock Variance for: <?php echo date('M j, Y', strtotime($_GET['recount_date'])); ?></h4>
    
    <div class="table-responsive">
        <table class="table table-striped report-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Previous Quantity</th>
                    <th>Counted Quantity</th>
                    <th>Variance</th>
                    <th>Category</th>
                    <th>Recount Date</th>
                    <th>Counted By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recount_date = $_GET['recount_date'];
                
                // Get the recount ID for the selected date
                $recount_query = $conn->prepare("SELECT id, counted_by FROM stock_recounts WHERE recount_date = ?");
                $recount_query->bind_param("s", $recount_date);
                $recount_query->execute();
                $recount_result = $recount_query->get_result();
                $recount = $recount_result->fetch_assoc();
                
                if($recount) {
                    $recount_id = $recount['id'];
                    $counted_by = $recount['counted_by'];
                    
                    // Get the variance data with category information
                    $variance_sql = "
                        SELECT 
                            sri.product_name,
                            sri.counted_quantity,
                            (SELECT quantity FROM invoice_items WHERE product_name = sri.product_name ORDER BY created_at DESC LIMIT 1) AS previous_quantity,
                            (sri.counted_quantity - (SELECT quantity FROM invoice_items WHERE product_name = sri.product_name ORDER BY created_at DESC LIMIT 1)) AS variance,
                            c.name AS category_name
                        FROM stock_recount_items sri
                        LEFT JOIN invoice_items ii ON sri.product_name = ii.product_name
                        LEFT JOIN categories c ON ii.category_id = c.id
                        WHERE sri.recount_id = ?
                        ORDER BY sri.product_name
                    ";
                    
                    $variance_stmt = $conn->prepare($variance_sql);
                    $variance_stmt->bind_param("i", $recount_id);
                    $variance_stmt->execute();
                    $variance_result = $variance_stmt->get_result();
                    
                    if($variance_result->num_rows > 0) {
                        while($row = $variance_result->fetch_assoc()) {
                            $variance = $row['variance'];
                            $variance_class = '';
                            
                            if ($variance > 0) {
                                $variance_class = 'variance-positive'; // Positive variance (more than expected)
                            } else if ($variance < 0) {
                                $variance_class = 'variance-negative'; // Negative variance (less than expected)
                            }
                            
                            echo "<tr>";
                            echo "<td>".$row['product_name']."</td>";
                            echo "<td>".($row['previous_quantity'] ?? 'N/A')."</td>";
                            echo "<td>".$row['counted_quantity']."</td>";
                            echo "<td class='$variance_class'>".$variance."</td>";
                            echo "<td>".($row['category_name'] ?: 'N/A')."</td>";
                            echo "<td>".date('M j, Y', strtotime($recount_date))."</td>";
                            echo "<td>".$counted_by."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No variance data found for this recount</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No recount found for this date</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <h4>Overall Stock Variance Summary</h4>
    
    <div class="table-responsive">
        <table class="table table-striped report-table">
            <thead>
                <tr>
                    <th>Recount Date</th>
                    <th>Counted By</th>
                    <th>Items Counted</th>
                    <th>Total Variance</th>
                    <th>Positive Variance Items</th>
                    <th>Negative Variance Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $summary_sql = "
                    SELECT 
                        sr.recount_date,
                        sr.counted_by,
                        (SELECT COUNT(*) FROM stock_recount_items sri WHERE sri.recount_id = sr.id) AS items_counted,
                        (SELECT SUM(sri.counted_quantity - (SELECT COALESCE(SUM(ii.quantity), 0) FROM invoice_items ii WHERE ii.product_name = sri.product_name ORDER BY ii.created_at DESC LIMIT 1)) FROM stock_recount_items sri WHERE sri.recount_id = sr.id) AS total_variance
                    FROM stock_recounts sr
                    ORDER BY sr.recount_date DESC
                ";
                
                $summary_result = $conn->query($summary_sql);
                
                if($summary_result->num_rows > 0) {
                    while($row = $summary_result->fetch_assoc()) {
                        // Calculate positive and negative variance counts
                        $variance_details_sql = "
                            SELECT 
                                sri.product_name,
                                sri.counted_quantity,
                                (SELECT COALESCE(SUM(ii.quantity), 0) FROM invoice_items ii WHERE ii.product_name = sri.product_name ORDER BY ii.created_at DESC LIMIT 1) AS previous_quantity
                            FROM stock_recount_items sri
                            WHERE sri.recount_id = (SELECT id FROM stock_recounts WHERE recount_date = ?)
                        ";
                        
                        $variance_details_stmt = $conn->prepare($variance_details_sql);
                        $variance_details_stmt->bind_param("s", $row['recount_date']);
                        $variance_details_stmt->execute();
                        $variance_details_result = $variance_details_stmt->get_result();
                        
                        $positive_variance_count = 0;
                        $negative_variance_count = 0;
                        
                        while($detail = $variance_details_result->fetch_assoc()) {
                            $variance = $detail['counted_quantity'] - ($detail['previous_quantity'] ?? 0);
                            if($variance > 0) {
                                $positive_variance_count++;
                            } else if($variance < 0) {
                                $negative_variance_count++;
                            }
                        }
                        
                        echo "<tr>";
                        echo "<td>".date('M j, Y', strtotime($row['recount_date']))."</td>";
                        echo "<td>".$row['counted_by']."</td>";
                        echo "<td>".$row['items_counted']."</td>";
                        echo "<td>".$row['total_variance']."</td>";
                        echo "<td>".$positive_variance_count."</td>";
                        echo "<td>".$negative_variance_count."</td>";
                        echo "<td><a href='?recount_date=".$row['recount_date']."' class='btn btn-sm btn-outline-primary'>View Details</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No stock recounts found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
include '../includes/footer.php'; // Include footer from parent directory
?>