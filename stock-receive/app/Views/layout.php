<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Stock Receive System'; ?></title>
    <!-- Local Bootstrap CSS -->
    <link href="/stock-receive/public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/stock-receive/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/stock-receive/">Stock Receive System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="/stock-receive/">Dashboard</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Vendors</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/vendors">View Vendors</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/vendors/create">Add Vendor</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Invoices</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/invoices">View Invoices</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/invoices/create">Add Invoice</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Stock</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/stock_recounts">Stock Recounts</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/invoices">Invoices</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Settings</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/settings">Categories</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Reports</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/reports/invoice_comparison.php">Invoice Comparison</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/reports/invoice_analysis.php">Invoice Analysis</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/reports/stock_variance.php">Stock Variance</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Audit</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/stock-receive/audit/all.php">All Changes</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/audit/invoices.php">Invoice Changes</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/audit/vendors.php">Vendor Changes</a></li>
                            <li><a class="dropdown-item" href="/stock-receive/audit/stock_recounts.php">Stock Recount Changes</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Content will be inserted here by child templates -->
        <?php echo $content ?? ''; ?>
    </div>

    <!-- Local Bootstrap JS -->
    <script src="/stock-receive/public/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/stock-receive/public/js/script.js"></script>
</body>
</html>