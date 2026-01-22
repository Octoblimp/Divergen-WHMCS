<?php
namespace OpenWHM\Modules\Servers\hestiacp;

use OpenWHM\Core\Database;
use OpenWHM\Core\Logger;

/**
 * HestiaCP Server Module for OpenWHM
 * 
 * API Documentation: https://docs.hestiacp.com/admin_docs/api.html
 */
class hestiacp
{
    private $host;
    private $port;
    private $apiKey;
    private $apiSecret;
    
    /**
     * Get module configuration fields
     */
    public static function getConfigFields()
    {
        return [
            'hostname' => [
                'label' => 'Hostname',
                'type' => 'text',
                'description' => 'HestiaCP server hostname or IP address'
            ],
            'port' => [
                'label' => 'Port',
                'type' => 'text',
                'default' => '8083',
                'description' => 'HestiaCP port (default: 8083)'
            ],
            'api_key' => [
                'label' => 'API Key',
                'type' => 'text',
                'description' => 'HestiaCP API Key (generate in admin panel under API)'
            ],
            'api_secret' => [
                'label' => 'API Secret',
                'type' => 'password',
                'description' => 'HestiaCP API Secret (generate in admin panel under API)'
            ]
        ];
    }
    
    /**
     * Get product configuration fields
     */
    public static function getProductConfigFields()
    {
        return [
            'package' => [
                'label' => 'Hosting Package',
                'type' => 'text',
                'description' => 'HestiaCP package name (e.g., default)'
            ],
            'shell' => [
                'label' => 'Shell Access',
                'type' => 'select',
                'options' => ['nologin' => 'No Shell', 'bash' => 'Bash'],
                'default' => 'nologin'
            ],
            'quota' => [
                'label' => 'Disk Quota (MB)',
                'type' => 'text',
                'default' => 'unlimited'
            ],
            'bandwidth' => [
                'label' => 'Bandwidth (MB)',
                'type' => 'text',
                'default' => 'unlimited'
            ]
        ];
    }
    
    /**
     * Create hosting account
     */
    public function create($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        // Generate username and password
        $username = $this->generateUsername($service['domain']);
        $password = $this->generatePassword();
        
        // Get package name from product config
        $package = $product['config_option1'] ?? 'default';
        $shell = $product['config_option2'] ?? 'nologin';
        
        // Create user
        $result = $this->apiCall('v-add-user', [
            $username,
            $password,
            $client['email'],
            $package,
            $client['firstname'],
            $client['lastname']
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to create user'];
        }
        
        // Add domain
        if (!empty($service['domain'])) {
            $domainResult = $this->apiCall('v-add-domain', [
                $username,
                $service['domain']
            ]);
            
            if ($domainResult['code'] !== 0) {
                Logger::warning("Failed to add domain: " . ($domainResult['error'] ?? 'Unknown error'));
            }
            
            // Add SSL if available
            $this->apiCall('v-add-letsencrypt-domain', [
                $username,
                $service['domain']
            ]);
        }
        
        // Update service with credentials
        $db = Database::getInstance();
        $db->update('services', [
            'username' => $username,
            'password' => $this->encryptPassword($password)
        ], 'id = ?', [$service['id']]);
        
        Logger::info("HestiaCP account created: {$username}");
        
        return [
            'success' => true,
            'username' => $username,
            'password' => $password
        ];
    }
    
    /**
     * Suspend hosting account
     */
    public function suspend($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-suspend-user', [
            $service['username']
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to suspend user'];
        }
        
        Logger::info("HestiaCP account suspended: {$service['username']}");
        
        return ['success' => true];
    }
    
    /**
     * Unsuspend hosting account
     */
    public function unsuspend($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-unsuspend-user', [
            $service['username']
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to unsuspend user'];
        }
        
        Logger::info("HestiaCP account unsuspended: {$service['username']}");
        
