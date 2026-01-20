<?php
/**
 * Bank Transfer / Offline Payment Gateway
 */

namespace OpenWHM\Modules\Gateways;

class BankTransfer extends GatewayModule
{
    protected $name = 'banktransfer';
    protected $displayName = 'Bank Transfer';
    
    /**
     * Get configuration fields
     */
    public function getConfigFields()
    {
        return [
            'account_name' => [
                'label' => 'Account Name',
                'type' => 'text',
                'description' => 'Bank account holder name'
            ],
            'account_number' => [
                'label' => 'Account Number',
                'type' => 'text',
                'description' => 'Bank account number'
            ],
            'bank_name' => [
                'label' => 'Bank Name',
                'type' => 'text',
                'description' => 'Name of the bank'
            ],
            'routing_number' => [
                'label' => 'Routing Number / SWIFT',
                'type' => 'text',
                'description' => 'Bank routing number or SWIFT code'
            ],
            'instructions' => [
                'label' => 'Payment Instructions',
                'type' => 'textarea',
                'description' => 'Additional instructions to show to clients'
            ]
        ];
    }
    
    /**
     * Process payment - Show bank details
     */
    public function processPayment($invoice, $amount, $currency)
    {
        // Bank transfer doesn't process online, just return the bank details
        return [
            'success' => true,
            'type' => 'offline',
            'details' => [
                'account_name' => $this->getSetting('account_name'),
                'account_number' => $this->getSetting('account_number'),
                'bank_name' => $this->getSetting('bank_name'),
                'routing_number' => $this->getSetting('routing_number'),
                'reference' => 'INV-' . $invoice['invoice_num'],
                'amount' => number_format($amount, 2) . ' ' . $currency,
                'instructions' => $this->getSetting('instructions')
            ]
        ];
    }
    
    /**
     * Handle callback - Not applicable for bank transfer
     */
    public function handleCallback($data)
    {
        return ['success' => true];
    }
    
    /**
     * Get payment instructions HTML
     */
    public function getPaymentInstructions($invoice, $amount, $currency)
    {
        $result = $this->processPayment($invoice, $amount, $currency);
        $details = $result['details'];
        
        $html = '<div class="alert alert-info">';
        $html .= '<h5><i class="fas fa-university me-2"></i>Bank Transfer Details</h5>';
        $html .= '<p>Please transfer the exact amount to the following bank account:</p>';
        $html .= '<table class="table table-sm table-borderless mb-0">';
        
        if ($details['bank_name']) {
            $html .= '<tr><td><strong>Bank Name:</strong></td><td>' . htmlspecialchars($details['bank_name']) . '</td></tr>';
        }
        if ($details['account_name']) {
            $html .= '<tr><td><strong>Account Name:</strong></td><td>' . htmlspecialchars($details['account_name']) . '</td></tr>';
        }
        if ($details['account_number']) {
            $html .= '<tr><td><strong>Account Number:</strong></td><td>' . htmlspecialchars($details['account_number']) . '</td></tr>';
        }
        if ($details['routing_number']) {
            $html .= '<tr><td><strong>Routing/SWIFT:</strong></td><td>' . htmlspecialchars($details['routing_number']) . '</td></tr>';
        }
        
        $html .= '<tr><td><strong>Reference:</strong></td><td><code>' . htmlspecialchars($details['reference']) . '</code></td></tr>';
        $html .= '<tr><td><strong>Amount:</strong></td><td><strong>' . htmlspecialchars($details['amount']) . '</strong></td></tr>';
        $html .= '</table>';
        
        if ($details['instructions']) {
            $html .= '<hr>';
            $html .= '<p class="mb-0">' . nl2br(htmlspecialchars($details['instructions'])) . '</p>';
        }
        
        $html .= '</div>';
        $html .= '<p class="text-muted"><small>Your order will be processed once payment is received and confirmed.</small></p>';
        
        return $html;
    }
}
