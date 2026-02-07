# Directory Structure Restructuring Proposal

## Current Structure
```
/var/www/app/stock-receive/
├── assets/
├── audit/
├── config/
├── css/
├── includes/
├── invoices/
├── js/
├── logs/
├── models/
├── reports/
├── services/
├── settings/
├── sql/
├── stock_recounts/
└── vendors/
```

## Proposed Standard MVC Structure
```
stock-receive/
├── app/
│   ├── Controllers/
│   │   ├── VendorController.php
│   │   ├── InvoiceController.php
│   │   ├── StockRecountController.php
│   │   └── DashboardController.php
│   ├── Models/
│   │   ├── Vendor.php
│   │   ├── Invoice.php
│   │   ├── StockRecount.php
│   │   └── Category.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── vendors/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── invoices/
│   │   │   ├── index.php
│   │   │   └── create.php
│   │   └── stock_recounts/
│   │       ├── index.php
│   │       └── create.php
│   ├── Services/
│   │   └── StatsService.php
│   └── Repositories/
│       ├── VendorRepository.php
│       ├── InvoiceRepository.php
│       └── StockRecountRepository.php
├── config/
│   ├── config.php
│   └── database.php
├── public/                 # Web root
│   ├── index.php
│   ├── css/
│   │   ├── style.css
│   │   └── bootstrap.min.css
│   ├── js/
│   │   ├── script.js
│   │   └── bootstrap.bundle.min.js
│   ├── assets/
│   │   └── fonts/
│   └── images/
├── includes/               # Shared utilities
│   ├── sanitize.php
│   ├── csrf.php
│   ├── logger.php
│   └── audit_helper.php
├── sql/
│   ├── migrations/
│   ├── seeds/
│   └── test-data/
├── tests/
│   ├── Unit/
│   └── Feature/
├── logs/
├── vendor/                 # Composer dependencies
└── composer.json
```

## Benefits of the New Structure

1. **Clear Separation of Concerns**: Controllers, models, and views are clearly separated
2. **Standardized Organization**: Follows industry-standard MVC patterns
3. **Scalability**: Easy to add new features in organized directories
4. **Maintainability**: Clear location for each type of file
5. **Security**: Web root only contains public-facing files
6. **Best Practices**: Aligns with frameworks like Laravel, Symfony, etc.

## Migration Steps

1. Create new directory structure
2. Move files to appropriate locations
3. Update all include/require paths
4. Update nginx configuration to point to new public/ directory
5. Update all URLs and paths in the application
6. Test thoroughly to ensure nothing is broken