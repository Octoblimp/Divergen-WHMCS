<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Support Ticket Model
 */
class Ticket extends Model
{
    protected $table = 'tickets';
    
    protected $fillable = [
        'ticket_number', 'department_id', 'client_id', 'name', 'email', 'cc',
        'subject', 'message', 'status', 'priority', 'admin_id', 'service_id',
        'domain_id', 'urgent', 'flagged', 'merged_ticket_id', 'last_reply', 'last_reply_by'
    ];
    
    /**
     * Create new ticket
     */
    public function create($data)
    {
        // Generate ticket number
        if (empty($data['ticket_number'])) {
            $data['ticket_number'] = $this->generateTicketNumber();
        }
        
        $data['last_reply'] = date('Y-m-d H:i:s');
        $data['last_reply_by'] = 'client';
        
        $ticketId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('TicketOpen', [
            'ticket_id' => $ticketId,
            'data' => $data
        ]);
        
        Logger::activity('ticket_opened', "Ticket #{$data['ticket_number']} opened: {$data['subject']}", $data['client_id']);
        
        return $ticketId;
    }
    
    /**
     * Generate unique ticket number
     */
    public function generateTicketNumber()
    {
        return 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
    }
    
    /**
     * Get ticket with department and client
     */
    public function getWithDetails($ticketId)
    {
        return $this->db->fetch(
            "SELECT t.*, d.name as department_name, d.email as department_email,
                    c.firstname, c.lastname, c.email as client_email,
                    a.name as assigned_admin
             FROM {$this->getTable()} t
             LEFT JOIN {$this->db->table('support_departments')} d ON t.department_id = d.id
             LEFT JOIN {$this->db->table('clients')} c ON t.client_id = c.id
             LEFT JOIN {$this->db->table('admins')} a ON t.admin_id = a.id
             WHERE t.id = ?",
            [$ticketId]
        );
    }
    
    /**
     * Add reply to ticket
     */
    public function addReply($ticketId, $data)
    {
        $data['ticket_id'] = $ticketId;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $replyId = $this->db->insert('ticket_replies', $data);
        
        // Update ticket status and last reply
        $isAdmin = !empty($data['admin_id']);
        
        $this->update($ticketId, [
            'status' => $isAdmin ? 'answered' : 'customer_reply',
            'last_reply' => date('Y-m-d H:i:s'),
            'last_reply_by' => $isAdmin ? 'admin' : 'client'
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('TicketReply', [
            'ticket_id' => $ticketId,
            'reply_id' => $replyId,
            'is_admin' => $isAdmin
        ]);
        
        return $replyId;
    }
    
    /**
     * Get ticket replies
     */
    public function getReplies($ticketId)
    {
        return $this->db->fetchAll(
            "SELECT r.*, 
                    COALESCE(c.firstname, r.name) as author_firstname,
                    COALESCE(c.lastname, '') as author_lastname,
                    COALESCE(a.name, c.firstname) as author_name,
                    CASE WHEN r.admin_id IS NOT NULL THEN 'admin' ELSE 'client' END as author_type
             FROM {$this->db->table('ticket_replies')} r
             LEFT JOIN {$this->db->table('clients')} c ON r.client_id = c.id
             LEFT JOIN {$this->db->table('admins')} a ON r.admin_id = a.id
             WHERE r.ticket_id = ?
             ORDER BY r.created_at ASC",
            [$ticketId]
        );
    }
    
    /**
     * Close ticket
     */
    public function close($ticketId)
    {
        $ticket = $this->find($ticketId);
        
        if (!$ticket) {
            return false;
        }
        
        $this->update($ticketId, ['status' => 'closed']);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('TicketClose', [
            'ticket_id' => $ticketId,
            'ticket' => $ticket
        ]);
        
        Logger::activity('ticket_closed', "Ticket #{$ticket['ticket_number']} closed", $ticket['client_id']);
        
        return true;
    }
    
    /**
     * Reopen ticket
     */
    public function reopen($ticketId)
    {
        $this->update($ticketId, ['status' => 'open']);
        return true;
    }
    
    /**
     * Assign ticket to admin
     */
    public function assign($ticketId, $adminId)
    {
        $this->update($ticketId, ['admin_id' => $adminId]);
        return true;
    }
    
    /**
     * Get open tickets count
     */
    public function getOpenCount()
    {
        return $this->count("status NOT IN ('closed')");
    }
    
    /**
     * Get tickets by status
     */
    public function getByStatus($status, $limit = 50)
    {
        return $this->db->fetchAll(
            "SELECT t.*, d.name as department_name, c.firstname, c.lastname, c.email
             FROM {$this->getTable()} t
             LEFT JOIN {$this->db->table('support_departments')} d ON t.department_id = d.id
             LEFT JOIN {$this->db->table('clients')} c ON t.client_id = c.id
             WHERE t.status = ?
             ORDER BY t.last_reply DESC
             LIMIT ?",
            [$status, $limit]
        );
    }
    
    /**
     * Get all departments
     */
    public function getDepartments()
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('support_departments')} 
             WHERE hidden = 0 
             ORDER BY sort_order ASC"
        );
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->count(),
            'open' => $this->count("status = 'open'"),
            'answered' => $this->count("status = 'answered'"),
            'customer_reply' => $this->count("status = 'customer_reply'"),
            'on_hold' => $this->count("status = 'on_hold'"),
            'in_progress' => $this->count("status = 'in_progress'"),
            'closed' => $this->count("status = 'closed'"),
            'urgent' => $this->count("urgent = 1 AND status != 'closed'")
        ];
    }
}
