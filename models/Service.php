<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Service Model
 */
class Service extends Model
{
    protected $table = 'services';
    
    protected $fillable = [
        'client_id', 'order_id', 'product_id', 'server_id', 'domain',
        'username', 'password', 'billing_cycle', 'amount', 'first_payment_amount',
        'registration_date', 'next_due_date', 'next_invoice_date', 'termination_date',
        'completed_date', 'status', 'suspend_reason', 'override_suspend',
        'override_auto_terminate', 'dedicated_ip', 'assigned_ips', 'disk_usage',
        'disk_limit', 'bandwidth_usage', 'bandwidth_limit', 'last_update', 'notes'
    ];
    
    protected $hidden = ['password'];
    
    /**
     * Get service with product and server
     */
    public function getWithDetails($serviceId)
    {
        return $this->db->fetch(
            "SELECT s.*, p.name as product_name, p.module, p.type as product_type,
                    sv.hostname as server_hostname, sv.ip_address as server_ip,
                    c.email as client_email, c.firstname, c.lastname
             FROM {$this->getTable()} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             LEFT JOIN {$this->db->table('servers')} sv ON s.server_id = sv.id
             LEFT JOIN {$this->db->table('clients')} c ON s.client_id = c.id
             WHERE s.id = ?",
            [$serviceId]
        );
    }
    
