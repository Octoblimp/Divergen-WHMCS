<?php
/**
 * OpenWHM Installation Wizard
 * Web-based installer for first-time setup and database migrations
 */

session_start();

define('OPENWH', true);
define('ROOT_PATH', __DIR__);
define('INSTALLER_VERSION', '1.0.0');

// Check if already installed
$installedLock = ROOT_PATH . '/config/installed.lock';
$isInstalled = file_exists($installedLock);

// Get current step
$step = $_GET['step'] ?? ($isInstalled ? 'upgrade' : 'welcome');

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    handleAjaxRequest();
    exit;
}

/**
 * Handle AJAX requests
 */
function handleAjaxRequest()
{
    $action = $_GET['ajax'];
    
    switch ($action) {
        case 'test_db':
            testDatabaseConnection();
            break;
        case 'check_requirements':
            checkRequirements();
            break;
        case 'run_install':
            runInstallation();
            break;
        case 'run_migration':
            runMigration();
            break;
        case 'check_updates':
            checkDatabaseUpdates();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

/**
 * Test database connection
 */
function testDatabaseConnection()
{
    $host = $_POST['db_host'] ?? 'localhost';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    
    try {
        $pdo = new PDO(
            "mysql:host={$host};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote($name));
        $dbExists = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'db_exists' => (bool)$dbExists,
            'message' => $dbExists ? 'Database connection successful!' : 'Connected! Database will be created.'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Connection failed: ' . $e->getMessage()
        ]);
    }
}

/**
 * Check system requirements
 */
function checkRequirements()
{
    $requirements = [
        'php_version' => [
            'name' => 'PHP Version >= 7.4',
            'met' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'current' => PHP_VERSION
        ],
        'pdo_mysql' => [
            'name' => 'PDO MySQL Extension',
            'met' => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not installed'
        ],
        'curl' => [
            'name' => 'cURL Extension',
            'met' => extension_loaded('curl'),
            'current' => extension_loaded('curl') ? 'Installed' : 'Not installed'
        ],
        'openssl' => [
            'name' => 'OpenSSL Extension',
            'met' => extension_loaded('openssl'),
            'current' => extension_loaded('openssl') ? 'Installed' : 'Not installed'
        ],
        'json' => [
            'name' => 'JSON Extension',
            'met' => extension_loaded('json'),
            'current' => extension_loaded('json') ? 'Installed' : 'Not installed'
        ],
        'mbstring' => [
            'name' => 'Mbstring Extension',
            'met' => extension_loaded('mbstring'),
            'current' => extension_loaded('mbstring') ? 'Installed' : 'Not installed'
        ],
        'config_writable' => [
            'name' => 'Config Directory Writable',
            'met' => is_writable(ROOT_PATH . '/config'),
            'current' => is_writable(ROOT_PATH . '/config') ? 'Writable' : 'Not writable'
        ],
        'cache_writable' => [
            'name' => 'Cache Directory Writable',
            'met' => !file_exists(ROOT_PATH . '/cache') || is_writable(ROOT_PATH . '/cache'),
            'current' => (!file_exists(ROOT_PATH . '/cache') || is_writable(ROOT_PATH . '/cache')) ? 'OK' : 'Not writable'
        ],
        'logs_writable' => [
            'name' => 'Logs Directory Writable',
            'met' => !file_exists(ROOT_PATH . '/logs') || is_writable(ROOT_PATH . '/logs'),
            'current' => (!file_exists(ROOT_PATH . '/logs') || is_writable(ROOT_PATH . '/logs')) ? 'OK' : 'Not writable'
        ]
    ];
    
    $allMet = true;
    foreach ($requirements as $req) {
        if (!$req['met']) {
            $allMet = false;
            break;
        }
    }
    
    echo json_encode([
        'success' => $allMet,
        'requirements' => $requirements
    ]);
}

/**
 * Run installation
 */
function runInstallation()
{
    try {
        // Get form data
        $dbHost = $_POST['db_host'] ?? 'localhost';
        $dbName = $_POST['db_name'] ?? '';
        $dbUser = $_POST['db_user'] ?? '';
        $dbPass = $_POST['db_pass'] ?? '';
        $dbPrefix = $_POST['db_prefix'] ?? 'owh_';
        
        $systemUrl = rtrim($_POST['system_url'] ?? '', '/');
        $companyName = $_POST['company_name'] ?? 'OpenWHM';
        
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminName = $_POST['admin_name'] ?? 'Administrator';
        
        // Connect to MySQL
        $pdo = new PDO(
            "mysql:host={$dbHost};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");
        
        // Read and execute schema
        $schemaFile = ROOT_PATH . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception('Schema file not found: database/schema.sql');
        }
        
        $schema = file_get_contents($schemaFile);
        
        // Replace prefix if different
        if ($dbPrefix !== 'owh_') {
            $schema = str_replace('owh_', $dbPrefix, $schema);
        }
        
        // Execute schema (split by semicolons)
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($statements as $statement) {
            if (!empty($statement) && stripos($statement, '--') !== 0) {
                $pdo->exec($statement);
            }
        }
        
        // Create admin account
        $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("
            INSERT INTO {$dbPrefix}admins (email, password, name, role, created_at, updated_at)
            VALUES (?, ?, ?, 'super_admin', NOW(), NOW())
            ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name)
        ");
        $stmt->execute([$adminEmail, $hashedPassword, $adminName]);
        
        // Generate encryption key
        $encryptionKey = bin2hex(random_bytes(16));
        $cronKey = bin2hex(random_bytes(16));
        $sessionName = 'OPENWHM_' . strtoupper(substr(md5($systemUrl), 0, 8));
        
        // Create config file
        $configContent = generateConfigFile([
            'db_host' => $dbHost,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_pass' => $dbPass,
            'db_prefix' => $dbPrefix,
            'system_url' => $systemUrl,
            'company_name' => $companyName,
            'encryption_key' => $encryptionKey,
            'cron_key' => $cronKey,
            'session_name' => $sessionName
        ]);
        
        file_put_contents(ROOT_PATH . '/config/config.php', $configContent);
        
        // Create installed.lock
        file_put_contents(ROOT_PATH . '/config/installed.lock', json_encode([
            'version' => INSTALLER_VERSION,
            'installed_at' => date('Y-m-d H:i:s'),
            'schema_version' => getSchemaVersion()
        ]));
        
        // Create required directories
        $dirs = ['cache', 'logs', 'uploads'];
        foreach ($dirs as $dir) {
            $path = ROOT_PATH . '/' . $dir;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            file_put_contents($path . '/.htaccess', "Deny from all\n");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Installation completed successfully!'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Installation failed: ' . $e->getMessage()
        ]);
    }
}

