<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Order Model
 */
class Order extends Model
{
    protected $table = 'orders';
    
    protected $fillable = [
        'order_number', 'client_id', 'invoice_id', 'promo_code', 'promo_id',
        'amount', 'payment_method', 'status', 'ip_address', 'fraud_score',
        'fraud_output', 'notes'
    ];
    
    /**
     * Create new order
     */
    public function create($data)
    {
        // Generate order number
        if (empty($data['order_number'])) {
            $data['order_number'] = $this->generateOrderNumber();
        }
        
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $orderId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('OrderCreated', [
            'order_id' => $orderId,
            'data' => $data
        ]);
        
        Logger::activity('order_created', "Order #{$data['order_number']} created", $data['client_id']);
        
        return $orderId;
    }
    
    /**
     * Generate unique order number
     */
    public function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
    
    /**
     * Get order with details
     */
    public function getWithDetails($orderId)
    {
        return $this->db->fetch(
            "SELECT o.*, c.firstname, c.lastname, c.email, c.company,
                    i.invoice_number, i.total as invoice_total, i.status as invoice_status
             FROM {$this->getTable()} o
             LEFT JOIN {$this->db->table('clients')} c ON o.client_id = c.id
             LEFT JOIN {$this->db->table('invoices')} i ON o.invoice_id = i.id
             WHERE o.id = ?",
            [$orderId]
        );
    }
    
    /**
     * Accept order and activate services
     */
    public function accept($orderId)
    {
        $order = $this->find($orderId);
        
        if (!$order || $order['status'] !== 'pending') {
            return false;
        }
        
        // Update order status
        $this->update($orderId, ['status' => 'active']);
        
        // Activate all related services
        $services = $this->db->fetchAll(
            "SELECT id FROM {$this->db->table('services')} WHERE order_id = ? AND status = 'pending'",
            [$orderId]
        );
        
        $serviceModel = new Service();
        foreach ($services as $service) {
            $serviceModel->activate($service['id']);
        }
        
        // Activate all related domains
        $domains = $this->db->fetchAll(
            "SELECT id FROM {$this->db->table('domains')} WHERE order_id = ? AND status = 'pending'",
            [$orderId]
        );
        
        $domainModel = new Domain();
        foreach ($domains as $domain) {
            $domainModel->activate($domain['id']);
        }
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('OrderAccepted', [
            'order_id' => $orderId,
            'order' => $order
        ]);
        
        Logger::activity('order_accepted', "Order #{$order['order_number']} accepted", $order['client_id']);
        
        return true;
    }
    
    /**
     * Cancel order
     */
    public function cancel($orderId, $reason = '')
    {
        $order = $this->find($orderId);
        
        if (!$order) {
            return false;
        }
        
        // Update order status
        $this->update($orderId, [
            'status' => 'cancelled',
            'notes' => $reason
        ]);
        
        // Cancel all related services
        $this->db->update('services', 
            ['status' => 'cancelled'], 
            'order_id = ?', 
            [$orderId]
        );
        
        // Cancel all related domains
        $this->db->update('domains', 
            ['status' => 'cancelled'], 
            'order_id = ?', 
            [$orderId]
        );
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('OrderCancelled', [
            'order_id' => $orderId,
            'order' => $order,
            'reason' => $reason
        ]);
        
        Logger::activity('order_cancelled', "Order #{$order['order_number']} cancelled: {$reason}", $order['client_id']);
        
        return true;
    }
    
    /**
     * Mark order as fraud
     */
    public function markFraud($orderId, $reason = '')
    {
        $order = $this->find($orderId);
        
        if (!$order) {
            return false;
        }
        
        // Update order status
        $this->update($orderId, [
            'status' => 'fraud',
            'notes' => $reason
        ]);
        
        // Mark related items as fraud
        $this->db->update('services', 
            ['status' => 'fraud'], 
            'order_id = ?', 
            [$orderId]
        );
        
        $this->db->update('domains', 
            ['status' => 'fraud'], 
            'order_id = ?', 
            [$orderId]
        );
        
        Logger::activity('order_fraud', "Order #{$order['order_number']} marked as fraud: {$reason}", $order['client_id']);
        
        return true;
    }
    
    /**
     * Get services for order
     */
    public function getServices($orderId)
    {
        return $this->db->fetchAll(
            "SELECT s.*, p.name as product_name, p.type as product_type
             FROM {$this->db->table('services')} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.order_id = ?",
            [$orderId]
        );
    }
    
    /**
     * Get domains for order
     */
    public function getDomains($orderId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('domains')} WHERE order_id = ?",
            [$orderId]
        );
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        $revenueToday = $this->db->fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->getTable()} 
             WHERE status = 'active' AND DATE(created_at) = CURDATE()"
        );
        
        $revenueMonth = $this->db->fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->getTable()} 
             WHERE status = 'active' AND created_at >= ?",
            [date('Y-m-01')]
        );
        
        return [
            'total' => $this->count(),
            'pending' => $this->count("status = 'pending'"),
            'active' => $this->count("status = 'active'"),
            'cancelled' => $this->count("status = 'cancelled'"),
            'fraud' => $this->count("status = 'fraud'"),
            'today' => $this->count("DATE(created_at) = CURDATE()"),
            'revenue_today' => $revenueToday,
            'revenue_month' => $revenueMonth
        ];
    }
}
