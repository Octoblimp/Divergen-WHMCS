<?php
/**
 * Stripe Payment Gateway
 */

namespace OpenWHM\Modules\Gateways;

class Stripe extends GatewayModule
{
    protected $name = 'stripe';
    protected $displayName = 'Stripe';
    
    private $apiUrl = 'https://api.stripe.com/v1';
    
    /**
     * Get configuration fields
     */
    public function getConfigFields()
    {
        return [
            'publishable_key' => [
                'label' => 'Publishable Key',
                'type' => 'text',
                'description' => 'Stripe Publishable Key'
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'type' => 'password',
                'description' => 'Stripe Secret Key'
            ],
            'webhook_secret' => [
                'label' => 'Webhook Secret',
                'type' => 'password',
                'description' => 'Stripe Webhook Signing Secret'
            ]
        ];
    }
    
    /**
     * Process payment - Create Stripe Checkout Session
     */
    public function processPayment($invoice, $amount, $currency)
    {
        $lineItems = [];
        
        // Get invoice items
        $db = \OpenWHM\Core\Database::getInstance();
        $items = $db->fetchAll(
            "SELECT * FROM {$db->table('invoice_items')} WHERE invoice_id = ?",
            [$invoice['id']]
        );
        
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => $item['description']
                    ],
                    'unit_amount' => (int) ($item['amount'] * 100)
                ],
                'quantity' => 1
            ];
        }
        
        // Create checkout session
        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => BASE_URL . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => BASE_URL . '/payment/stripe/cancel?invoice_id=' . $invoice['id'],
            'client_reference_id' => 'INV-' . $invoice['id'],
            'metadata' => [
                'invoice_id' => $invoice['id'],
                'client_id' => $invoice['client_id']
            ]
        ];
        
        $response = $this->apiRequest('/checkout/sessions', $sessionData);
        
        if (isset($response['id'])) {
            return [
                'success' => true,
                'session_id' => $response['id'],
                'redirect_url' => $response['url']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error']['message'] ?? 'Failed to create checkout session'
        ];
    }
    
    /**
     * Handle callback/webhook
     */
    public function handleCallback($data)
    {
        // Verify webhook signature
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $webhookSecret = $this->getSetting('webhook_secret');
        
        if (!$this->verifyWebhookSignature($payload, $sigHeader, $webhookSecret)) {
            return ['success' => false, 'error' => 'Invalid signature'];
        }
        
        $event = json_decode($payload, true);
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];
        
        switch ($type) {
            case 'checkout.session.completed':
                // Payment successful
                $invoiceId = $object['metadata']['invoice_id'] ?? null;
                $paymentIntentId = $object['payment_intent'];
                $amount = $object['amount_total'] / 100;
                
                if ($invoiceId) {
                    $this->markInvoicePaid($invoiceId, $paymentIntentId, $amount);
                }
                break;
                
            case 'payment_intent.succeeded':
                // Payment intent succeeded
                $invoiceId = $object['metadata']['invoice_id'] ?? null;
                $amount = $object['amount_received'] / 100;
                
                if ($invoiceId) {
                    $this->markInvoicePaid($invoiceId, $object['id'], $amount);
                }
                break;
                
            case 'payment_intent.payment_failed':
                // Payment failed
                $invoiceId = $object['metadata']['invoice_id'] ?? null;
                
                if ($invoiceId) {
                    $this->logTransaction($invoiceId, $object['id'], 0, 'failed', $object);
                }
                break;
        }
        
        return ['success' => true];
    }
    
    /**
     * Get payment URL
     */
    public function getPaymentUrl($invoice, $amount, $currency)
    {
        $result = $this->processPayment($invoice, $amount, $currency);
        
        if ($result['success']) {
            return $result['redirect_url'];
        }
        
        return null;
    }
    
    /**
     * Retrieve checkout session
     */
    public function retrieveSession($sessionId)
    {
        return $this->apiRequest('/checkout/sessions/' . $sessionId, [], 'GET');
    }
    
    /**
     * Make API request
     */
    private function apiRequest($endpoint, $data = [], $method = 'POST')
    {
        $secretKey = $this->getSetting('secret_key');
        
        $ch = curl_init($this->apiUrl . $endpoint);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secretKey,
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ];
        
        if ($method === 'POST' && !empty($data)) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($this->flattenArray($data));
        }
        
        curl_setopt_array($ch, $opts);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Flatten nested array for Stripe API
     */
    private function flattenArray($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}[{$key}]" : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
    
    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($payload, $sigHeader, $secret)
    {
        if (empty($secret)) {
            return true; // Skip verification if no secret configured
        }
        
        $elements = explode(',', $sigHeader);
        $timestamp = null;
        $signature = null;
        
        foreach ($elements as $element) {
            list($key, $value) = explode('=', $element, 2);
            if ($key === 't') $timestamp = $value;
            if ($key === 'v1') $signature = $value;
        }
        
        if (!$timestamp || !$signature) {
            return false;
        }
        
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Mark invoice as paid
     */
    private function markInvoicePaid($invoiceId, $transactionId, $amount)
    {
        $invoiceModel = new \OpenWHM\Models\Invoice();
        $invoice = $invoiceModel->find($invoiceId);
        
        if ($invoice && $invoice['status'] !== 'paid') {
            // Log transaction
            $this->logTransaction($invoiceId, $transactionId, $amount, 'completed');
            
            // Mark paid
            $invoiceModel->markPaid($invoiceId, 'Stripe', $transactionId);
        }
    }
}
