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
        
        // Calculate financial metrics
        $revenue = $this->db->fetch(
            "SELECT SUM(total) as total_revenue FROM {$this->db->table('invoices')} WHERE status = 'paid'"
        );
        $stats['revenue'] = $revenue['total_revenue'] ?? 0;
        
        $pending_revenue = $this->db->fetch(
            "SELECT SUM(total) as total FROM {$this->db->table('invoices')} WHERE status IN ('unpaid', 'partially_paid')"
        );
        $stats['pending_revenue'] = $pending_revenue['total'] ?? 0;
        
        // Get MRR (Monthly Recurring Revenue)
        $current_month = date('Y-m');
        $mrr = $this->db->fetch(
            "SELECT SUM(amount) as total FROM {$this->db->table('invoices')} 
             WHERE DATE_FORMAT(date, '%Y-%m') = ? AND status IN ('paid', 'unpaid')",
            [$current_month]
        );
        $stats['mrr'] = $mrr['total'] ?? 0;
        
        // Get recent orders
        $recentOrders = $orderModel->where('1', [], 'created_at DESC', 10);
        foreach ($recentOrders as &$order) {
            $order['client'] = $clientModel->find($order['client_id']);
        }
        
        // Get recent tickets
        $recentTickets = $ticketModel->where("status != 'closed'", [], 'last_reply DESC', 10);
        
        // Get overdue invoices
        $overdueInvoices = $invoiceModel->getOverdue();
        
        // Get pending orders
        $pendingOrders = $orderModel->where("status = 'pending'", [], 'created_at DESC', 5);
        
        // Get recently registered clients
        $newClients = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('clients')} ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get support overview
        $supportStats = [
            'open_tickets' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM {$this->db->table('tickets')} WHERE status != 'closed'"
            )['count'] ?? 0,
            'avg_response_time' => $this->getAverageResponseTime(),
            'client_satisfaction' => $this->getClientSatisfaction()
        ];
        
        $this->render('admin.dashboard.index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentTickets' => $recentTickets,
            'overdueInvoices' => $overdueInvoices,
            'pendingOrders' => $pendingOrders,
            'newClients' => $newClients,
            'supportStats' => $supportStats,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Get average ticket response time in hours
     */
    private function getAverageResponseTime()
    {
        $result = $this->db->fetch(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time 
             FROM {$this->db->table('tickets')} 
             WHERE updated_at IS NOT NULL AND status = 'answered'"
        );
        
        return round($result['avg_time'] ?? 0, 2);
    }
    
    /**
     * Get client satisfaction score (based on ratings)
     */
    private function getClientSatisfaction()
    {
        $result = $this->db->fetch(
            "SELECT AVG(rating) as avg_rating FROM {$this->db->table('ticket_ratings')} WHERE rating > 0"
        );
        
        return round($result['avg_rating'] ?? 0, 1);
    }
}
