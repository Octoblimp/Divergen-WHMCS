<?php
namespace OpenWHM\Core;

/**
 * HTTP Router
 */
class Router
{
    private $routes = [];
    private $currentGroup = '';
    private $middleware = [];
    
    /**
     * Add GET route
     */
    public function get($path, $handler, $middleware = [])
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $handler, $middleware = [])
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $handler, $middleware = [])
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $handler, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Add route group
     */
    public function group($prefix, $callback, $middleware = [])
    {
        $previousGroup = $this->currentGroup;
        $previousMiddleware = $this->middleware;
        
        $this->currentGroup .= $prefix;
        $this->middleware = array_merge($this->middleware, $middleware);
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
        $this->middleware = $previousMiddleware;
    }
    
    /**
     * Add a route
     */
    private function addRoute($method, $path, $handler, $middleware = [])
    {
        $fullPath = $this->currentGroup . $path;
        $allMiddleware = array_merge($this->middleware, $middleware);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $allMiddleware,
            'pattern' => $this->pathToPattern($fullPath)
        ];
    }
    
    /**
     * Convert path to regex pattern
     */
    private function pathToPattern($path)
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash (except for root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $this->runMiddleware($middleware);
                }
                
                // Call the handler
                return $this->callHandler($route['handler'], $params);
            }
        }
        
        // No route found
        $this->notFound();
    }
    
    /**
     * Run middleware
     */
    private function runMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $class = "OpenWHM\\Middleware\\{$middleware}";
            $instance = new $class();
            $instance->handle();
        } elseif (is_callable($middleware)) {
            $middleware();
        }
    }
    
    /**
     * Call route handler
     */
    private function callHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);
            $class = "OpenWHM\\Controllers\\{$controller}";
            
            if (!class_exists($class)) {
                throw new \Exception("Controller {$class} not found");
            }
            
            $instance = new $class();
            
            if (!method_exists($instance, $method)) {
                throw new \Exception("Method {$method} not found in {$class}");
            }
            
            return call_user_func_array([$instance, $method], $params);
            
        } elseif (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        throw new \Exception("Invalid route handler");
    }
    
    /**
     * Handle 404 Not Found
     */
    private function notFound()
    {
        http_response_code(404);
        
        if (file_exists(TEMPLATE_PATH . '/error/404.php')) {
            include TEMPLATE_PATH . '/error/404.php';
        } else {
            echo "<h1>404 - Page Not Found</h1>";
        }
        exit;
    }
    
    /**
     * Generate URL for a route
     */
    public function url($path, $params = [])
    {
        $url = $path;
        
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return SYSTEM_URL . $url;
    }
    
    /**
     * Redirect to URL
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
}
