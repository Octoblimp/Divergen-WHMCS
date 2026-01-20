<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Ticket;

/**
 * Client Ticket Controller
 */
class TicketController extends Controller
{
    private $ticketModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new Ticket();
    }
    
    /**
     * List client's tickets
     */
    public function index()
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $tickets = $this->db->fetchAll(
            "SELECT t.*, d.name as department_name
             FROM {$this->db->table('tickets')} t
             LEFT JOIN {$this->db->table('ticket_departments')} d ON t.department_id = d.id
             WHERE t.client_id = ?
             ORDER BY t.last_reply DESC",
            [$clientId]
        );
        
        $this->render('client.tickets.index', [
            'tickets' => $tickets
        ]);
    }
    
    /**
     * Show create ticket form
     */
    public function create()
    {
        $this->requireClient();
        
        $departments = $this->ticketModel->getDepartments();
        
        // Get client's services for related service dropdown
        $clientId = $this->session->getClientId();
        $services = $this->db->fetchAll(
            "SELECT s.id, s.domain, p.name as product_name
             FROM {$this->db->table('services')} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.client_id = ? AND s.status = 'active'",
            [$clientId]
        );
        
        $this->render('client.tickets.create', [
            'departments' => $departments,
            'services' => $services
        ]);
    }
    
    /**
     * Store new ticket
     */
    public function store()
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
            [$clientId]
        );
        
        $data = [
            'client_id' => $clientId,
            'department_id' => $this->input('department_id'),
            'subject' => $this->input('subject'),
            'message' => $this->input('message'),
            'priority' => $this->input('priority', 'medium'),
            'name' => $client['firstname'] . ' ' . $client['lastname'],
            'email' => $client['email'],
            'service_id' => $this->input('service_id') ?: null
        ];
        
        // Validate
        if (empty($data['subject']) || empty($data['message'])) {
            $this->flash('error', 'Please fill in all required fields');
            $this->redirect(CLIENT_URL . '/tickets/create');
        }
        
        $ticketId = $this->ticketModel->create($data);
        
        $this->flash('success', 'Ticket created successfully');
        $this->redirect(CLIENT_URL . '/tickets/' . $ticketId);
    }
    
    /**
     * View ticket
     */
    public function view($id)
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        
        $ticket = $this->ticketModel->getWithDetails($id);
        
        if (!$ticket || $ticket['client_id'] != $clientId) {
            $this->flash('error', 'Ticket not found');
            $this->redirect(CLIENT_URL . '/tickets');
        }
        
        $replies = $this->ticketModel->getReplies($id);
        
        $this->render('client.tickets.view', [
            'ticket' => $ticket,
            'replies' => $replies
        ]);
    }
    
    /**
     * Reply to ticket
     */
    public function reply($id)
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $ticket = $this->ticketModel->find($id);
        
        if (!$ticket || $ticket['client_id'] != $clientId) {
            $this->flash('error', 'Ticket not found');
            $this->redirect(CLIENT_URL . '/tickets');
        }
        
        $message = $this->input('message');
        
        if (empty($message)) {
            $this->flash('error', 'Please enter a message');
            $this->redirect(CLIENT_URL . '/tickets/' . $id);
        }
        
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE id = ?",
            [$clientId]
        );
        
        $this->ticketModel->addReply($id, [
            'client_id' => $clientId,
            'name' => $client['firstname'] . ' ' . $client['lastname'],
            'email' => $client['email'],
            'message' => $message
        ]);
        
        $this->flash('success', 'Reply added successfully');
        $this->redirect(CLIENT_URL . '/tickets/' . $id);
    }
    
    /**
     * Close ticket
     */
    public function close($id)
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $ticket = $this->ticketModel->find($id);
        
        if (!$ticket || $ticket['client_id'] != $clientId) {
            $this->flash('error', 'Ticket not found');
            $this->redirect(CLIENT_URL . '/tickets');
        }
        
        $this->ticketModel->close($id);
        
        $this->flash('success', 'Ticket closed');
        $this->redirect(CLIENT_URL . '/tickets/' . $id);
    }
}
