<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Service;

/**
 * Client Service Controller
 */
class ServiceController extends Controller
{
    private $serviceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new Service();
    }
    
    /**
     * List client's services
     */
    public function index()
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $services = $this->db->fetchAll(
            "SELECT s.*, p.name as product_name, p.type as product_type
             FROM {$this->db->table('services')} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.client_id = ?
             ORDER BY s.status ASC, s.created_at DESC",
            [$clientId]
        );
        
        $this->render('client.services.index', [
            'services' => $services
        ]);
    }
    
    /**
     * View service details
     */
    public function view($id)
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $service = $this->serviceModel->getWithDetails($id);
        
        if (!$service || $service['client_id'] != $clientId) {
            $this->flash('error', 'Service not found');
            $this->redirect(CLIENT_URL . '/services');
        }
        
        // Get server info if available
        $server = null;
        if ($service['server_id']) {
            $server = $this->db->fetch(
                "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
                [$service['server_id']]
            );
        }
        
        $this->render('client.services.view', [
            'service' => $service,
            'server' => $server
        ]);
    }
    
    /**
     * Request cancellation
     */
    public function cancel($id)
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $service = $this->serviceModel->find($id);
        
        if (!$service || $service['client_id'] != $clientId) {
            $this->flash('error', 'Service not found');
            $this->redirect(CLIENT_URL . '/services');
        }
        
        $reason = $this->input('reason', '');
        $type = $this->input('type', 'end_of_billing'); // immediate or end_of_billing
        
        // Create cancellation request
        $this->db->insert('cancellation_requests', [
            'service_id' => $id,
            'client_id' => $clientId,
            'type' => $type,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->hooks->execute('ServiceCancellationRequest', [
            'service_id' => $id,
            'client_id' => $clientId,
            'type' => $type
        ]);
        
        $this->flash('success', 'Cancellation request submitted');
        $this->redirect(CLIENT_URL . '/services/' . $id);
    }
}
