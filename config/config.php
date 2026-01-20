<?php
/**
 * OpenWHM Configuration File
 * WHMCS-like Web Hosting Management System
 */

// Prevent direct access
if (!defined('OPENWH')) {
    die('Direct access not permitted');
}

// System Settings
define('SYSTEM_NAME', 'OpenWHM');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_URL', 'https://yourdomain.com');
define('ADMIN_URL', SYSTEM_URL . '/admin');
define('CLIENT_URL', SYSTEM_URL . '/client');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'openwhm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PREFIX', 'owh_');
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('ENCRYPTION_KEY', 'your-32-character-encryption-key');
define('SESSION_NAME', 'OPENWHM_SESSION');
define('CSRF_TOKEN_NAME', 'owh_csrf_token');
define('PASSWORD_HASH_COST', 12);

// Email Settings
define('MAIL_HOST', 'smtp.yourdomain.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@yourdomain.com');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'OpenWHM');
define('MAIL_ENCRYPTION', 'tls');

// Currency Settings
define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// Invoice Settings
define('INVOICE_PREFIX', 'INV-');
define('INVOICE_START_NUMBER', 1000);
define('INVOICE_DUE_DAYS', 14);
define('INVOICE_LATE_FEE_PERCENT', 5);

// Tax Settings
define('ENABLE_TAX', true);
define('DEFAULT_TAX_RATE', 0);

// Cron Settings
define('CRON_KEY', 'your-cron-secret-key');

// Extension Settings
define('EXTENSIONS_PATH', ROOT_PATH . '/extensions');
define('ENABLE_EXTENSIONS', true);

// Template Settings
define('TEMPLATE_PATH', ROOT_PATH . '/templates');
define('ADMIN_TEMPLATE', 'admin');
define('CLIENT_TEMPLATE', 'client');
define('FRONTEND_TEMPLATE', 'frontend');

// Cache Settings
define('ENABLE_CACHE', true);
define('CACHE_PATH', ROOT_PATH . '/cache');
define('CACHE_LIFETIME', 3600);

// Logging
define('LOG_PATH', ROOT_PATH . '/logs');
define('LOG_LEVEL', 'debug'); // debug, info, warning, error

// API Settings
define('API_ENABLED', true);
define('API_RATE_LIMIT', 100); // requests per minute

// NameSilo API
define('NAMESILO_API_KEY', '');
define('NAMESILO_SANDBOX', true);

// HestiaCP API
define('HESTIACP_HOST', '');
define('HESTIACP_PORT', 8083);
define('HESTIACP_USERNAME', 'admin');
define('HESTIACP_PASSWORD', '');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
