<?php
namespace OpenWHM\Core;

/**
 * Extension Manager
 * Handles loading, enabling, and disabling extensions
 */
class ExtensionManager
{
    private $extensions = [];
    private $loaded = [];
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Load all enabled extensions
     */
    public function loadAll()
    {
        // Get enabled extensions from database
        try {
            $enabled = $this->db->fetchAll(
                "SELECT * FROM {$this->db->table('extensions')} WHERE enabled = 1"
            );
        } catch (\Exception $e) {
            // Table might not exist yet during installation
            $enabled = [];
        }
        
        foreach ($enabled as $ext) {
            $this->load($ext['name']);
        }
        
        // Also scan for new extensions
        $this->scanForNew();
    }
    
    /**
     * Load a specific extension
     */
    public function load($name)
    {
        if (isset($this->loaded[$name])) {
            return true;
        }
        
        $path = EXTENSIONS_PATH . '/' . $name;
        $mainFile = $path . '/' . $name . '.php';
        $configFile = $path . '/extension.json';
        
        if (!file_exists($mainFile)) {
            Logger::warning("Extension main file not found: {$mainFile}");
            return false;
        }
        
        // Load extension config
        $config = [];
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        }
        
        // Check dependencies
        if (!$this->checkDependencies($config['dependencies'] ?? [])) {
            Logger::warning("Extension dependencies not met: {$name}");
            return false;
        }
        
        // Load the extension
        require_once $mainFile;
        
        $className = "OpenWHM\\Extensions\\{$name}\\{$name}Extension";
        
        if (class_exists($className)) {
            $extension = new $className();
            
            if (method_exists($extension, 'boot')) {
                $extension->boot();
            }
            
            $this->loaded[$name] = $extension;
            $this->extensions[$name] = array_merge($config, [
                'instance' => $extension
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Scan for new extensions
     */
    private function scanForNew()
    {
        if (!is_dir(EXTENSIONS_PATH)) {
            return;
        }
        
        $dirs = scandir(EXTENSIONS_PATH);
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $path = EXTENSIONS_PATH . '/' . $dir;
            
            if (is_dir($path) && file_exists($path . '/extension.json')) {
                $this->registerExtension($dir);
            }
        }
    }
    
    /**
     * Register extension in database
     */
    private function registerExtension($name)
    {
        $configFile = EXTENSIONS_PATH . '/' . $name . '/extension.json';
        
        if (!file_exists($configFile)) {
            return;
        }
        
        $config = json_decode(file_get_contents($configFile), true);
        
        // Check if already registered
        $exists = $this->db->count('extensions', 'name = ?', [$name]);
        
        if ($exists == 0) {
            $this->db->insert('extensions', [
                'name' => $name,
                'display_name' => $config['name'] ?? $name,
                'description' => $config['description'] ?? '',
                'version' => $config['version'] ?? '1.0.0',
                'author' => $config['author'] ?? 'Unknown',
                'type' => $config['type'] ?? 'addon',
                'enabled' => 0,
                'settings' => '{}',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Enable an extension
     */
    public function enable($name)
    {
        // Load extension first
        if (!$this->load($name)) {
            throw new \Exception("Failed to load extension: {$name}");
        }
        
        // Run activation hook
        $extension = $this->loaded[$name] ?? null;
        if ($extension && method_exists($extension, 'activate')) {
            $extension->activate();
        }
        
        // Update database
        $this->db->update('extensions', ['enabled' => 1], 'name = ?', [$name]);
        
        return true;
    }
    
    /**
     * Disable an extension
     */
    public function disable($name)
    {
        // Run deactivation hook
        $extension = $this->loaded[$name] ?? null;
        if ($extension && method_exists($extension, 'deactivate')) {
            $extension->deactivate();
        }
        
        // Update database
        $this->db->update('extensions', ['enabled' => 0], 'name = ?', [$name]);
        
        // Unload extension
        unset($this->loaded[$name]);
        
        return true;
    }
    
    /**
     * Check dependencies
     */
    private function checkDependencies($dependencies)
    {
        foreach ($dependencies as $dep => $version) {
            if ($dep === 'php') {
                if (!version_compare(PHP_VERSION, $version, '>=')) {
                    return false;
                }
            } elseif ($dep === 'openwhm') {
                if (!version_compare(SYSTEM_VERSION, $version, '>=')) {
                    return false;
                }
            } elseif (!isset($this->loaded[$dep])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get all extensions
     */
    public function getAll()
    {
        try {
            return $this->db->fetchAll("SELECT * FROM {$this->db->table('extensions')}");
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get extension settings
     */
    public function getSettings($name)
    {
        $ext = $this->db->fetch(
            "SELECT settings FROM {$this->db->table('extensions')} WHERE name = ?",
            [$name]
        );
        
        return $ext ? json_decode($ext['settings'], true) : [];
    }
    
    /**
     * Save extension settings
     */
    public function saveSettings($name, $settings)
    {
        return $this->db->update(
            'extensions',
            ['settings' => json_encode($settings), 'updated_at' => date('Y-m-d H:i:s')],
            'name = ?',
            [$name]
        );
    }
    
    /**
     * Get loaded extension instance
     */
    public function get($name)
    {
        return $this->loaded[$name] ?? null;
    }
    
    /**
     * Check if extension is loaded
     */
    public function isLoaded($name)
    {
        return isset($this->loaded[$name]);
    }
    
    /**
     * Get extensions by type
     */
    public function getByType($type)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('extensions')} WHERE type = ?",
            [$type]
        );
    }
}
