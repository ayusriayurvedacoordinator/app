# Stock Receive System - Testing Guide

## Setting Up Tests

1. Install dependencies:
```bash
composer install
```

2. Make sure your database configuration is correct in `.env` file

3. Run the tests:
```bash
./vendor/bin/phpunit
```

Or if you have PHPUnit installed globally:
```bash
phpunit
```

## Running Different Test Suites

- Run all tests: `phpunit`
- Run only unit tests: `phpunit --testsuite Unit`
- Run only feature tests: `phpunit --testsuite Feature`
- Run with coverage: `phpunit --coverage-html coverage/`

## Test Structure

- `tests/Unit/` - Unit tests for individual classes and functions
- `tests/Feature/` - Feature tests for complete workflows
- `tests/bootstrap.php` - Test environment setup

## Writing Tests

When adding new functionality, please follow these guidelines:

1. Add corresponding tests in the appropriate directory
2. Follow PHPUnit best practices
3. Ensure test coverage for critical functionality
4. Use descriptive test method names