<?php
/**
 * OpenWHM - Open Source Web Hosting Management
 * Main Entry Point
 */

define('OPENWH', true);
define('ROOT_PATH', __DIR__);

// Load core files
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Autoloader.php';

// Initialize autoloader
OpenWHM\Core\Autoloader::register();

// Initialize the application
$app = new OpenWHM\Core\Application();

// Handle the request
$app->run();
