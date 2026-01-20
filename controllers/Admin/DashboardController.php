<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Client;
use OpenWHM\Models\Order;
use OpenWHM\Models\Invoice;
use OpenWHM\Models\Service;
use OpenWHM\Models\Ticket;

/**
 * Admin Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        
        // Get statistics
        $clientModel = new Client();
        $orderModel = new Order();
        $invoiceModel = new Invoice();
        $serviceModel = new Service();
        $ticketModel = new Ticket();
        
        $stats = [
            'clients' => $clientModel->getStats(),
            'orders' => $orderModel->getStats(),
            'invoices' => $invoiceModel->getStats(),
            'services' => $serviceModel->getStats(),
            'tickets' => $ticketModel->getStats()
        ];
        
        // Get recent orders
        $recentOrders = $orderModel->where('1', [], 'created_at DESC', 10);
        
        // Get recent tickets
        $recentTickets = $ticketModel->where("status != 'closed'", [], 'last_reply DESC', 10);
        
        // Get overdue invoices
        $overdueInvoices = $invoiceModel->getOverdue();
        
        // Get pending orders
        $pendingOrders = $orderModel->where("status = 'pending'", [], 'created_at DESC');
        
        $this->render('admin.dashboard.index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentTickets' => $recentTickets,
            'overdueInvoices' => $overdueInvoices,
            'pendingOrders' => $pendingOrders,
            'admin' => $this->getAdmin()
        ]);
    }
}
