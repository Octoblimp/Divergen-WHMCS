<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Invoice Model
 */
class Invoice extends Model
{
    protected $table = 'invoices';
    
    protected $fillable = [
        'invoice_number', 'client_id', 'date', 'due_date', 'date_paid',
        'subtotal', 'tax_rate', 'tax', 'tax_rate2', 'tax2', 'credit', 'total',
        'payment_method', 'status', 'notes'
    ];
    
    /**
     * Create new invoice
     */
    public function create($data)
    {
        // Generate invoice number if not provided
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }
        
        // Set default dates
        if (empty($data['date'])) {
            $data['date'] = date('Y-m-d');
        }
        
        if (empty($data['due_date'])) {
            $data['due_date'] = date('Y-m-d', strtotime('+' . INVOICE_DUE_DAYS . ' days'));
        }
        
        $invoiceId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('InvoiceCreated', ['invoice_id' => $invoiceId]);
        
        Logger::activity('invoice_created', "Invoice #{$data['invoice_number']} created", $data['client_id']);
        
        return $invoiceId;
    }
    
    /**
     * Generate unique invoice number
     */
    public function generateInvoiceNumber()
    {
        $lastInvoice = $this->db->fetch(
            "SELECT invoice_number FROM {$this->getTable()} ORDER BY id DESC LIMIT 1"
        );
        
        if ($lastInvoice) {
            $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastInvoice['invoice_number']);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = INVOICE_START_NUMBER;
        }
        
        return INVOICE_PREFIX . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Add item to invoice
     */
    public function addItem($invoiceId, $data)
    {
        $data['invoice_id'] = $invoiceId;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('invoice_items', $data);
        
        // Recalculate totals
        $this->recalculate($invoiceId);
    }
    
    /**
     * Remove item from invoice
     */
    public function removeItem($itemId)
    {
        $item = $this->db->fetch(
            "SELECT invoice_id FROM {$this->db->table('invoice_items')} WHERE id = ?",
            [$itemId]
        );
        
        if ($item) {
            $this->db->delete('invoice_items', 'id = ?', [$itemId]);
            $this->recalculate($item['invoice_id']);
        }
    }
    
    /**
     * Get invoice items
     */
    public function getItems($invoiceId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('invoice_items')} WHERE invoice_id = ?",
            [$invoiceId]
        );
    }
    
    /**
     * Recalculate invoice totals
     */
    public function recalculate($invoiceId)
    {
        $invoice = $this->find($invoiceId);
        $items = $this->getItems($invoiceId);
        
        $subtotal = 0;
        $taxableAmount = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['amount'];
            if ($item['taxed']) {
                $taxableAmount += $item['amount'];
            }
        }
        
        $tax = 0;
        $tax2 = 0;
        
        if (ENABLE_TAX && $invoice['tax_rate'] > 0) {
            $tax = $taxableAmount * ($invoice['tax_rate'] / 100);
        }
        
        if (ENABLE_TAX && $invoice['tax_rate2'] > 0) {
            $tax2 = $taxableAmount * ($invoice['tax_rate2'] / 100);
        }
        
        $total = $subtotal + $tax + $tax2 - $invoice['credit'];
        
        $this->update($invoiceId, [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax2' => $tax2,
            'total' => max(0, $total)
        ]);
    }
    
    /**
     * Mark invoice as paid
     */
    public function markPaid($invoiceId, $transactionId = null, $gateway = null)
    {
        $invoice = $this->find($invoiceId);
        
        if (!$invoice) {
            return false;
        }
        
        $this->update($invoiceId, [
            'status' => 'paid',
            'date_paid' => date('Y-m-d H:i:s'),
            'payment_method' => $gateway ?? $invoice['payment_method']
        ]);
        
        // Record transaction
        if ($gateway) {
            $this->db->insert('transactions', [
                'client_id' => $invoice['client_id'],
                'invoice_id' => $invoiceId,
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'description' => "Payment for Invoice #{$invoice['invoice_number']}",
                'amount_in' => $invoice['total'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('InvoicePaid', [
            'invoice_id' => $invoiceId,
            'invoice' => $invoice
        ]);
        
        Logger::activity('invoice_paid', "Invoice #{$invoice['invoice_number']} marked as paid", $invoice['client_id']);
        
        // Activate related services/domains
        $this->activateRelatedItems($invoiceId);
        
        return true;
    }
    
    /**
     * Activate items related to paid invoice
     */
    private function activateRelatedItems($invoiceId)
    {
        $items = $this->getItems($invoiceId);
        
        foreach ($items as $item) {
            if ($item['type'] === 'hosting' && $item['related_id']) {
                // Activate service
                $service = new Service();
                $service->activate($item['related_id']);
                
            } elseif ($item['type'] === 'domain' && $item['related_id']) {
                // Activate domain
                $domain = new Domain();
                $domain->activate($item['related_id']);
            }
        }
    }
    
    /**
     * Apply credit to invoice
     */
    public function applyCredit($invoiceId, $amount)
    {
        $invoice = $this->find($invoiceId);
        
        if (!$invoice || $invoice['status'] === 'paid') {
            return false;
        }
        
        // Check client has enough credit
        $client = new Client();
        $clientData = $client->find($invoice['client_id']);
        
        if ($clientData['credit'] < $amount) {
            $amount = $clientData['credit'];
        }
        
        if ($amount <= 0) {
            return false;
        }
        
        // Deduct credit from client
        $client->removeCredit($invoice['client_id'], $amount, "Credit applied to Invoice #{$invoice['invoice_number']}");
        
        // Apply to invoice
        $newCredit = $invoice['credit'] + $amount;
        $newTotal = $invoice['subtotal'] + $invoice['tax'] + $invoice['tax2'] - $newCredit;
        
        $this->update($invoiceId, [
            'credit' => $newCredit,
            'total' => max(0, $newTotal)
        ]);
        
        // Check if fully paid
        if ($newTotal <= 0) {
            $this->markPaid($invoiceId, null, 'Credit');
        }
        
        return true;
    }
    
    /**
     * Get overdue invoices
     */
    public function getOverdue()
    {
        return $this->db->fetchAll(
            "SELECT i.*, c.firstname, c.lastname, c.email
             FROM {$this->getTable()} i
             LEFT JOIN {$this->db->table('clients')} c ON i.client_id = c.id
             WHERE i.status = 'unpaid' AND i.due_date < CURDATE()
             ORDER BY i.due_date ASC"
        );
    }
    
    /**
     * Get invoice with client details
     */
    public function getWithClient($invoiceId)
    {
        return $this->db->fetch(
            "SELECT i.*, c.firstname, c.lastname, c.email, c.company, 
                    c.address1, c.address2, c.city, c.state, c.postcode, c.country
             FROM {$this->getTable()} i
             LEFT JOIN {$this->db->table('clients')} c ON i.client_id = c.id
             WHERE i.id = ?",
            [$invoiceId]
        );
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        $unpaidTotal = $this->db->fetchValue(
            "SELECT COALESCE(SUM(total), 0) FROM {$this->getTable()} WHERE status = 'unpaid'"
        );
        
        $paidThisMonth = $this->db->fetchValue(
            "SELECT COALESCE(SUM(total), 0) FROM {$this->getTable()} 
             WHERE status = 'paid' AND date_paid >= ?",
            [date('Y-m-01')]
        );
        
        return [
            'total' => $this->count(),
            'unpaid' => $this->count("status = 'unpaid'"),
            'paid' => $this->count("status = 'paid'"),
            'overdue' => $this->count("status = 'unpaid' AND due_date < CURDATE()"),
            'unpaid_total' => $unpaidTotal,
            'paid_this_month' => $paidThisMonth
        ];
    }
}
