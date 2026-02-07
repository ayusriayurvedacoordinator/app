<?php
/**
 * Base Controller
 * Provides common functionality for all controllers
 */

abstract class BaseController
{
    protected $conn;
    protected $viewData = [];

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Set data to be passed to the view
     */
    protected function set(string $key, $value): void
    {
        $this->viewData[$key] = $value;
    }

    /**
     * Render a view template
     */
    protected function render(string $view, array $data = []): void
    {
        $viewData = array_merge($this->viewData, $data);
        
        // Extract variables to local scope for the view
        extract($viewData);
        
        // Determine the view file path
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }
        
        include $viewFile;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * Get POST data with optional default value
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data with optional default value
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Get sanitized input
     */
    protected function sanitizeInput($data)
    {
        require_once __DIR__ . '/../includes/sanitize.php';
        return sanitize_input($data);
    }

    /**
     * Get sanitized output
     */
    protected function sanitizeOutput($data)
    {
        require_once __DIR__ . '/../includes/sanitize.php';
        return sanitize_output($data);
    }
}