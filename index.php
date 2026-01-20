<?php
/**
 * OpenWHM - Open Source Web Hosting Management
 * Main Entry Point
 */

define('OPENWH', true);
define('ROOT_PATH', __DIR__);

// Handle installer route
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

if ($path === '/install' || strpos($path, '/install/') === 0) {
    require_once ROOT_PATH . '/install/index.php';
    exit;
}

// Check if installed
if (!file_exists(ROOT_PATH . '/config/installed.lock')) {
    header('Location: /install');
    exit;
}

// Load configuration FIRST
require_once ROOT_PATH . '/config/config.php';

// Load core files
require_once ROOT_PATH . '/core/Autoloader.php';

// Initialize autoloader
OpenWHM\Core\Autoloader::register();

// Initialize the application
$app = new OpenWHM\Core\Application();

// Handle the request
$app->run();
