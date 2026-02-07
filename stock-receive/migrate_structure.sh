#!/bin/bash
# Directory Structure Migration Script

echo "Starting directory structure migration..."

# Create new directory structure
mkdir -p app/{Controllers,Models,Views,Services,Repositories}
mkdir -p app/Views/{layouts,dashboard,vendors,invoices,stock_recounts}
mkdir -p public/{css,js,assets/fonts,images}
mkdir -p includes
mkdir -p sql/{migrations,seeds,test-data}
mkdir -p tests/{Unit,Feature}
mkdir -p docs

# Move existing directories/files to new locations
mv controllers/* app/Controllers/ 2>/dev/null || echo "No controllers to move"
mv models/* app/Models/ 2>/dev/null || echo "No models to move"
mv services/* app/Services/ 2>/dev/null || echo "No services to move"

# Move view files if they exist
mv views/* app/Views/ 2>/dev/null || echo "No views to move"

# Move public assets
mv css/* public/css/ 2>/dev/null || echo "No CSS files to move"
mv js/* public/js/ 2>/dev/null || echo "No JS files to move"
mv assets/* public/assets/ 2>/dev/null || echo "No asset files to move"

# Move includes
mv includes/* includes/ 2>/dev/null || echo "No include files to move"

# Move configuration
mv config/* config/ 2>/dev/null || echo "No config files to move"

# Move SQL files
mv sql/* sql/ 2>/dev/null || echo "No SQL files to move"

# Move test files
mv tests/* tests/ 2>/dev/null || echo "No test files to move"

# Update include paths in PHP files (this is a simplified example)
echo "This script would update include paths in PHP files to reflect new structure"
echo "For example:"
echo "- Change require_once 'models/Vendor.php' to require_once 'app/Models/Vendor.php'"
echo "- Change include 'includes/header.php' to include 'includes/header.php' (relative to web root)"

echo "Directory structure migration completed!"
echo ""
echo "Next steps:"
echo "1. Update all require/include statements in PHP files"
echo "2. Update nginx configuration to point to public/ directory"
echo "3. Update all hardcoded paths in the application"
echo "4. Test thoroughly to ensure everything works"