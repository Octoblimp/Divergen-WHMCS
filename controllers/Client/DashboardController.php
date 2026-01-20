<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Client;
use OpenWHM\Models\Service;
use OpenWHM\Models\Invoice;
use OpenWHM\Models\Ticket;

/**
 * Client Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show client dashboard
     */
    public function index()
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        $clientModel = new Client();
        
        // Get client info
        $client = $clientModel->find($clientId);
        
        // Get services
        $services = $clientModel->getServices($clientId);
        
        // Get domains
        $domains = $clientModel->getDomains($clientId);
        
        // Get recent invoices
        $invoices = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('invoices')} 
             WHERE client_id = ? 
             ORDER BY date DESC 
             LIMIT 5",
            [$clientId]
        );
        
        // Get open tickets
        $tickets = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('tickets')} 
             WHERE client_id = ? AND status != 'closed' 
             ORDER BY last_reply DESC 
             LIMIT 5",
            [$clientId]
        );
        
        // Calculate stats
        $stats = [
            'active_services' => count(array_filter($services, fn($s) => $s['status'] === 'active')),
            'active_domains' => count(array_filter($domains, fn($d) => $d['status'] === 'active')),
            'unpaid_invoices' => count(array_filter($invoices, fn($i) => $i['status'] === 'unpaid')),
            'open_tickets' => count($tickets),
            'credit' => $client['credit'] ?? 0
        ];
        
        $this->render('client.dashboard.index', [
            'client' => $client,
            'services' => $services,
            'domains' => $domains,
            'invoices' => $invoices,
            'tickets' => $tickets,
            'stats' => $stats
        ]);
    }
}
