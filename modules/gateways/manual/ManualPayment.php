<?php
/**
 * Manual Payment Gateway - Custom Instructions
 * Supports: Zelle, Wire Transfer, Check, Custom methods
 */

namespace OpenWHM\Modules\Gateways;

class ManualPayment
{
    protected $db;
    protected $config = [];
    protected $methods = [];
    
    public function __construct()
    {
        $this->db = \OpenWHM\Core\Database::getInstance();
        $this->loadConfig();
    }
    
    /**
     * Load gateway configuration
     */
    protected function loadConfig(): void
    {
        $config = $this->db->fetch(
            "SELECT * FROM payment_gateways WHERE gateway = 'manual'",
            []
        );
        
        if ($config) {
            $this->config = json_decode($config['config'] ?? '{}', true) ?: [];
        }
        
        // Load all manual payment methods
        $this->methods = $this->db->fetchAll(
            "SELECT * FROM manual_payment_methods WHERE active = 1 ORDER BY sort_order ASC"
        ) ?: $this->getDefaultMethods();
    }
    
    /**
     * Get module info
     */
    public function getInfo(): array
    {
        return [
            'name' => 'Manual/Offline Payment',
            'description' => 'Accept manual payments like Zelle, Wire Transfer, Check, or custom methods',
            'version' => '1.0.0',
            'author' => 'OpenWHM'
        ];
    }
    
    /**
     * Get configuration fields
     */
    public function getConfigFields(): array
    {
        return [
            'enabled' => [
                'label' => 'Enable Manual Payments',
                'type' => 'checkbox',
                'default' => true
            ],
            'pending_status' => [
                'label' => 'Initial Invoice Status',
                'type' => 'select',
                'options' => [
                    'unpaid' => 'Unpaid',
                    'payment_pending' => 'Payment Pending'
                ],
                'default' => 'payment_pending'
            ],
            'confirmation_email' => [
                'label' => 'Send Payment Instructions Email',
                'type' => 'checkbox',
                'default' => true
            ],
            'admin_notification' => [
                'label' => 'Notify Admin on Payment Submission',
                'type' => 'checkbox',
                'default' => true
            ]
        ];
    }
    
