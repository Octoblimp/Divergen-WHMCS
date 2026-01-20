<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Invoice;

/**
 * Client Invoice Controller
 */
class InvoiceController extends Controller
{
    private $invoiceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
    }
    
    /**
     * List client's invoices
     */
    public function index()
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $invoices = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('invoices')} 
             WHERE client_id = ?
             ORDER BY date DESC",
            [$clientId]
        );
        
        $this->render('client.invoices.index', [
            'invoices' => $invoices
        ]);
    }
    
    /**
     * View invoice
     */
    public function view($id)
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice || $invoice['client_id'] != $clientId) {
            $this->flash('error', 'Invoice not found');
            $this->redirect(CLIENT_URL . '/invoices');
        }
        
        $items = $this->invoiceModel->getItems($id);
        
        // Get client info
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
            [$clientId]
        );
        
        $this->render('client.invoices.view', [
            'invoice' => $invoice,
            'items' => $items,
            'client' => $client
        ]);
    }
    
    /**
     * Pay invoice
     */
    public function pay($id)
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice || $invoice['client_id'] != $clientId) {
            $this->flash('error', 'Invoice not found');
            $this->redirect(CLIENT_URL . '/invoices');
        }
        
        if ($invoice['status'] !== 'unpaid') {
            $this->flash('error', 'This invoice cannot be paid');
            $this->redirect(CLIENT_URL . '/invoices/' . $id);
        }
        
        // Get available payment gateways
        $gateways = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('payment_gateways')} WHERE active = 1 ORDER BY sort_order ASC"
        );
        
        // Get client for credit check
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
            [$clientId]
        );
        
        $this->render('client.invoices.pay', [
            'invoice' => $invoice,
            'gateways' => $gateways,
            'client' => $client
        ]);
    }
    
    /**
     * Process payment
     */
    public function processPayment($id)
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice || $invoice['client_id'] != $clientId) {
            $this->flash('error', 'Invoice not found');
            $this->redirect(CLIENT_URL . '/invoices');
        }
        
        if ($invoice['status'] !== 'unpaid') {
            $this->flash('error', 'This invoice cannot be paid');
            $this->redirect(CLIENT_URL . '/invoices/' . $id);
        }
        
        $gateway = $this->input('payment_method');
        
        // Get gateway settings
        $gatewayConfig = $this->db->fetch(
            "SELECT * FROM {$this->db->table('payment_gateways')} WHERE name = ? AND active = 1",
            [$gateway]
        );
        
        if (!$gatewayConfig) {
            $this->flash('error', 'Invalid payment method');
            $this->redirect(CLIENT_URL . '/invoices/' . $id . '/pay');
        }
        
        // Load gateway module
        $gatewayName = strtolower(str_replace(' ', '', $gateway));
        $gatewayFile = ROOT_PATH . '/modules/gateways/' . $gatewayName . '/' . $gatewayName . '.php';
        
        if (!file_exists($gatewayFile)) {
            $this->flash('error', 'Payment gateway not configured');
            $this->redirect(CLIENT_URL . '/invoices/' . $id . '/pay');
        }
        
        require_once ROOT_PATH . '/modules/gateways/GatewayModule.php';
        require_once $gatewayFile;
        
        $className = 'OpenWHM\\Modules\\Gateways\\' . ucfirst($gatewayName);
        
        if (!class_exists($className)) {
            $this->flash('error', 'Payment gateway class not found');
            $this->redirect(CLIENT_URL . '/invoices/' . $id . '/pay');
        }
        
        $gatewayModule = new $className();
        $gatewayModule->setSettings(json_decode($gatewayConfig['settings'] ?? '{}', true));
        
        // Process payment
        $result = $gatewayModule->processPayment($invoice, $invoice['total'], 'USD');
        
        if ($result['success']) {
            if (isset($result['redirect_url'])) {
                header('Location: ' . $result['redirect_url']);
                exit;
            }
            
            if (isset($result['type']) && $result['type'] === 'offline') {
                $this->flash('info', 'Please complete your payment using the bank details shown below.');
                $this->redirect(CLIENT_URL . '/invoices/' . $id);
            }
            
            $this->flash('success', 'Payment processed successfully');
            $this->redirect(CLIENT_URL . '/invoices/' . $id);
        } else {
            $this->flash('error', $result['error'] ?? 'Payment failed');
            $this->redirect(CLIENT_URL . '/invoices/' . $id . '/pay');
        }
    }
    
    /**
     * Apply credit to invoice
     */
    public function applyCredit($id)
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice || $invoice['client_id'] != $clientId) {
            $this->flash('error', 'Invoice not found');
            $this->redirect(CLIENT_URL . '/invoices');
        }
        
        // Get client credit
        $client = $this->db->fetch(
            "SELECT credit FROM {$this->db->table('clients')} WHERE id = ?",
            [$clientId]
        );
        
        if ($client['credit'] <= 0) {
            $this->flash('error', 'No credit available');
            $this->redirect(CLIENT_URL . '/invoices/' . $id . '/pay');
        }
        
        // Apply credit
        $this->invoiceModel->applyCredit($id, $client['credit']);
        
        $this->flash('success', 'Credit applied successfully');
        $this->redirect(CLIENT_URL . '/invoices/' . $id);
    }
}
