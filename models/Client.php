<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Client Model
 */
class Client extends Model
{
    protected $table = 'clients';
    
    protected $fillable = [
        'email', 'password', 'firstname', 'lastname', 'company',
        'address1', 'address2', 'city', 'state', 'postcode', 'country',
        'phone', 'currency', 'language', 'credit', 'tax_exempt', 'tax_id',
        'notes', 'status', 'email_verified', 'email_verified_at',
        'two_factor_secret', 'two_factor_enabled', 'last_login', 'last_login_ip'
    ];
    
    protected $hidden = ['password', 'two_factor_secret'];
    
    /**
     * Find client by email
     */
    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }
    
    /**
     * Create new client
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        }
        
        $clientId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ClientAdd', ['client_id' => $clientId, 'data' => $data]);
        
        Logger::activity('client_add', "New client registered: {$data['email']}", $clientId);
        
        return $clientId;
    }
    
    /**
     * Update client
     */
    public function update($id, $data)
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        } else {
            unset($data['password']);
        }
        
        $result = parent::update($id, $data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('ClientEdit', ['client_id' => $id, 'data' => $data]);
        
        return $result;
    }
    
    /**
     * Verify client password
     */
    public function verifyPassword($client, $password)
    {
        return password_verify($password, $client['password']);
    }
    
    /**
     * Get client's services
     */
    public function getServices($clientId)
    {
        return $this->db->fetchAll(
            "SELECT s.*, p.name as product_name, p.type as product_type
             FROM {$this->db->table('services')} s
             LEFT JOIN {$this->db->table('products')} p ON s.product_id = p.id
             WHERE s.client_id = ?
             ORDER BY s.created_at DESC",
            [$clientId]
        );
    }
    
    /**
     * Get client's domains
     */
    public function getDomains($clientId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('domains')} 
             WHERE client_id = ? 
             ORDER BY created_at DESC",
            [$clientId]
        );
    }
    
    /**
     * Get client's invoices
     */
    public function getInvoices($clientId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('invoices')} 
             WHERE client_id = ? 
             ORDER BY date DESC",
            [$clientId]
        );
    }
    
    /**
     * Get client's tickets
     */
    public function getTickets($clientId)
    {
        return $this->db->fetchAll(
            "SELECT t.*, d.name as department_name
             FROM {$this->db->table('tickets')} t
             LEFT JOIN {$this->db->table('support_departments')} d ON t.department_id = d.id
             WHERE t.client_id = ?
             ORDER BY t.last_reply DESC",
            [$clientId]
        );
    }
    
    /**
     * Get client's unpaid invoices total
     */
    public function getUnpaidTotal($clientId)
    {
        return $this->db->fetchValue(
            "SELECT COALESCE(SUM(total), 0) FROM {$this->db->table('invoices')} 
             WHERE client_id = ? AND status = 'unpaid'",
            [$clientId]
        ) ?? 0;
    }
    
    /**
     * Add credit to client
     */
    public function addCredit($clientId, $amount, $description = '')
    {
        $client = $this->find($clientId);
        
        if (!$client) {
            return false;
        }
        
        $newCredit = $client['credit'] + $amount;
        
        $this->db->update('clients', ['credit' => $newCredit], 'id = ?', [$clientId]);
        
        // Log credit change
        $this->db->insert('credit_log', [
            'client_id' => $clientId,
            'amount' => $amount,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $newCredit;
    }
    
    /**
     * Remove credit from client
     */
    public function removeCredit($clientId, $amount, $description = '')
    {
        return $this->addCredit($clientId, -$amount, $description);
    }
    
    /**
     * Get full name
     */
    public function getFullName($client)
    {
        return trim($client['firstname'] . ' ' . $client['lastname']);
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->count(),
            'active' => $this->count("status = 'active'"),
            'inactive' => $this->count("status = 'inactive'"),
            'closed' => $this->count("status = 'closed'"),
            'this_month' => $this->count("created_at >= ?", [date('Y-m-01')]),
        ];
    }
}