    /**
     * Get default payment methods
     */
    protected function getDefaultMethods(): array
    {
        return [
            [
                'id' => 'zelle',
                'name' => 'Zelle',
                'type' => 'zelle',
                'icon' => 'fas fa-mobile-alt',
                'instructions' => "To pay via Zelle:\n\n1. Open your bank's mobile app or online banking\n2. Navigate to \"Send Money with Zelle\"\n3. Send payment to: {{ZELLE_EMAIL}}\n4. Use Invoice #{{INVOICE_ID}} as the memo\n5. Payment will be credited within 24 hours",
                'fields' => [
                    'zelle_email' => ['label' => 'Zelle Email/Phone', 'value' => ''],
                    'zelle_name' => ['label' => 'Registered Name', 'value' => '']
                ],
                'active' => true,
                'sort_order' => 1
            ],
            [
                'id' => 'wire',
                'name' => 'Wire Transfer',
                'type' => 'wire',
                'icon' => 'fas fa-university',
                'instructions' => "Wire Transfer Details:\n\nBank Name: {{BANK_NAME}}\nAccount Name: {{ACCOUNT_NAME}}\nAccount Number: {{ACCOUNT_NUMBER}}\nRouting Number: {{ROUTING_NUMBER}}\nSWIFT Code: {{SWIFT_CODE}}\n\nReference: Invoice #{{INVOICE_ID}}\n\nPlease allow 3-5 business days for processing.",
                'fields' => [
                    'bank_name' => ['label' => 'Bank Name', 'value' => ''],
                    'account_name' => ['label' => 'Account Name', 'value' => ''],
                    'account_number' => ['label' => 'Account Number', 'value' => ''],
                    'routing_number' => ['label' => 'Routing Number', 'value' => ''],
                    'swift_code' => ['label' => 'SWIFT Code', 'value' => '']
                ],
                'active' => false,
                'sort_order' => 2
            ],
            [
                'id' => 'check',
                'name' => 'Check',
                'type' => 'check',
                'icon' => 'fas fa-money-check',
                'instructions' => "Please mail your check to:\n\n{{COMPANY_NAME}}\n{{MAILING_ADDRESS}}\n\nMake check payable to: {{PAYABLE_TO}}\nMemo: Invoice #{{INVOICE_ID}}\n\nPlease allow 7-10 business days for processing.",
                'fields' => [
                    'payable_to' => ['label' => 'Make Check Payable To', 'value' => ''],
                    'mailing_address' => ['label' => 'Mailing Address', 'value' => '', 'type' => 'textarea']
                ],
                'active' => false,
                'sort_order' => 3
            ],
            [
                'id' => 'venmo',
                'name' => 'Venmo',
                'type' => 'venmo',
                'icon' => 'fab fa-vimeo-v',
                'instructions' => "To pay via Venmo:\n\n1. Open the Venmo app\n2. Send payment to: @{{VENMO_USERNAME}}\n3. Amount: ${{AMOUNT}}\n4. Note: Invoice #{{INVOICE_ID}}\n\nPayment will be credited within 24 hours.",
                'fields' => [
                    'venmo_username' => ['label' => 'Venmo Username', 'value' => ''],
                    'venmo_qr' => ['label' => 'QR Code Image URL', 'value' => '']
                ],
                'active' => false,
                'sort_order' => 4
            ],
            [
                'id' => 'cashapp',
                'name' => 'Cash App',
                'type' => 'cashapp',
                'icon' => 'fas fa-dollar-sign',
                'instructions' => "To pay via Cash App:\n\n1. Open Cash App\n2. Send payment to: ${{CASHTAG}}\n3. Amount: ${{AMOUNT}}\n4. Note: Invoice #{{INVOICE_ID}}\n\nPayment will be credited within 24 hours.",
                'fields' => [
                    'cashtag' => ['label' => 'Cash Tag ($)', 'value' => ''],
                    'cashapp_qr' => ['label' => 'QR Code Image URL', 'value' => '']
                ],
                'active' => false,
                'sort_order' => 5
            ],
            [
                'id' => 'crypto',
                'name' => 'Cryptocurrency',
                'type' => 'crypto',
                'icon' => 'fab fa-bitcoin',
                'instructions' => "Pay with Cryptocurrency:\n\nBitcoin (BTC): {{BTC_ADDRESS}}\nEthereum (ETH): {{ETH_ADDRESS}}\n\nAmount: ${{AMOUNT}} USD equivalent\nReference: {{INVOICE_ID}}\n\nPlease send the exact amount and email your transaction ID to {{SUPPORT_EMAIL}}",
                'fields' => [
                    'btc_address' => ['label' => 'Bitcoin Address', 'value' => ''],
                    'eth_address' => ['label' => 'Ethereum Address', 'value' => ''],
                    'support_email' => ['label' => 'Support Email', 'value' => '']
                ],
                'active' => false,
                'sort_order' => 6
            ]
        ];
    }
    
    /**
     * Get all available methods
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
    
    /**
     * Get active methods for checkout
     */
    public function getActiveMethods(): array
    {
        return array_filter($this->methods, fn($m) => $m['active'] ?? false);
    }
    
