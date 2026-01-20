<?php
namespace OpenWHM\Core;

/**
 * PSR-4 Autoloader for OpenWHM
 */
class Autoloader
{
    private static $namespaces = [];
    
    /**
     * Register the autoloader
     */
    public static function register()
    {
        // Register default namespaces
        self::addNamespace('OpenWHM\\Core', ROOT_PATH . '/core');
        self::addNamespace('OpenWHM\\Models', ROOT_PATH . '/models');
        self::addNamespace('OpenWHM\\Controllers', ROOT_PATH . '/controllers');
        self::addNamespace('OpenWHM\\Modules', ROOT_PATH . '/modules');
        self::addNamespace('OpenWHM\\Extensions', ROOT_PATH . '/extensions');
        self::addNamespace('OpenWHM\\Api', ROOT_PATH . '/api');
        
        spl_autoload_register([self::class, 'loadClass']);
    }
    
    /**
     * Add a namespace prefix and base directory
     */
    public static function addNamespace($prefix, $baseDir)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        self::$namespaces[$prefix] = $baseDir;
    }
    
    /**
     * Load the class file
     */
    public static function loadClass($class)
    {
        foreach (self::$namespaces as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }
            
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        
        return false;
    }
}
