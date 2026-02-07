<?php
/**
 * Front Controller
 * Single entry point for the application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once __DIR__ . '/config/config.php';

// Include database connection
require_once __DIR__ . '/config/database.php';

// Include necessary helpers
require_once __DIR__ . '/includes/sanitize.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/logger.php';
require_once __DIR__ . '/includes/audit_helper.php';

// Include router
require_once __DIR__ . '/router/Router.php';

// Include controllers
require_once __DIR__ . '/controllers/VendorController.php';

// Create router instance
$router = new Router();

// Define routes
$router->get('/', function() {
    // Redirect to dashboard or show dashboard
    include __DIR__ . '/index.php';
});

// Vendor routes
$router->get('/vendors', function() {
    $controller = new VendorController();
    $controller->index();
});

$router->get('/vendors/create', function() {
    $controller = new VendorController();
    $controller->create();
});

$router->post('/vendors', function() {
    $controller = new VendorController();
    $controller->store();
});

$router->get('/vendors/{id}/edit', function($id) {
    $controller = new VendorController();
    $controller->edit($id);
});

$router->post('/vendors/{id}/update', function($id) {
    $controller = new VendorController();
    $controller->update($id);
});

$router->get('/vendors/{id}/delete', function($id) {
    $controller = new VendorController();
    $controller->delete($id);
});

// Resolve the current request
$router->resolve();