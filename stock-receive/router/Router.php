<?php
/**
 * Simple Router
 * Handles URL routing to appropriate controllers
 */

class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get(string $path, callable $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, callable $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Add a route for any HTTP method
     */
    private function addRoute(string $method, string $path, callable $callback): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    /**
     * Resolve the current request to a route
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove the base path if needed (e.g., /stock-receive/)
        $basePath = '/stock-receive';
        if (strpos($requestPath, $basePath) === 0) {
            $requestPath = substr($requestPath, strlen($basePath));
        }

        // Ensure path starts with /
        if ($requestPath === '' || $requestPath === false) {
            $requestPath = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);
                
                if (preg_match($pattern, $requestPath, $matches)) {
                    // Remove the full match
                    array_shift($matches);
                    
                    // Call the callback with matched parameters
                    call_user_func_array($route['callback'], $matches);
                    return;
                }
            }
        }

        // If no route matches, show 404
        $this->handleNotFound();
    }

    /**
     * Convert route pattern to regex
     */
    private function convertToRegex(string $path): string
    {
        // Convert {param} to ([^/]+) for capturing parameters
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}