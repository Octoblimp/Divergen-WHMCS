<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;

/**
 * Admin Analytics & Reporting Controller
 */
class ReportsController extends Controller
{
    /**
     * Show main reports dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        
        // Get date range from query
        $startDate = $this->query('start_date') ? date('Y-m-d', strtotime($this->query('start_date'))) : date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->query('end_date') ? date('Y-m-d', strtotime($this->query('end_date'))) : date('Y-m-d');
        
        // Revenue data
        $revenue = $this->getRevenueData($startDate, $endDate);
        $orderTrends = $this->getOrderTrends($startDate, $endDate);
        $clientGrowth = $this->getClientGrowth($startDate, $endDate);
        $serviceBreakdown = $this->getServiceBreakdown();
        $paymentMethods = $this->getPaymentMethods($startDate, $endDate);
        $topClients = $this->getTopClients($startDate, $endDate);
        $ticketMetrics = $this->getTicketMetrics($startDate, $endDate);
        
        $this->render('admin.reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'revenue' => $revenue,
            'orderTrends' => $orderTrends,
            'clientGrowth' => $clientGrowth,
            'serviceBreakdown' => $serviceBreakdown,
            'paymentMethods' => $paymentMethods,
            'topClients' => $topClients,
            'ticketMetrics' => $ticketMetrics,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Get revenue data for date range
     */
    private function getRevenueData($startDate, $endDate)
    {
        $data = $this->db->fetchAll(
            "SELECT 
                DATE(date) as date,
                SUM(total) as total,
                COUNT(*) as count
             FROM {$this->db->table('invoices')}
             WHERE status = 'paid' AND date BETWEEN ? AND ?
             GROUP BY DATE(date)
             ORDER BY date ASC",
            [$startDate, $endDate]
        );
        
        $totalRevenue = array_sum(array_column($data, 'total'));
        $avgDaily = count($data) > 0 ? $totalRevenue / count($data) : 0;
        
        return [
            'total' => $totalRevenue,
            'average_daily' => $avgDaily,
            'daily_breakdown' => $data
        ];
    }
    
    /**
     * Get order trends
     */
    private function getOrderTrends($startDate, $endDate)
    {
        $data = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                SUM(total) as total,
                status
             FROM {$this->db->table('orders')}
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at), status
             ORDER BY date ASC",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        
        $totalOrders = count(array_unique(array_column($data, 'date')));
        
        return [
            'total_orders' => array_sum(array_column($data, 'count')),
            'total_value' => array_sum(array_column($data, 'total')),
            'daily_breakdown' => $data
        ];
    }
    
    /**
     * Get client growth
     */
    private function getClientGrowth($startDate, $endDate)
    {
        $data = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                status
             FROM {$this->db->table('clients')}
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at), status
             ORDER BY date ASC",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        
        return [
            'new_clients' => array_sum(array_column($data, 'count')),
            'daily_breakdown' => $data
        ];
    }
    
    /**
     * Get service breakdown by type
     */
    private function getServiceBreakdown()
    {
        return $this->db->fetchAll(
            "SELECT 
                s.package_id,
                p.name,
                COUNT(s.id) as count,
                SUM(p.price) as total_value,
                COUNT(CASE WHEN s.status = 'active' THEN 1 END) as active
             FROM {$this->db->table('services')} s
             LEFT JOIN {$this->db->table('products')} p ON s.package_id = p.id
             GROUP BY s.package_id, p.name
             ORDER BY count DESC"
        );
    }
    
    /**
     * Get payment methods breakdown
     */
    private function getPaymentMethods($startDate, $endDate)
    {
        return $this->db->fetchAll(
            "SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(total) as total
             FROM {$this->db->table('orders')}
             WHERE created_at BETWEEN ? AND ?
             GROUP BY payment_method
             ORDER BY total DESC",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
    }
    
    /**
     * Get top clients by revenue
     */
    private function getTopClients($startDate, $endDate, $limit = 10)
    {
        return $this->db->fetchAll(
            "SELECT 
                c.id,
                c.firstname,
                c.lastname,
                c.email,
                COUNT(DISTINCT o.id) as order_count,
                SUM(o.total) as total_spent,
                COUNT(DISTINCT s.id) as service_count
             FROM {$this->db->table('clients')} c
             LEFT JOIN {$this->db->table('orders')} o ON c.id = o.client_id AND o.created_at BETWEEN ? AND ?
             LEFT JOIN {$this->db->table('services')} s ON c.id = s.client_id
             GROUP BY c.id
             ORDER BY total_spent DESC
             LIMIT ?",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59', $limit]
        );
    }
    
    /**
     * Get ticket metrics
     */
    private function getTicketMetrics($startDate, $endDate)
    {
        $totalTickets = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->table('tickets')}
             WHERE created_at BETWEEN ? AND ?",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        
        $closedTickets = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->table('tickets')}
             WHERE status = 'closed' AND created_at BETWEEN ? AND ?",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        
        $avgResponseTime = $this->db->fetch(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as hours
             FROM {$this->db->table('tickets')}
             WHERE updated_at IS NOT NULL AND created_at BETWEEN ? AND ?",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        
        return [
            'total' => $totalTickets['count'] ?? 0,
            'closed' => $closedTickets['count'] ?? 0,
            'avg_response_hours' => round($avgResponseTime['hours'] ?? 0, 2),
            'closure_rate' => ($totalTickets['count'] ?? 0) > 0 ? round((($closedTickets['count'] ?? 0) / ($totalTickets['count'] ?? 0)) * 100, 1) : 0
        ];
    }
}
