# SQL Files Organization

This directory contains SQL files organized by purpose:

## Directory Structure

### `/migrations`
Contains database schema definitions and structural changes:
- `001_initial_schema.sql` - Complete initial database schema with all tables and indexes

### `/seeds` 
Contains default data that should be present in a production system:
- `001_seed_default_data.sql` - Default categories and sample vendors

### `/test-data`
Contains test data for development and testing environments:
- `001_test_data.sql` - Sample records for testing the application

## Usage

### Setting up a new database:
1. Execute files in `/migrations` in order
2. Execute files in `/seeds` to populate default data
3. Optionally execute files in `/test-data` for development

### Development workflow:
```bash
# Apply migrations
mysql -u username -p < sql/migrations/001_initial_schema.sql

# Add seed data
mysql -u username -p < sql/seeds/001_seed_default_data.sql

# Add test data (optional)
mysql -u username -p < sql/test-data/001_test_data.sql
```

## Versioning
Files are prefixed with sequential numbers to ensure proper execution order during migrations.