    /**
     * Create new service
     */
    public function create($data)
    {
        // Get product
        $product = new Product();
        $productData = $product->find($data['product_id']);
        
        // Get available server
        $server = $product->getAvailableServer($productData);
        
        if ($server) {
            $data['server_id'] = $server['id'];
        }
        
        // Set dates
        $data['registration_date'] = date('Y-m-d');
        
        if (empty($data['next_due_date'])) {
            $data['next_due_date'] = $this->calculateNextDueDate($data['billing_cycle']);
        }
        
        $serviceId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ServiceCreated', [
            'service_id' => $serviceId,
            'data' => $data
        ]);
        
        Logger::activity('service_created', "Service created for product {$productData['name']}", $data['client_id']);
        
        return $serviceId;
    }
    
    /**
     * Calculate next due date based on billing cycle
     */
    private function calculateNextDueDate($billingCycle, $fromDate = null)
    {
        $fromDate = $fromDate ?? date('Y-m-d');
        
        $intervals = [
            'monthly' => '+1 month',
            'quarterly' => '+3 months',
            'semiannually' => '+6 months',
            'annually' => '+1 year',
            'biennially' => '+2 years',
            'triennially' => '+3 years',
            'onetime' => null,
            'free' => null
        ];
        
        if (isset($intervals[$billingCycle]) && $intervals[$billingCycle]) {
            return date('Y-m-d', strtotime($fromDate . ' ' . $intervals[$billingCycle]));
        }
        
        return null;
    }
    
    /**
     * Activate service
     */
    public function activate($serviceId)
    {
        $service = $this->getWithDetails($serviceId);
        
        if (!$service) {
            return false;
        }
        
        // If module exists, create account
        if ($service['module']) {
            $result = $this->callModule($service, 'create');
            
            if (!$result['success']) {
                Logger::error("Failed to create account: " . ($result['error'] ?? 'Unknown error'));
                return false;
            }
        }
        
        // Update service status
        $this->update($serviceId, [
            'status' => 'active',
            'completed_date' => date('Y-m-d')
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ServiceActivate', [
            'service_id' => $serviceId,
            'service' => $service
        ]);
        
        Logger::activity('service_activated', "Service #{$serviceId} activated", $service['client_id']);
        
        return true;
    }
    
    /**
     * Suspend service
     */
    public function suspend($serviceId, $reason = '')
    {
        $service = $this->getWithDetails($serviceId);
        
        if (!$service || $service['override_suspend']) {
            return false;
        }
        
        // If module exists, suspend account
        if ($service['module']) {
            $result = $this->callModule($service, 'suspend');
            
            if (!$result['success']) {
                Logger::error("Failed to suspend account: " . ($result['error'] ?? 'Unknown error'));
            }
        }
        
        // Update service status
        $this->update($serviceId, [
            'status' => 'suspended',
            'suspend_reason' => $reason
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ServiceSuspend', [
            'service_id' => $serviceId,
            'service' => $service,
            'reason' => $reason
        ]);
        
        Logger::activity('service_suspended', "Service #{$serviceId} suspended: {$reason}", $service['client_id']);
        
        return true;
    }
    
    /**
     * Unsuspend service
     */
    public function unsuspend($serviceId)
    {
        $service = $this->getWithDetails($serviceId);
        
        if (!$service) {
            return false;
        }
        
        // If module exists, unsuspend account
        if ($service['module']) {
            $result = $this->callModule($service, 'unsuspend');
            
            if (!$result['success']) {
                Logger::error("Failed to unsuspend account: " . ($result['error'] ?? 'Unknown error'));
            }
        }
        
        // Update service status
        $this->update($serviceId, [
            'status' => 'active',
            'suspend_reason' => null
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ServiceUnsuspend', [
            'service_id' => $serviceId,
            'service' => $service
        ]);
        
        Logger::activity('service_unsuspended', "Service #{$serviceId} unsuspended", $service['client_id']);
        
        return true;
    }
    
    /**
     * Terminate service
     */
    public function terminate($serviceId)
    {
        $service = $this->getWithDetails($serviceId);
        
        if (!$service || $service['override_auto_terminate']) {
            return false;
        }
        
        // If module exists, terminate account
        if ($service['module']) {
            $result = $this->callModule($service, 'terminate');
            
            if (!$result['success']) {
                Logger::error("Failed to terminate account: " . ($result['error'] ?? 'Unknown error'));
            }
        }
        
        // Update service status
        $this->update($serviceId, [
            'status' => 'terminated',
            'termination_date' => date('Y-m-d')
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ServiceTerminate', [
            'service_id' => $serviceId,
            'service' => $service
        ]);
        
        Logger::activity('service_terminated', "Service #{$serviceId} terminated", $service['client_id']);
        
        return true;
    }
    
    /**
     * Call module function
     */
    public function callModule($service, $function)
    {
        $moduleName = $service['module'];
        $moduleClass = "OpenWHM\\Modules\\Servers\\{$moduleName}\\{$moduleName}";
        
        if (!class_exists($moduleClass)) {
            // Try to load module
            $modulePath = ROOT_PATH . "/modules/servers/{$moduleName}/{$moduleName}.php";
            
            if (file_exists($modulePath)) {
                require_once $modulePath;
            } else {
                return ['success' => false, 'error' => "Module not found: {$moduleName}"];
            }
        }
        
        if (!class_exists($moduleClass)) {
            return ['success' => false, 'error' => "Module class not found: {$moduleClass}"];
        }
        
        $module = new $moduleClass();
        
        if (!method_exists($module, $function)) {
            return ['success' => false, 'error' => "Module function not found: {$function}"];
        }
        
        // Get server details
        $server = $this->db->fetch(
            "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
            [$service['server_id']]
        );
        
        // Get client details
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
            [$service['client_id']]
        );
        
        // Get product details
        $product = $this->db->fetch(
            "SELECT * FROM {$this->db->table('products')} WHERE id = ?",
            [$service['product_id']]
        );
        
        return $module->$function($service, $server, $client, $product);
    }
    
    /**
     * Get services due for invoice
     */
    public function getDueForInvoice($days = 14)
    {
        $dueDate = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->db->fetchAll(
            "SELECT s.*, c.email as client_email, c.firstname, c.lastname,
                    p.name as product_name
             FROM {$this->getTable()} s
             LEFT JOIN {$this->db->table('clients')} c ON s.client_id = c.id
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.status = 'active' 
             AND s.billing_cycle NOT IN ('free', 'onetime')
             AND s.next_due_date <= ?
             AND (s.next_invoice_date IS NULL OR s.next_invoice_date <= CURDATE())
             ORDER BY s.next_due_date ASC",
            [$dueDate]
        );
    }
    
    /**
     * Get overdue services for suspension
     */
    public function getOverdue($graceDays = 3)
    {
        $date = date('Y-m-d', strtotime("-{$graceDays} days"));
        
        return $this->db->fetchAll(
            "SELECT s.*, p.name as product_name
             FROM {$this->getTable()} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.status = 'active'
             AND s.override_suspend = 0
             AND s.next_due_date < ?
             AND s.client_id IN (
                 SELECT DISTINCT client_id FROM {$this->db->table('invoices')}
                 WHERE status = 'unpaid' AND due_date < CURDATE()
             )",
            [$date]
        );
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->count(),
            'active' => $this->count("status = 'active'"),
            'pending' => $this->count("status = 'pending'"),
            'suspended' => $this->count("status = 'suspended'"),
            'terminated' => $this->count("status = 'terminated'")
        ];
    }
}
