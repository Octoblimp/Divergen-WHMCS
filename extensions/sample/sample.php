<?php
/**
 * OpenWHM Sample Extension - Main File
 * 
 * This extension demonstrates how to build extensions for OpenWHM.
 * It shows how to:
 * - Register and handle hooks
 * - Add admin pages
 * - Add client area widgets
 * - Store and retrieve extension settings
 * - Create custom tables
 * - Interact with the OpenWHM API
 */

namespace OpenWHM\Extensions\Sample;

use OpenWHM\Database;
use OpenWHM\Hooks;
use OpenWHM\Settings;

class SampleExtension
{
    private $db;
    private $settings = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    /**
     * Load extension settings
     */
    private function loadSettings()
    {
        $settings = $this->db->fetch(
            "SELECT config FROM {$this->db->table('extensions')} WHERE name = ?",
            ['Sample Extension']
        );
        
        if ($settings && $settings['config']) {
            $this->settings = json_decode($settings['config'], true) ?: [];
        }
    }
    
    /**
     * Get a setting value
     */
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Register all hooks
     */
    public function registerHooks()
    {
        // Client login hook
        Hooks::register('ClientLogin', function($params) {
            $this->onClientLogin($params);
        });
        
        // Client logout hook
        Hooks::register('ClientLogout', function($params) {
            $this->onClientLogout($params);
        });
        
        // Invoice paid hook
        Hooks::register('InvoicePaid', function($params) {
            $this->onInvoicePaid($params);
        });
        
        // Ticket opened hook
        Hooks::register('TicketOpened', function($params) {
            $this->onTicketOpened($params);
        });
        
        // Service created hook
        Hooks::register('ServiceCreated', function($params) {
            $this->onServiceCreated($params);
        });
        
        // Daily job hook (called by cron)
        Hooks::register('DailyJob', function($params) {
            $this->onDailyJob($params);
        });
        
        // Add sidebar widget to client area
        Hooks::register('ClientAreaSidebarWidgets', function(&$widgets) {
            $widgets[] = [
                'name' => 'sample_welcome',
                'title' => 'Welcome',
                'content' => $this->renderWelcomeWidget()
            ];
        });
        
        // Add admin menu items
        Hooks::register('AdminMenuItems', function(&$menu) {
            $menu['extensions']['items'][] = [
                'title' => 'Sample Extension',
                'icon' => 'fas fa-puzzle-piece',
                'children' => [
                    ['title' => 'Dashboard', 'url' => 'index.php?page=ext_sample_dashboard'],
                    ['title' => 'Settings', 'url' => 'index.php?page=ext_sample_settings'],
                    ['title' => 'Logs', 'url' => 'index.php?page=ext_sample_logs']
                ]
            ];
        });
    }
    
    /**
     * Handle client login event
     */
    private function onClientLogin($params)
    {
        if (!$this->getSetting('log_logins', true)) {
            return;
        }
        
        $clientId = $params['client_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if ($clientId) {
            $this->logEvent('login', $clientId, [
                'ip_address' => $ipAddress,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        }
    }
    
    /**
     * Handle client logout event
     */
    private function onClientLogout($params)
    {
        $clientId = $params['client_id'] ?? null;
        
        if ($clientId) {
            $this->logEvent('logout', $clientId);
        }
    }
    
    /**
     * Handle invoice paid event
     */
    private function onInvoicePaid($params)
    {
        $invoiceId = $params['invoice_id'] ?? null;
        $clientId = $params['client_id'] ?? null;
        $amount = $params['amount'] ?? 0;
        
        if ($invoiceId) {
            $this->logEvent('invoice_paid', $clientId, [
                'invoice_id' => $invoiceId,
                'amount' => $amount
            ]);
            
            // Example: Send a thank you notification
            // You could integrate with external services here
        }
    }
    
    /**
     * Handle ticket opened event
     */
    private function onTicketOpened($params)
    {
        $ticketId = $params['ticket_id'] ?? null;
        $clientId = $params['client_id'] ?? null;
        $subject = $params['subject'] ?? '';
        
        if ($ticketId) {
            $this->logEvent('ticket_opened', $clientId, [
                'ticket_id' => $ticketId,
                'subject' => $subject
            ]);
        }
    }
    
    /**
     * Handle service created event
     */
    private function onServiceCreated($params)
    {
        $serviceId = $params['service_id'] ?? null;
        $clientId = $params['client_id'] ?? null;
        $productId = $params['product_id'] ?? null;
        
        if ($serviceId) {
            $this->logEvent('service_created', $clientId, [
                'service_id' => $serviceId,
                'product_id' => $productId
            ]);
        }
    }
    
    /**
     * Handle daily cron job
     */
    private function onDailyJob($params)
    {
        // Cleanup old logs (older than 30 days)
        $this->cleanupOldLogs(30);
        
        $this->logEvent('daily_job', null, [
            'message' => 'Daily job completed successfully'
        ]);
    }
    
    /**
     * Log an event to the extension's log table
     */
    private function logEvent($event, $clientId = null, $data = [])
    {
        // Ensure table exists
        $this->ensureLogTableExists();
        
        $this->db->insert('sample_extension_logs', [
            'event' => $event,
            'client_id' => $clientId,
            'data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Ensure the log table exists
     */
    private function ensureLogTableExists()
    {
        static $checked = false;
        
        if ($checked) {
            return;
        }
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->db->table('sample_extension_logs')} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event VARCHAR(50) NOT NULL,
                client_id INT NULL,
                data TEXT NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_event (event),
                INDEX idx_client_id (client_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $checked = true;
    }
    
    /**
     * Cleanup old log entries
     */
    private function cleanupOldLogs($days = 30)
    {
        $this->ensureLogTableExists();
        
        $this->db->query(
            "DELETE FROM {$this->d…