/**
 * Check for database updates
 */
function checkDatabaseUpdates()
{
    try {
        require_once ROOT_PATH . '/config/config.php';
        
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $migrations = getMigrations();
        $applied = getAppliedMigrations($pdo);
        $pending = array_diff(array_keys($migrations), $applied);
        
        // Check for schema differences
        $differences = checkSchemaDifferences($pdo);
        
        echo json_encode([
            'success' => true,
            'pending_migrations' => count($pending),
            'migrations' => array_values($pending),
            'schema_differences' => $differences,
            'needs_update' => count($pending) > 0 || count($differences) > 0
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Run database migration
 */
function runMigration()
{
    try {
        require_once ROOT_PATH . '/config/config.php';
        
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Ensure migrations table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at DATETIME NOT NULL,
                UNIQUE KEY (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $migrations = getMigrations();
        $applied = getAppliedMigrations($pdo);
        $pending = array_diff(array_keys($migrations), $applied);
        
        $executedCount = 0;
        $errors = [];
        
        foreach ($pending as $migrationName) {
            try {
                $sql = $migrations[$migrationName];
                
                // Replace prefix
                $sql = str_replace('owh_', DB_PREFIX, $sql);
                
                // Execute migration
                $pdo->exec($sql);
                
                // Record migration
                $stmt = $pdo->prepare("INSERT INTO " . DB_PREFIX . "migrations (migration, executed_at) VALUES (?, NOW())");
                $stmt->execute([$migrationName]);
                
                $executedCount++;
            } catch (Exception $e) {
                $errors[] = "{$migrationName}: " . $e->getMessage();
            }
        }
        
        // Apply schema differences
        $differences = checkSchemaDifferences($pdo);
        foreach ($differences as $diff) {
            try {
                $pdo->exec($diff['sql']);
            } catch (Exception $e) {
                $errors[] = "Schema fix: " . $e->getMessage();
            }
        }
        
        // Update installed.lock
        $lockFile = ROOT_PATH . '/config/installed.lock';
        $lockData = json_decode(file_get_contents($lockFile), true);
        $lockData['schema_version'] = getSchemaVersion();
        $lockData['last_migration'] = date('Y-m-d H:i:s');
        file_put_contents($lockFile, json_encode($lockData, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'success' => count($errors) === 0,
            'executed' => $executedCount,
            'errors' => $errors,
            'message' => count($errors) === 0 
                ? "Successfully applied {$executedCount} migrations" 
                : "Completed with " . count($errors) . " errors"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get all available migrations
 */
function getMigrations()
{
    $migrationsDir = ROOT_PATH . '/database/migrations';
    $migrations = [];
    
    if (is_dir($migrationsDir)) {
        $files = glob($migrationsDir . '/*.sql');
        foreach ($files as $file) {
            $name = basename($file, '.sql');
            $migrations[$name] = file_get_contents($file);
        }
        ksort($migrations);
    }
    
    return $migrations;
}

/**
 * Get applied migrations
 */
function getAppliedMigrations($pdo)
{
    try {
        $stmt = $pdo->query("SELECT migration FROM " . DB_PREFIX . "migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Check for schema differences
 */
function checkSchemaDifferences($pdo)
{
    $differences = [];
    
    // Expected tables from schema.sql
    $expectedTables = [
        'admins', 'clients', 'product_groups', 'products', 'servers', 
        'server_groups', 'services', 'domains', 'domain_pricing',
        'invoices', 'invoice_items', 'transactions', 'orders', 'order_items',
        'tickets', 'ticket_replies', 'support_departments', 'logs', 'settings',
        'payment_gateways', 'extensions', 'email_templates', 'announcements',
        'knowledgebase_categories', 'knowledgebase_articles', 'contacts',
        'api_credentials', 'cancellation_requests', 'migrations'
    ];
    
    foreach ($expectedTables as $table) {
        $fullTable = DB_PREFIX . $table;
        $stmt = $pdo->query("SHOW TABLES LIKE '{$fullTable}'");
        if (!$stmt->fetchColumn()) {
            // Table missing - generate CREATE statement
            $differences[] = [
                'type' => 'missing_table',
                'table' => $table,
                'sql' => getTableCreateStatement($table),
                'description' => "Table '{$fullTable}' is missing"
            ];
        }
    }
    
    // Check for missing columns in existing tables
    $columnChecks = [
        'clients' => ['credit', 'tax_exempt', 'two_factor_enabled'],
        'services' => ['suspended_reason', 'auto_terminate'],
        'invoices' => ['tax_rate2', 'credit'],
        'products' => ['stock_control', 'stock_quantity']
    ];
    
    foreach ($columnChecks as $table => $columns) {
        $fullTable = DB_PREFIX . $table;
        try {
            $stmt = $pdo->query("DESCRIBE `{$fullTable}`");
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($columns as $column) {
                if (!in_array($column, $existingColumns)) {
                    $sql = getColumnAddStatement($table, $column);
                    if ($sql) {
                        $differences[] = [
                            'type' => 'missing_column',
                            'table' => $table,
                            'column' => $column,
                            'sql' => $sql,
                            'description' => "Column '{$column}' missing from '{$fullTable}'"
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // Table doesn't exist, already handled above
        }
    }
    
    return $differences;
}

/**
 * Get CREATE TABLE statement for a table
 */
function getTableCreateStatement($table)
{
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    $schema = file_get_contents($schemaFile);
    
    // Find the CREATE TABLE statement
    $pattern = '/CREATE TABLE IF NOT EXISTS `owh_' . preg_quote($table) . '`\s*\([^;]+\);/is';
    if (preg_match($pattern, $schema, $matches)) {
        return str_replace('owh_', DB_PREFIX, $matches[0]);
    }
    
    return null;
}

/**
 * Get ALTER TABLE ADD COLUMN statement
 */
function getColumnAddStatement($table, $column)
{
    $columnDefs = [
        'clients' => [
            'credit' => "ALTER TABLE `" . DB_PREFIX . "clients` ADD COLUMN `credit` DECIMAL(10,2) DEFAULT 0.00 AFTER `language`",
            'tax_exempt' => "ALTER TABLE `" . DB_PREFIX . "clients` ADD COLUMN `tax_exempt` TINYINT(1) DEFAULT 0 AFTER `credit`",
            'two_factor_enabled' => "ALTER TABLE `" . DB_PREFIX . "clients` ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `two_factor_secret`"
        ],
        'services' => [
            'suspended_reason' => "ALTER TABLE `" . DB_PREFIX . "services` ADD COLUMN `suspended_reason` TEXT NULL AFTER `status`",
            'auto_terminate' => "ALTER TABLE `" . DB_PREFIX . "services` ADD COLUMN `auto_terminate` TINYINT(1) DEFAULT 1 AFTER `suspended_reason`"
        ],
        'invoices' => [
            'tax_rate2' => "ALTER TABLE `" . DB_PREFIX . "invoices` ADD COLUMN `tax_rate2` DECIMAL(5,2) DEFAULT 0.00 AFTER `tax`",
            'credit' => "ALTER TABLE `" . DB_PREFIX . "invoices` ADD COLUMN `credit` DECIMAL(10,2) DEFAULT 0.00 AFTER `tax2`"
        ],
        'products' => [
            'stock_control' => "ALTER TABLE `" . DB_PREFIX . "products` ADD COLUMN `stock_control` TINYINT(1) DEFAULT 0 AFTER `welcome_email`",
            'stock_quantity' => "ALTER TABLE `" . DB_PREFIX . "products` ADD COLUMN `stock_quantity` INT DEFAULT 0 AFTER `stock_control`"
        ]
    ];
    
    return $columnDefs[$table][$column] ?? null;
}

/**
 * Get current schema version
 */
function getSchemaVersion()
{
    return '1.0.0';
}

/**
 * Generate config file content
 */
function generateConfigFile($settings)
{
    return <<<PHP
<?php
/**
 * OpenWHM Configuration File
 * Generated by installer on {$_SERVER['REQUEST_TIME']}
 */

// Prevent direct access
if (!defined('OPENWH')) {
    die('Direct access not permitted');
}

// Check if installed
define('INSTALLED', file_exists(__DIR__ . '/installed.lock'));

// System Settings
define('SYSTEM_NAME', '{$settings['company_name']}');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_URL', '{$settings['system_url']}');
define('BASE_URL', SYSTEM_URL);
define('ADMIN_URL', SYSTEM_URL . '/admin');
define('CLIENT_URL', SYSTEM_URL . '/client');

// Company Settings (aliases for compatibility)
define('COMPANY_NAME', SYSTEM_NAME);
define('APP_NAME', SYSTEM_NAME);
define('APP_URL', SYSTEM_URL);

// Database Configuration
define('DB_HOST', '{$settings['db_host']}');
define('DB_NAME', '{$settings['db_name']}');
define('DB_USER', '{$settings['db_user']}');
define('DB_PASS', '{$settings['db_pass']}');
define('DB_PREFIX', '{$settings['db_prefix']}');
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('ENCRYPTION_KEY', '{$settings['encryption_key']}');
define('SESSION_NAME', '{$settings['session_name']}');
define('CSRF_TOKEN_NAME', 'owh_csrf_token');
define('PASSWORD_HASH_COST', 12);

// Email Settings
define('MAIL_HOST', 'smtp.yourdomain.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@yourdomain.com');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', SYSTEM_NAME);
define('MAIL_ENCRYPTION', 'tls');

// Currency Settings
define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '\$');

// Invoice Settings
define('INVOICE_PREFIX', 'INV-');
define('INVOICE_START_NUMBER', 1000);
define('INVOICE_DUE_DAYS', 14);
define('INVOICE_LATE_FEE_PERCENT', 5);

// Tax Settings
define('ENABLE_TAX', true);
define('DEFAULT_TAX_RATE', 0);

// Cron Settings
define('CRON_KEY', '{$settings['cron_key']}');

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
define('LOG_LEVEL', 'info');

// API Settings
define('API_ENABLED', true);
define('API_RATE_LIMIT', 100);

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

// Error Reporting (change to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
PHP;
}

// Render the installer page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $isInstalled ? 'Upgrade' : 'Install'; ?> - OpenWHM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .installer-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .installer-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .installer-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .installer-header h1 {
            font-size: 2rem;
            margin: 0;
        }
        
        .installer-header p {
            margin: 0.5rem 0 0;
            opacity: 0.8;
        }
        
        .installer-body {
            padding: 2rem;
        }
        
        .step-indicators {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 0.5rem;
        }
        
        .step-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
        }
        
        .step-indicator.active {
            background: #667eea;
            color: white;
        }
        
        .step-indicator.completed {
            background: #28a745;
            color: white;
        }
        
        .step-indicator:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            width: 30px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }
        
        .step-indicator.completed:not(:last-child)::after {
            background: #28a745;
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .btn-install {
            background: var(--primary-gradient);
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
        }
        
        .btn-install:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .loading-spinner {
            display: none;
        }
        
        .loading .loading-spinner {
            display: inline-block;
        }
        
        .loading .btn-text {
            display: none;
        }
        
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #667eea;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .alert-migration {
            background: linear-gradient(135deg, #f6f8ff 0%, #f0f3ff 100%);
            border: 1px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container installer-container">
        <div class="installer-card">
            <div class="installer-header">
                <h1><i class="fas fa-server me-2"></i>OpenWHM</h1>
                <p><?php echo $isInstalled ? 'System Upgrade & Maintenance' : 'Installation Wizard'; ?></p>
            </div>
            
            <div class="installer-body">
                <?php if (!$isInstalled): ?>
                <!-- New Installation -->
                <div class="step-indicators">
                    <div class="step-indicator active" data-step="1">1</div>
                    <div class="step-indicator" data-step="2">2</div>
                    <div class="step-indicator" data-step="3">3</div>
                    <div class="step-indicator" data-step="4">4</div>
                </div>
                
                <!-- Step 1: Requirements -->
                <div class="step-content active" id="step-1">
                    <h4 class="mb-4"><i class="fas fa-clipboard-check me-2"></i>System Requirements</h4>
                    <div id="requirements-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 mb-0">Checking requirements...</p>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="button" class="btn btn-primary btn-install" id="btn-step1" disabled onclick="goToStep(2)">
                            <span class="btn-text">Continue</span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Database -->
                <div class="step-content" id="step-2">
                    <h4 class="mb-4"><i class="fas fa-database me-2"></i>Database Configuration</h4>
                    <form id="db-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                    <label>Database Host</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="db_name" name="db_name" value="openwhm" required>
                                    <label>Database Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="db_user" name="db_user" required>
                                    <label>Database Username</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                                    <label>Database Password</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="db_prefix" name="db_prefix" value="owh_">
                                    <label>Table Prefix</label>
                                </div>
                            </div>
                        </div>
                        <div id="db-status" class="mt-3"></div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn btn-outline-primary flex-grow-1" onclick="testDatabase()">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                            <button type="button" class="btn btn-primary btn-install" id="btn-step2" disabled onclick="goToStep(3)">
                                <span class="btn-text">Continue</span>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 3: System Settings -->
                <div class="step-content" id="step-3">
                    <h4 class="mb-4"><i class="fas fa-cog me-2"></i>System Configuration</h4>
                    <form id="system-form">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="company_name" name="company_name" value="OpenWHM" required>
                                    <label>Company Name</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="system_url" name="system_url" 
                                           value="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>" required>
                                    <label>System URL (without trailing slash)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr>
                                <h5 class="mb-3"><i class="fas fa-user-shield me-2"></i>Admin Account</h5>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="admin_name" name="admin_name" value="Administrator" required>
                                    <label>Admin Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                                    <label>Admin Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="8">
                                    <label>Admin Password</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn btn-primary btn-install flex-grow-1" onclick="goToStep(4)">
                                <span class="btn-text">Continue</span>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 4: Install -->
                <div class="step-content" id="step-4">
                    <h4 class="mb-4"><i class="fas fa-rocket me-2"></i>Ready to Install</h4>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Click the button below to install OpenWHM. This will:
                        <ul class="mb-0 mt-2">
                            <li>Create the database structure</li>
                            <li>Set up your admin account</li>
                            <li>Configure the system</li>
                        </ul>
                    </div>
                    <div id="install-status" class="mb-3"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="goToStep(3)">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </button>
                        <button type="button" class="btn btn-success btn-install flex-grow-1" id="btn-install" onclick="runInstall()">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2"></span>
                            <span class="btn-text"><i class="fas fa-check me-2"></i>Install OpenWHM</span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 5: Complete -->
                <div class="step-content" id="step-5">
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-success">Installation Complete!</h3>
                        <p class="text-muted mb-4">OpenWHM has been successfully installed.</p>
                        <div class="d-grid gap-2">
                            <a href="admin" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Go to Admin Panel
                            </a>
                            <a href="./" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>View Website
                            </a>
                        </div>
                        <div class="alert alert-warning mt-4 text-start">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Security Notice:</strong> Delete or rename <code>install.php</code> after installation.
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Upgrade Mode -->
                <div class="text-center mb-4">
                    <i class="fas fa-sync-alt text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">System Maintenance</h4>
                    <p class="text-muted">Check for and apply database updates</p>
                </div>
                
                <div id="upgrade-status">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">Checking for updates...</p>
                    </div>
                </div>
                
                <div id="upgrade-actions" class="d-none">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btn-upgrade" onclick="runUpgrade()">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2"></span>
                            <span class="btn-text"><i class="fas fa-sync me-2"></i>Apply Updates</span>
                        </button>
                        <a href="./" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Return to Site
                        </a>
                    </div>
                </div>
                
                <div id="upgrade-complete" class="d-none text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">System is up to date!</h5>
                    <a href="./" class="btn btn-primary mt-3">Return to Site</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-white-50">OpenWHM v<?php echo INSTALLER_VERSION; ?> | &copy; <?php echo date('Y'); ?></small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        let dbValid = false;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!$isInstalled): ?>
            checkRequirements();
            <?php else: ?>
            checkUpdates();
            <?php endif; ?>
        });
        
        function checkRequirements() {
            fetch('install.php?ajax=check_requirements')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    for (const [key, req] of Object.entries(data.requirements)) {
                        html += `
                            <div class="requirement-item">
                                <span>${req.name}</span>
                                <span>
                                    ${req.met 
                                        ? '<i class="fas fa-check-circle text-success"></i>' 
                                        : '<i class="fas fa-times-circle text-danger"></i>'}
                                    <small class="text-muted ms-2">${req.current}</small>
                                </span>
                            </div>
                        `;
                    }
                    document.getElementById('requirements-list').innerHTML = html;
                    document.getElementById('btn-step1').disabled = !data.success;
                    
                    if (!data.success) {
                        document.getElementById('requirements-list').innerHTML += `
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please fix the requirements above before continuing.
                            </div>
                        `;
                    }
                });
        }
        
        function goToStep(step) {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
                if (parseInt(el.dataset.step) < step) {
                    el.classList.add('completed');
                } else {
                    el.classList.remove('completed');
                }
            });
            
            document.getElementById('step-' + step).classList.add('active');
            document.querySelector(`.step-indicator[data-step="${step}"]`).classList.add('active');
            currentStep = step;
        }
        
        function testDatabase() {
            const formData = new FormData(document.getElementById('db-form'));
            
            fetch('install.php?ajax=test_db', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                const statusDiv = document.getElementById('db-status');
                if (data.success) {
                    statusDiv.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>${data.message}</div>`;
                    document.getElementById('btn-step2').disabled = false;
                    dbValid = true;
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>${data.message}</div>`;
                    document.getElementById('btn-step2').disabled = true;
                    dbValid = false;
                }
            });
        }
        
        function runInstall() {
            const btn = document.getElementById('btn-install');
            btn.classList.add('loading');
            btn.disabled = true;
            
            // Collect all form data
            const formData = new FormData();
            
            // Database
            document.querySelectorAll('#db-form input').forEach(input => {
                formData.append(input.name, input.value);
            });
            
            // System
            document.querySelectorAll('#system-form input').forEach(input => {
                formData.append(input.name, input.value);
            });
            
            fetch('install.php?ajax=run_install', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    goToStep(5);
                } else {
                    document.getElementById('install-status').innerHTML = `
                        <div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>${data.message}</div>
                    `;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            })
            .catch(error => {
                document.getElementById('install-status').innerHTML = `
                    <div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>An error occurred: ${error}</div>
                `;
                btn.classList.remove('loading');
                btn.disabled = false;
            });
        }
        
        // Upgrade functions
        function checkUpdates() {
            fetch('install.php?ajax=check_updates')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.needs_update) {
                            let html = '<div class="alert alert-migration">';
                            html += '<h6><i class="fas fa-database me-2"></i>Database Updates Available</h6>';
                            
                            if (data.pending_migrations > 0) {
                                html += `<p class="mb-2">${data.pending_migrations} migration(s) pending</p>`;
                            }
                            
                            if (data.schema_differences.length > 0) {
                                html += '<p class="mb-1">Schema fixes needed:</p><ul class="mb-0">';
                                data.schema_differences.forEach(diff => {
                                    html += `<li><small>${diff.description}</small></li>`;
                                });
                                html += '</ul>';
                            }
                            
                            html += '</div>';
                            document.getElementById('upgrade-status').innerHTML = html;
                            document.getElementById('upgrade-actions').classList.remove('d-none');
                        } else {
                            document.getElementById('upgrade-status').classList.add('d-none');
                            document.getElementById('upgrade-complete').classList.remove('d-none');
                        }
                    } else {
                        document.getElementById('upgrade-status').innerHTML = `
                            <div class="alert alert-danger">${data.message}</div>
                        `;
                    }
                });
        }
        
        function runUpgrade() {
            const btn = document.getElementById('btn-upgrade');
            btn.classList.add('loading');
            btn.disabled = true;
            
            fetch('install.php?ajax=run_migration', {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('upgrade-status').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>${data.message}
                        </div>
                    `;
                    document.getElementById('upgrade-actions').classList.add('d-none');
                    setTimeout(() => {
                        document.getElementById('upgrade-status').classList.add('d-none');
                        document.getElementById('upgrade-complete').classList.remove('d-none');
                    }, 2000);
                } else {
                    document.getElementById('upgrade-status').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>${data.message}
                            ${data.errors ? '<ul class="mb-0 mt-2">' + data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>' : ''}
                        </div>
                    `;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>