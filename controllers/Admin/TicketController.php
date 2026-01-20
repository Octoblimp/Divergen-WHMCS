<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Ticket;

/**
 * Admin Ticket Controller
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
     * List tickets
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
            $conditions = "status != 'closed'";
            $params = [];
        }
        
        $tickets = $this->ticketModel->paginate($page, 25, $conditions, $params, 'last_reply DESC');
        
        // Get details for each ticket
        foreach ($tickets['items'] as &$ticket) {
            $ticket = $this->ticketModel->getWithDetails($ticket['id']);
        }
        
        $departments = $this->ticketModel->getDepartments();
        
        $this->render('admin.tickets.index', [
            'tickets' => $tickets,
            'departments' => $departments,
            'status' => $status,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * View ticket
     */
    public function view($id)
    {
        $this->requireAdmin();
        
        $ticket = $this->ticketModel->getWithDetails($id);
        
        if (!$ticket) {
            $this->flash('error', 'Ticket not found');
            $this->redirect(ADMIN_URL . '/tickets');
        }
        
        $replies = $this->ticketModel->getReplies($id);
        
        $this->render('admin.tickets.view', [
            'ticket' => $ticket,
            'replies' => $replies,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Reply to ticket
     */
    public function reply($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $message = $this->input('message');
        
        if (empty($message)) {
            $this->flash('error', 'Please enter a message');
            $this->redirect(ADMIN_URL . '/tickets/' . $id);
        }
        
        $admin = $this->getAdmin();
        
        $this->ticketModel->addReply($id, [
            'admin_id' => $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'message' => $message
        ]);
        
        $this->flash('success', 'Reply added successfully');
        $this->redirect(ADMIN_URL . '/tickets/' . $id);
    }
    
    /**
     * Close ticket
     */
    public function close($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $this->ticketModel->close($id);
        
        $this->flash('success', 'Ticket closed');
        $this->redirect(ADMIN_URL . '/tickets/' . $id);
    }
}
