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
        require_once __DIR__ . '/controllers/VendorController.php';
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
            include __DIR__ . '/views/vendors/show.php';
        } else {
            $controller->index();
        }
    }
    
    private function handleInvoiceRequest($id, $action)
    {
        // For now, delegate to original invoice pages
        if ($id === 'create' && !$action) {
            include __DIR__ . '/invoices/add.php';
        } else {
            include __DIR__ . '/invoices/index.php';
        }
    }
    
    private function handleStockRecountRequest($id, $action)
    {
        // For now, delegate to original stock recount pages
        if ($id === 'create' && !$action) {
            include __DIR__ . '/stock_recounts/add.php';
        } else {
            include __DIR__ . '/stock_recounts/index.php';
        }
    }
    
    private function fallbackToOriginal($path)
    {
        // Try to include the original file if it exists
        $originalFile = __DIR__ . '/' . $path . '.php';
        if (file_exists($originalFile)) {
            include $originalFile;
        } else {
            // Show 404
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}