    /**
     * Process payment selection
     */
    public function processPayment(array $invoice, string $methodId): array
    {
        $method = null;
        foreach ($this->methods as $m) {
            if (($m['id'] ?? $m['name']) === $methodId) {
                $method = $m;
                break;
            }
        }
        
        if (!$method) {
            return [
                'success' => false,
                'error' => 'Invalid payment method selected.'
            ];
        }
        
        // Update invoice status
        $this->db->update('invoices', [
            'status' => $this->config['pending_status'] ?? 'payment_pending',
            'payment_method' => 'manual:' . $methodId
        ], 'id = ?', [$invoice['id']]);
        
        // Log the payment attempt
        $this->db->insert('payment_log', [
            'invoice_id' => $invoice['id'],
            'gateway' => 'manual',
            'method' => $methodId,
            'status' => 'pending',
            'data' => json_encode(['method' => $method['name']]),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Get formatted instructions
        $instructions = $this->formatInstructions($method, $invoice);
        
        // Send confirmation email if enabled
        if ($this->config['confirmation_email'] ?? true) {
            $this->sendInstructionsEmail($invoice, $method, $instructions);
        }
        
        // Notify admin if enabled
        if ($this->config['admin_notification'] ?? true) {
            $this->notifyAdmin($invoice, $method);
        }
        
        return [
            'success' => true,
            'status' => 'pending',
            'instructions' => $instructions,
            'method' => $method
        ];
    }
    
    /**
     * Format payment instructions with variables
     */
    public function formatInstructions(array $method, array $invoice): string
    {
        $instructions = $method['instructions'] ?? '';
        $fields = $method['fields'] ?? [];
        
        // Replace field placeholders
        foreach ($fields as $key => $field) {
            $value = $field['value'] ?? '';
            $placeholder = '{{' . strtoupper($key) . '}}';
            $instructions = str_replace($placeholder, $value, $instructions);
        }
        
        // Replace invoice placeholders
        $replacements = [
            '{{INVOICE_ID}}' => $invoice['id'],
            '{{INVOICE_NUMBER}}' => $invoice['invoice_number'] ?? $invoice['id'],
            '{{AMOUNT}}' => number_format($invoice['total'], 2),
            '{{DUE_DATE}}' => $invoice['due_date'] ?? 'N/A',
            '{{COMPANY_NAME}}' => COMPANY_NAME ?? 'Company',
            '{{CLIENT_NAME}}' => ($invoice['client_firstname'] ?? '') . ' ' . ($invoice['client_lastname'] ?? ''),
            '{{CLIENT_EMAIL}}' => $invoice['client_email'] ?? ''
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $instructions = str_replace($placeholder, $value, $instructions);
        }
        
        return $instructions;
    }
    
    /**
     * Send payment instructions email
     */
    protected function sendInstructionsEmail(array $invoice, array $method, string $instructions): void
    {
        if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
            return;
        }
        
        $client = $this->db->fetch(
            "SELECT * FROM clients WHERE id = ?",
            [$invoice['client_id']]
        );
        
        if (!$client) return;
        
        $subject = 'Payment Instructions for Invoice #' . $invoice['id'];
        $body = "Dear " . htmlspecialchars($client['firstname']) . ",\n\n";
        $body .= "Thank you for choosing to pay via " . $method['name'] . ".\n\n";
        $body .= "Invoice #: " . $invoice['id'] . "\n";
        $body .= "Amount Due: $" . number_format($invoice['total'], 2) . "\n\n";
        $body .= "Payment Instructions:\n";
        $body .= "─────────────────────\n";
        $body .= $instructions . "\n\n";
        $body .= "─────────────────────\n\n";
        $body .= "Your invoice will be marked as paid once we receive and verify your payment.\n\n";
        $body .= "If you have any questions, please contact our support team.\n\n";
        $body .= "Thank you,\n" . COMPANY_NAME;
        
        // Use mailer if available
        if (class_exists('\OpenWHM\Core\Mailer')) {
            $mailer = new \OpenWHM\Core\Mailer();
            $mailer->send($client['email'], $subject, nl2br($body));
        }
    }
    
    /**
     * Notify admin of pending payment
     */
    protected function notifyAdmin(array $invoice, array $method): void
    {
        if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
            return;
        }
        
        if (!defined('ADMIN_EMAIL')) return;
        
        $subject = 'Manual Payment Pending - Invoice #' . $invoice['id'];
        $body = "A client has selected manual payment.\n\n";
        $body .= "Invoice #: " . $invoice['id'] . "\n";
        $body .= "Amount: $" . number_format($invoice['total'], 2) . "\n";
        $body .= "Payment Method: " . $method['name'] . "\n";
        $body .= "Client ID: " . $invoice['client_id'] . "\n\n";
        $body .= "Please verify payment and update the invoice status accordingly.\n";
        $body .= "Admin Panel: " . ADMIN_URL . "/invoices/view/" . $invoice['id'];
        
        if (class_exists('\OpenWHM\Core\Mailer')) {
            $mailer = new \OpenWHM\Core\Mailer();
            $mailer->send(ADMIN_EMAIL, $subject, nl2br($body));
        }
    }
    