        return ['success' => true];
    }
    
    /**
     * Terminate hosting account
     */
    public function terminate($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-delete-user', [
            $service['username']
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to delete user'];
        }
        
        Logger::info("HestiaCP account terminated: {$service['username']}");
        
        return ['success' => true];
    }
    
    /**
     * Change package
     */
    public function changePackage($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        $package = $product['config_option1'] ?? 'default';
        
        $result = $this->apiCall('v-change-user-package', [
            $service['username'],
            $package
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to change package'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Change password
     */
    public function changePassword($service, $server, $client, $product, $newPassword = null)
    {
        $this->setServerConfig($server);
        
        $password = $newPassword ?? $this->generatePassword();
        
        $result = $this->apiCall('v-change-user-password', [
            $service['username'],
            $password
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to change password'];
        }
        
        // Update service password
        $db = Database::getInstance();
        $db->update('services', [
            'password' => $this->encryptPassword($password)
        ], 'id = ?', [$service['id']]);
        
        return [
            'success' => true,
            'password' => $password
        ];
    }
    
    /**
     * Get usage statistics
     */
    public function getUsage($service, $server, $client, $product)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-user', [
            $service['username'],
            'json'
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to get usage'];
        }
        
        $data = json_decode($result['output'], true);
        $user = $data[$service['username']] ?? [];
        
        return [
            'success' => true,
            'disk_usage' => $user['U_DISK'] ?? 0,
            'disk_limit' => $user['DISK_QUOTA'] ?? 'unlimited',
            'bandwidth_usage' => $user['U_BANDWIDTH'] ?? 0,
            'bandwidth_limit' => $user['BANDWIDTH'] ?? 'unlimited',
            'web_domains' => $user['U_WEB_DOMAINS'] ?? 0,
            'mail_domains' => $user['U_MAIL_DOMAINS'] ?? 0,
            'databases' => $user['U_DATABASES'] ?? 0,
            'suspended' => $user['SUSPENDED'] === 'yes'
        ];
    }
    
    /**
     * Add domain to account
     */
    public function addDomain($service, $server, $domain)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-add-domain', [
            $service['username'],
            $domain
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to add domain'];
        }
        
        // Try to add SSL
        $this->apiCall('v-add-letsencrypt-domain', [
            $service['username'],
            $domain
        ]);
        
        return ['success' => true];
    }
    
    /**
     * Delete domain from account
     */
    public function deleteDomain($service, $server, $domain)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-delete-domain', [
            $service['username'],
            $domain
        ]);
        
        return ['success' => $result['code'] === 0, 'error' => $result['error'] ?? null];
    }
    
    /**
     * List domains on account
     */
    public function listDomains($service, $server)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-web-domains', [
            $service['username'],
            'json'
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list domains'];
        }
        
        $domains = json_decode($result['output'], true) ?? [];
        
        return [
            'success' => true,
            'domains' => array_keys($domains)
        ];
    }
    
    /**
     * Create database
     */
    public function createDatabase($service, $server, $dbName, $dbUser, $dbPassword)
    {
        $this->setServerConfig($server);
        
        $username = $service['username'];
        $fullDbName = $username . '_' . $dbName;
        $fullDbUser = $username . '_' . $dbUser;
        
        // Add database
        $result = $this->apiCall('v-add-database', [
            $username,
            $dbName,
            $dbUser,
            $dbPassword
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to create database'];
        }
        
        return [
            'success' => true,
            'database' => $fullDbName,
            'username' => $fullDbUser,
            'password' => $dbPassword
        ];
    }
    
    /**
     * List databases
     */
    public function listDatabases($service, $server)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-databases', [
            $service['username'],
            'json'
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list databases'];
        }
        
        $databases = json_decode($result['output'], true) ?? [];
        
        return [
            'success' => true,
            'databases' => array_keys($databases)
        ];
    }
    
    /**
     * Create email account
     */
    public function createEmail($service, $server, $domain, $account, $password)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-add-mail-account', [
            $service['username'],
            $domain,
            $account,
            $password
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to create email'];
        }
        
        return [
            'success' => true,
            'email' => $account . '@' . $domain
        ];
    }
    
    /**
     * List email accounts
     */
    public function listEmails($service, $server, $domain)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-mail-accounts', [
            $service['username'],
            $domain,
            'json'
        ]);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list emails'];
        }
        
        $accounts = json_decode($result['output'], true) ?? [];
        
        return [
            'success' => true,
            'emails' => array_keys($accounts)
        ];
    }
    
    /**
     * Test connection to server
     */
    public function testConnection($server)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-sys-info', ['json']);
        
        return [
            'success' => $result['code'] === 0,
            'error' => $result['error'] ?? null
        ];
    }
    
    /**
     * Get server statistics
     */
    public function getServerStats($server)
    {
        $this->setServerConfig($server);
        
        $result = $this->apiCall('v-list-sys-info', ['json']);
        
        if ($result['code'] !== 0) {
            return ['success' => false, 'error' => $result['error']];
        }
        
        $info = json_decode($result['output'], true);
        
        return [
            'success' => true,
            'hostname' => $info['HOSTNAME'] ?? '',
            'os' => $info['OS'] ?? '',
            'version' => $info['HESTIA'] ?? '',
            'uptime' => $info['UPTIME'] ?? '',
            'load' => $info['LOAD_ONE'] ?? '',
            'memory_used' => $info['MEM_USAGE'] ?? 0,
            'disk_used' => $info['DISK_USAGE'] ?? 0
        ];
    }
    
    /**
     * Get login URL for client
     */
    public function getLoginUrl($service, $server)
    {
        return "https://{$server['hostname']}:{$server['port']}/";
    }
    
    /**
     * Generate SSO token for client login
     */
    public function clientAreaSingleSignOn($service, $server)
    {
        // HestiaCP doesn't have built-in SSO, return login URL
        return [
            'success' => true,
            'redirect_url' => $this->getLoginUrl($service, $server)
        ];
    }
    
    /**
     * Set server configuration
     */
    private function setServerConfig($server)
    {
        $this->host = $server['hostname'];
        $this->port = $server['port'] ?? 8083;
        $this->apiKey = $server['api_key'];
        $this->apiSecret = $this->decryptPassword($server['api_secret']);
    }
    
    /**
     * Make API call to HestiaCP using API Key authentication
     */
    private function apiCall($command, $args = [])
    {
        $url = "https://{$this->host}:{$this->port}/api/";
        
        // Generate timestamp
        $timestamp = time();
        
        // Build query parameters
        $query = [
            'cmd' => $command,
            'key' => $this->apiKey,
            'timestamp' => $timestamp
        ];
        
        // Add command arguments
        foreach ($args as $i => $arg) {
            $query['arg' . ($i + 1)] = $arg;
        }
        
        // Create request signature
        $sig = $this->createSignature($query);
        $query['signature'] = $sig;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            Logger::error("HestiaCP API error: " . $error);
            return ['code' => -1, 'error' => 'Connection error: ' . $error];
        }
        
        if ($httpCode !== 200) {
            Logger::error("HestiaCP HTTP error: {$httpCode}");
            return ['code' => $httpCode, 'error' => 'HTTP Error ' . $httpCode, 'output' => $response];
        }
        
        // Parse response
        $lines = explode("\n", trim($response));
        $returnCode = (int) end($lines);
        $output = implode("\n", array_slice($lines, 0, -1));
        
        if ($returnCode !== 0) {
            return [
                'code' => $returnCode,
                'error' => $this->getErrorMessage($returnCode),
                'output' => $output
            ];
        }
        
        return [
            'code' => 0,
            'output' => $output
        ];
    }
    
    /**
     * Create HMAC-SHA256 signature for API request
     */
    private function createSignature($params)
    {
        // Sort parameters alphabetically
        ksort($params);
        
        // Create query string without signature
        $queryString = http_build_query($params);
        
        // Create signature
        return hash_hmac('sha256', $queryString, $this->apiSecret);
    }
    
    /**
     * Get error message for return code
     */
    private function getErrorMessage($code)
    {
        $errors = [
            1 => 'Not enough arguments provided',
            2 => 'Object or argument is not valid',
            3 => 'Object doesn\'t exist',
            4 => 'Object already exists',
            5 => 'Object is suspended',
            6 => 'Object is already unsuspended',
            7 => 'Object can\'t be deleted because is used by another object',
            8 => 'Object cannot be created because of hosting package limits',
            9 => 'Wrong password',
            10 => 'Object cannot be accessed',
            11 => 'Subsystem is disabled',
            12 => 'Configuration is broken',
            13 => 'Not enough disk space',
            14 => 'Server is too busy',
            15 => 'Connection failed',
            16 => 'FTP server is not responding',
            17 => 'File upload has failed',
            18 => 'File download has failed',
            19 => 'Limit has been reached'
        ];
        
        return $errors[$code] ?? "Unknown error (code: {$code})";
    }
    
    /**
     * Generate username from domain
     */
    private function generateUsername($domain)
    {
        // Remove TLD and special characters
        $domain = preg_replace('/\.[a-z]{2,}$/i', '', $domain);
        $username = preg_replace('/[^a-z0-9]/i', '', $domain);
        
        // Limit to 8 characters and add random suffix
        $username = substr(strtolower($username), 0, 8);
        $username .= rand(100, 999);
        
        return $username;
    }
    
    /**
     * Generate secure password
     */
    private function generatePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Encrypt password for storage
     */
    private function encryptPassword($password)
    {
        return openssl_encrypt($password, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
    }
    
    /**
     * Decrypt stored password
     */
    private function decryptPassword($encrypted)
    {
        return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
    }
}
