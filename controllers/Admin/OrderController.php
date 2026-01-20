<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Order;
use OpenWHM\Models\Invoice;

/**
 * Admin Order Controller
 */
class OrderController extends Controller
{
    private $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
    }
    
    /**
     * List orders
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
        
        $orders = $this->orderModel->paginate($page, 25, $conditions, $params, 'created_at DESC');
        
        // Get client info for each order
        foreach ($orders['items'] as &$order) {
            $order = $this->orderModel->getWithDetails($order['id']);
        }
        
        $this->render('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * View order
     */
    public function view($id)
    {
        $this->requireAdmin();
        
        $order = $this->orderModel->getWithDetails($id);
        
        if (!$order) {
            $this->flash('error', 'Order not found');
            $this->redirect(ADMIN_URL . '/orders');
        }
        
        $services = $this->orderModel->getServices($id);
        $domains = $this->orderModel->getDomains($id);
        
        $this->render('admin.orders.view', [
            'order' => $order,
            'services' => $services,
            'domains' => $domains,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Accept order
     */
    public function accept($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $result = $this->orderModel->accept($id);
        
        if ($result) {
            $this->flash('success', 'Order accepted and services activated');
        } else {
            $this->flash('error', 'Failed to accept order');
        }
        
        $this->redirect(ADMIN_URL . '/orders/' . $id);
    }
    
    /**
     * Cancel order
     */
    public function cancel($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $reason = $this->input('reason', '');
        
        $result = $this->orderModel->cancel($id, $reason);
        
        if ($result) {
            $this->flash('success', 'Order cancelled');
        } else {
            $this->flash('error', 'Failed to cancel order');
        }
        
        $this->redirect(ADMIN_URL . '/orders/' . $id);
    }
}