    /**
     * Mark payment as received (admin action)
     */
    public function markPaymentReceived(int $invoiceId, string $transactionId = '', string $notes = ''): bool
    {
        $invoice = $this->db->fetch(
            "SELECT * FROM invoices WHERE id = ?",
            [$invoiceId]
        );
        
        if (!$invoice) {
            return false;
        }
        
        // Update invoice
        $this->db->update('invoices', [
            'status' => 'paid',
            'date_paid' => date('Y-m-d H:i:s'),
            'transaction_id' => $transactionId
        ], 'id = ?', [$invoiceId]);
        
        // Update payment log
        $this->db->update('payment_log', [
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'data' => json_encode(['notes' => $notes])
        ], 'invoice_id = ? AND gateway = ?', [$invoiceId, 'manual']);
        
        // Log the action
        $this->db->insert('admin_log', [
            'admin_id' => $_SESSION['admin']['id'] ?? 0,
            'action' => 'payment_confirmed',
            'description' => "Manual payment confirmed for Invoice #{$invoiceId}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Activate services if needed
        $this->activateServices($invoiceId);
        
        // Send confirmation to client
        $this->sendPaymentConfirmation($invoice);
        
        return true;
    }
    
    /**
     * Activate services linked to invoice
     */
    protected function activateServices(int $invoiceId): void
    {
        $items = $this->db->fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ?",
            [$invoiceId]
        );
        
        foreach ($items as $item) {
            if ($item['type'] === 'service' && $item['related_id']) {
                $this->db->update('services', [
                    'status' => 'active'
                ], 'id = ? AND status = ?', [$item['related_id'], 'pending']);
            }
        }
    }
    
    /**
     * Send payment confirmation to client
     */
    protected function sendPaymentConfirmation(array $invoice): void
    {
        if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
            return;
        }
        
        $client = $this->db->fetch(
            "SELECT * FROM clients WHERE id = ?",
            [$invoice['client_id']]
        );
        
        if (!$client) return;
        
        $subject = 'Payment Received - Invoice #' . $invoice['id'];
        $body = "Dear " . htmlspecialchars($client['firstname']) . ",\n\n";
        $body .= "We have received and processed your payment.\n\n";
        $body .= "Invoice #: " . $invoice['id'] . "\n";
        $body .= "Amount: $" . number_format($invoice['total'], 2) . "\n";
        $body .= "Date: " . date('F j, Y') . "\n\n";
        $body .= "Thank you for your business!\n\n";
        $body .= COMPANY_NAME;
        
        if (class_exists('\OpenWHM\Core\Mailer')) {
            $mailer = new \OpenWHM\Core\Mailer();
            $mailer->send($client['email'], $subject, nl2br($body));
        }
    }
    
    /**
     * Render payment selection for checkout
     */
    public function renderPaymentOptions(array $invoice): string
    {
        $methods = $this->getActiveMethods();
        
        if (empty($methods)) {
            return '';
        }
        
        $html = '<div class="manual-payment-options">';
        $html .= '<h5 class="mb-3">Pay Offline</h5>';
        
        foreach ($methods as $method) {
            $id = $method['id'] ?? sanitize($method['name']);
            $icon = $method['icon'] ?? 'fas fa-money-bill';
            $name = htmlspecialchars($method['name']);
            
            $html .= <<<HTML
<div class="form-check payment-option mb-2">
    <input class="form-check-input" type="radio" name="manual_method" id="method_{$id}" value="{$id}">
    <label class="form-check-label" for="method_{$id}">
        <i class="{$icon} me-2"></i> {$name}
    </label>
</div>
HTML;
        }
        
        $html .= '</div>';
        return $html;
    }
}

/**
 * Helper function to sanitize string for IDs
 */
function sanitize(string $str): string
{
    return preg_replace('/[^a-z0-9_]/', '_', strtolower($str));
}
