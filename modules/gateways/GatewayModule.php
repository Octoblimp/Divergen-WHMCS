<?php
/**
 * Payment Gateway Base Class
 */

namespace OpenWHM\Modules\Gateways;

abstract class GatewayModule
{
    protected $name;
    protected $displayName;
    protected $settings = [];
    
    /**
     * Get gateway name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get display name
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
    
    /**
     * Get configuration fields
     */
    abstract public function getConfigFields();
    
    /**
     * Process payment
     */
    abstract public function processPayment($invoice, $amount, $currency);
    
    /**
     * Handle callback/webhook
     */
    abstract public function handleCallback($data);
    
    /**
     * Get payment URL (for redirect gateways)
     */
    public function getPaymentUrl($invoice, $amount, $currency)
    {
        return null;
    }
    
    /**
     * Get setting value
     */
    protected function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Set settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }
    
    /**
     * Log transaction
     */
    protected function logTransaction($invoiceId, $transactionId, $amount, $status, $rawData = null)
    {
        $db = \OpenWHM\Core\Database::getInstance();
        
        return $db->insert('transactions', [
            'invoice_id' => $invoiceId,
            'gateway' => $this->name,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => $status,
            'raw_data' => $rawData ? json_encode($rawData) : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Add funds to client
     */
    protected function addCredit($clientId, $amount, $description)
    {
        $db = \OpenWHM\Core\Database::getInstance();
        
        $db->query(
            "UPDATE {$db->table('clients')} SET credit = credit + ? WHERE id = ?",
            [$amount, $clientId]
        );
        
        // Log credit
        $db->insert('logs', [
            'type' => 'credit',
            'client_id' => $clientId,
            'message' => "Credit added: $" . number_format($amount, 2) . " - " . $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
