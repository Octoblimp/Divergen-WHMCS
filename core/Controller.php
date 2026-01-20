<?php
namespace OpenWHM\Core;

/**
 * Base Controller
 */
abstract class Controller
{
    protected $app;
    protected $db;
    protected $session;
    protected $hooks;
    protected $view;
    
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->db = Database::getInstance();
        $this->session = $this->app->getSession();
        $this->hooks = $this->app->getHooks();
        $this->view = new View();
    }
    
    /**
     * Render a view
     */
    protected function render($template, $data = [])
    {
        return $this->view->render($template, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Get POST data
     */
    protected function input($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function query($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf()
    {
        $token = $this->input('csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!$this->session->validateCsrfToken($token)) {
            throw new \Exception('Invalid CSRF token');
        }
    }
    
    /**
     * Set flash message
     */
    protected function flash($type, $message)
    {
        $this->session->flash($type, $message);
    }
    
    /**
     * Require client authentication
     */
    protected function requireClient()
    {
        if (!$this->session->isClientLoggedIn()) {
            $this->redirect(CLIENT_URL . '/login');
        }
    }
    
    /**
     * Require admin authentication
     */
    protected function requireAdmin()
    {
        if (!$this->session->isAdminLoggedIn()) {
            $this->redirect(ADMIN_URL . '/login');
        }
    }
    
    /**
     * Get current client
     */
    protected function getClient()
    {
        $clientId = $this->session->getClientId();
        
        if ($clientId) {
            return $this->db->fetch(
                "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
                [$clientId]
            );
        }
        
        return null;
    }
    
    /**
     * Get current admin
     */
    protected function getAdmin()
    {
        $adminId = $this->session->getAdminId();
        
        if ($adminId) {
            return $this->db->fetch(
                "SELECT * FROM {$this->db->table('admins')} WHERE id = ?",
                [$adminId]
            );
        }
        
        return null;
    }
}
