<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Invoice;

/**
 * Admin Invoice Controller
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
     * List invoices
     */
    public function index()
    {
        $this->requireAdmin();
        
        $page = (int) ($this->query('page') ?? 1);
        $status = $this->query('status');
        
        if ($status) {
            $conditions = "status = ?";
            $params = [$status];
        } else {
            $conditions = '1';
            $params = [];
        }
        
        $invoices = $this->invoiceModel->paginate($page, 25, $conditions, $params, 'date DESC');
        
        // Get client info for each invoice
        foreach ($invoices['items'] as &$invoice) {
            $invoice = $this->invoiceModel->getWithClient($invoice['id']);
        }
        
        $this->render('admin.invoices.index', [
            'invoices' => $invoices,
            'status' => $status,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * View invoice
     */
    public function view($id)
    {
        $this->requireAdmin();
        
        $invoice = $this->invoiceModel->getWithClient($id);
        
        if (!$invoice) {
            $this->flash('error', 'Invoice not found');
            $this->redirect(ADMIN_URL . '/invoices');
        }
        
        $items = $this->invoiceModel->getItems($id);
        
        $this->render('admin.invoices.view', [
            'invoice' => $invoice,
            'items' => $items,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Mark invoice as paid
     */
    public function markPaid($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $gateway = $this->input('gateway', 'Manual Payment');
        $transactionId = $this->input('transaction_id');
        
        $result = $this->invoiceModel->markPaid($id, $transactionId, $gateway);
        
        if ($result) {
            $this->flash('success', 'Invoice marked as paid');
        } else {
            $this->flash('error', 'Failed to update invoice');
        }
        
        $this->redirect(ADMIN_URL . '/invoices/' . $id);
    }
}
