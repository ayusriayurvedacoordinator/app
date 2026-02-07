<?php
/**
 * Application Dispatcher
 * Handles routing requests to appropriate controllers while maintaining backward compatibility
 */

class AppDispatcher
{
    public function dispatch($path)
    {
        // Parse the path to determine which controller/action to call
        $path = trim($path, '/');
        
        if (empty($path)) {
            // Default to dashboard
            include __DIR__ . '/index.php';
            return;
        }
        
        $segments = explode('/', $path);
        $resource = $segments[0] ?? '';
        $id = $segments[1] ?? null;
        $action = $segments[2] ?? null;
        
        switch ($resource) {
            case 'vendors':
                $this->handleVendorRequest($id, $action);
                break;
            case 'invoices':
                $this->handleInvoiceRequest($id, $action);
                break;
            case 'stock_recounts':
                $this->handleStockRecountRequest($id, $action);
                break;
            default:
                // For now, fall back to the original structure
                $this->fallbackToOriginal($path);
        }
    }
    
    private function handleVendorRequest($id, $action)
    {
        require_once __DIR__ . '/app/Controllers/VendorController.php';
        $controller = new VendorController();
        
        if ($id === 'create' && !$action) {
            $controller->create();
        } elseif ($id && $action === 'edit') {
            $controller->edit($id);
        } elseif ($id && $action === 'update') {
            $controller->update($id);
        } elseif ($id && $action === 'delete') {
            $controller->delete($id);
        } elseif ($id) {
            // View specific vendor
            $vendor = $controller->vendorModel->getById((int)$id);
            include __DIR__ . '/app/Views/vendors/show.php';
        } else {
            $controller->index();
        }
    }
    
    private function handleInvoiceRequest($id, $action)
    {
        // For now, delegate to original invoice pages in legacy directory
        if ($id === 'create' && !$action) {
            include __DIR__ . '/legacy/invoices/add.php';
        } else {
            include __DIR__ . '/legacy/invoices/index.php';
        }
    }
    
    private function handleStockRecountRequest($id, $action)
    {
        // For now, delegate to original stock recount pages in legacy directory
        if ($id === 'create' && !$action) {
            include __DIR__ . '/legacy/stock_recounts/add.php';
        } else {
            include __DIR__ . '/legacy/stock_recounts/index.php';
        }
    }
    
    private function fallbackToOriginal($path)
    {
        // Try to include the original file if it exists
        $originalFile = __DIR__ . '/' . $path . '.php';
        
        // Special handling for vendor routes to redirect to new MVC structure
        if (strpos($path, 'vendors') === 0) {
            // Extract vendor ID and action if present
            $parts = explode('/', $path);
            $id = isset($parts[1]) && is_numeric($parts[1]) ? $parts[1] : null;
            $action = isset($parts[2]) ? $parts[2] : null;
            
            // Route to new controller
            $this->handleVendorRequest($id, $action);
            return;
        }
        
        // Check if it's a legacy feature route (from legacy/ directory)
        $legacyFile = __DIR__ . '/legacy/' . $path . '.php';
        if (file_exists($legacyFile)) {
            include $legacyFile;
            return;
        }
        
        if (file_exists($originalFile)) {
            include $originalFile;
        } else {
            // Show 404
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}