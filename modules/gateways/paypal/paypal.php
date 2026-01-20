<?php
/**
 * PayPal Payment Gateway
 */

namespace OpenWHM\Modules\Gateways;

class PayPal extends GatewayModule
{
    protected $name = 'paypal';
    protected $displayName = 'PayPal';
    
    private $apiUrl;
    
    public function __construct()
    {
        $this->apiUrl = $this->getSetting('sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }
    
    /**
     * Get configuration fields
     */
    public function getConfigFields()
    {
        return [
            'client_id' => [
                'label' => 'Client ID',
                'type' => 'text',
                'description' => 'PayPal API Client ID'
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'type' => 'password',
                'description' => 'PayPal API Client Secret'
            ],
            'sandbox' => [
                'label' => 'Sandbox Mode',
                'type' => 'checkbox',
                'description' => 'Enable sandbox/test mode'
            ]
        ];
    }
    
    /**
     * Process payment - Create PayPal order
     */
    public function processPayment($invoice, $amount, $currency)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }
        
        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'INV-' . $invoice['id'],
                    'description' => 'Invoice #' . $invoice['invoice_num'],
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => COMPANY_NAME,
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => BASE_URL . '/payment/paypal/return?invoice_id=' . $invoice['id'],
                'cancel_url' => BASE_URL . '/payment/paypal/cancel?invoice_id=' . $invoice['id']
            ]
        ];
        
        $response = $this->apiRequest('/v2/checkout/orders', $orderData, $accessToken);
        
        if (isset($response['id'])) {
            // Find approval URL
            $approvalUrl = null;
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }
            
            return [
                'success' => true,
                'order_id' => $response['id'],
                'redirect_url' => $approvalUrl
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to create PayPal order'
        ];
    }
    
    /**
     * Capture payment after approval
     */
    public function capturePayment($orderId)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }
        
        $response = $this->apiRequest("/v2/checkout/orders/{$orderId}/capture", [], $accessToken);
        
        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            $capture = $response['purchase_units'][0]['payments']['captures'][0];
            
            return [
                'success' => true,
                'transaction_id' => $capture['id'],
                'amount' => $capture['amount']['value'],
                'status' => 'completed'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to capture payment'
        ];
    }
    
    /**
     * Handle callback/webhook
     */
    public function handleCallback($data)
    {
        $eventType = $data['event_type'] ?? null;
        $resource = $data['resource'] ?? [];
        
        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                // Order approved, capture payment
                $orderId = $resource['id'];
                $result = $this->capturePayment($orderId);
                
                if ($result['success']) {
                    // Mark invoice as paid
                    $referenceId = $resource['purchase_units'][0]['reference_id'] ?? null;
                    if ($referenceId && strpos($referenceId, 'INV-') === 0) {
                        $invoiceId = (int) substr($referenceId, 4);
                        $this->markInvoicePaid($invoiceId, $result['transaction_id'], $result['amount']);
                    }
                }
                break;
                
            case 'PAYMENT.CAPTURE.COMPLETED':
                // Payment captured
                $transactionId = $resource['id'];
                $invoiceId = $resource['invoice_id'] ?? null;
                $amount = $resource['amount']['value'] ?? 0;
                
                if ($invoiceId) {
                    $this->markInvoicePaid($invoiceId, $transactionId, $amount);
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
     * Get access token
     */
    private function getAccessToken()
    {
        $clientId = $this->getSetting('client_id');
        $clientSecret = $this->getSetting('client_secret');
        
        $ch = curl_init($this->apiUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_USERPWD => $clientId . ':' . $clientSecret
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        return $data['access_token'] ?? null;
    }
    
    /**
     * Make API request
     */
    private function apiRequest($endpoint, $data, $accessToken)
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
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
            $invoiceModel->markPaid($invoiceId, 'PayPal', $transactionId);
        }
    }
}
