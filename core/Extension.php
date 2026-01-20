<?php
namespace OpenWHM\Core;

/**
 * Base Extension Class
 * All extensions should extend this class
 */
abstract class Extension
{
    protected $app;
    protected $hooks;
    protected $db;
    protected $settings = [];
    
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->hooks = $this->app->getHooks();
        $this->db = Database::getInstance();
        
        // Load settings
        $this->loadSettings();
    }
    
    /**
     * Boot the extension - called when extension is loaded
     */
    public function boot()
    {
        // Override in child class
    }
    
    /**
     * Called when extension is activated
     */
    public function activate()
    {
        // Override in child class
    }
    
    /**
     * Called when extension is deactivated
     */
    public function deactivate()
    {
        // Override in child class
    }
    
    /**
     * Get extension configuration
     */
    public static function getConfig()
    {
        return [
            'name' => 'Extension',
            'description' => '',
            'version' => '1.0.0',
            'author' => 'Unknown',
            'type' => 'addon',
            'settings' => []
        ];
    }
    
    /**
     * Load extension settings
     */
    protected function loadSettings()
    {
        $className = get_class($this);
        $parts = explode('\\', $className);
        $name = str_replace('Extension', '', end($parts));
        
        $ext = $this->db->fetch(
            "SELECT settings FROM {$this->db->table('extensions')} WHERE name = ?",
            [$name]
        );
        
        if ($ext) {
            $this->settings = json_decode($ext['settings'], true) ?? [];
        }
    }
    
    /**
     * Get a setting value
     */
    protected function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Register a hook listener
     */
    protected function addHook($hookName, $callback, $priority = 10)
    {
        $this->hooks->add($hookName, $callback, $priority);
    }
    
    /**
     * Register admin menu item
     */
    protected function addAdminMenuItem($label, $url, $icon = 'fa-puzzle-piece')
    {
        $this->addHook('AdminMenu', function($menu) use ($label, $url, $icon) {
            $menu[] = [
                'label' => $label,
                'url' => $url,
                'icon' => $icon
            ];
            return $menu;
        });
    }
    
    /**
     * Register client area menu item
     */
    protected function addClientMenuItem($label, $url, $icon = 'fa-puzzle-piece')
    {
        $this->addHook('ClientMenu', function($menu) use ($label, $url, $icon) {
            $menu[] = [
                'label' => $label,
                'url' => $url,
                'icon' => $icon
            ];
            return $menu;
        });
    }
    
    /**
     * Add admin page route
     */
    protected function addAdminPage($path, $handler)
    {
        $router = $this->app->getRouter();
        $router->get('/admin/addon/' . $path, $handler);
        $router->post('/admin/addon/' . $path, $handler);
    }
    
    /**
     * Add client page route
     */
    protected function addClientPage($path, $handler)
    {
        $router = $this->app->getRouter();
        $router->get('/client/addon/' . $path, $handler);
        $router->post('/client/addon/' . $path, $handler);
    }
    
    /**
     * Log message
     */
    protected function log($message, $level = 'info')
    {
        Logger::$level($message);
    }
}
