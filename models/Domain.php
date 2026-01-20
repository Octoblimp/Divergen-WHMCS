<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Domain Model
 */
class Domain extends Model
{
    protected $table = 'domains';
    
    protected $fillable = [
        'client_id', 'order_id', 'domain', 'registrar', 'registration_date',
        'expiry_date', 'next_due_date', 'next_invoice_date', 'registration_period',
        'recurring_amount', 'first_payment_amount', 'status', 'is_premium',
        'id_protection', 'dns_management', 'email_forwarding', 'do_not_renew',
        'nameserver1', 'nameserver2', 'nameserver3', 'nameserver4', 'nameserver5',
        'registrant_id', 'notes'
    ];
    
    /**
     * Create new domain
     */
    public function create($data)
    {
        $domainId = parent::create($data);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('DomainCreated', [
            'domain_id' => $domainId,
            'data' => $data
        ]);
        
        Logger::activity('domain_created', "Domain {$data['domain']} created", $data['client_id']);
        
        return $domainId;
    }
    
    /**
     * Get domain with client details
     */
    public function getWithClient($domainId)
    {
        return $this->db->fetch(
            "SELECT d.*, c.email as client_email, c.firstname, c.lastname,
                    c.company, c.address1, c.address2, c.city, c.state, c.postcode, c.country, c.phone
             FROM {$this->getTable()} d
             LEFT JOIN {$this->db->table('clients')} c ON d.client_id = c.id
             WHERE d.id = ?",
            [$domainId]
        );
    }
    
    /**
     * Activate domain (register with registrar)
     */
    public function activate($domainId)
    {
        $domain = $this->getWithClient($domainId);
        
        if (!$domain) {
            return false;
        }
        
        // Call registrar module
        $result = $this->callRegistrar($domain, 'register');
        
        if (!$result['success']) {
            Logger::error("Failed to register domain: " . ($result['error'] ?? 'Unknown error'));
            return false;
        }
        
        // Update domain status
        $this->update($domainId, [
            'status' => 'active',
            'registration_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime('+' . $domain['registration_period'] . ' years'))
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('DomainRegister', [
            'domain_id' => $domainId,
            'domain' => $domain
        ]);
        
        Logger::activity('domain_registered', "Domain {$domain['domain']} registered", $domain['client_id']);
        
        return true;
    }
    
    /**
     * Renew domain
     */
    public function renew($domainId, $years = 1)
    {
        $domain = $this->getWithClient($domainId);
        
        if (!$domain) {
            return false;
        }
        
        // Call registrar module
        $result = $this->callRegistrar($domain, 'renew', ['years' => $years]);
        
        if (!$result['success']) {
            Logger::error("Failed to renew domain: " . ($result['error'] ?? 'Unknown error'));
            return false;
        }
        
        // Update domain dates
        $newExpiry = date('Y-m-d', strtotime($domain['expiry_date'] . ' +' . $years . ' years'));
        
        $this->update($domainId, [
            'expiry_date' => $newExpiry,
            'next_due_date' => $newExpiry
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('DomainRenew', [
            'domain_id' => $domainId,
            'domain' => $domain,
            'years' => $years
        ]);
        
        Logger::activity('domain_renewed', "Domain {$domain['domain']} renewed for {$years} year(s)", $domain['client_id']);
        
        return true;
    }
    
    /**
     * Transfer domain
     */
    public function transfer($domainId, $eppCode = null)
    {
        $domain = $this->getWithClient($domainId);
        
        if (!$domain) {
            return false;
        }
        
        // Call registrar module
        $result = $this->callRegistrar($domain, 'transfer', ['epp_code' => $eppCode]);
        
        if (!$result['success']) {
            Logger::error("Failed to transfer domain: " . ($result['error'] ?? 'Unknown error'));
            return false;
        }
        
        // Update domain status
        $this->update($domainId, [
            'status' => 'pending_transfer'
        ]);
        
        // Fire hook
        Application::getInstance()->getHooks()->execute('DomainTransfer', [
            'domain_id' => $domainId,
            'domain' => $domain
        ]);
        
        Logger::activity('domain_transfer', "Domain {$domain['domain']} transfer initiated", $domain['client_id']);
        
        return true;
    }
    
    /**
     * Update nameservers
     */
    public function updateNameservers($domainId, $nameservers)
    {
        $domain = $this->getWithClient($domainId);
        
        if (!$domain) {
            return false;
        }
        
        // Call registrar module
        $result = $this->callRegistrar($domain, 'updateNameservers', ['nameservers' => $nameservers]);
        
        if (!$result['success']) {
            Logger::error("Failed to update nameservers: " . ($result['error'] ?? 'Unknown error'));
            return false;
        }
        
        // Update domain record
        $this->update($domainId, [
            'nameserver1' => $nameservers[0] ?? null,
            'nameserver2' => $nameservers[1] ?? null,
            'nameserver3' => $nameservers[2] ?? null,
            'nameserver4' => $nameservers[3] ?? null,
            'nameserver5' => $nameservers[4] ?? null
        ]);
        
        return true;
    }
    
    /**
     * Get EPP code
     */
    public function getEppCode($domainId)
    {
        $domain = $this->getWithClient($domainId);
        
        if (!$domain) {
            return null;
        }
        
        $result = $this->callRegistrar($domain, 'getEppCode');
        
        return $result['success'] ? $result['epp_code'] : null;
    }
    
    /**
     * Call registrar module
     */
    public function callRegistrar($domain, $function, $params = [])
    {
        $registrarName = $domain['registrar'];
        $registrarClass = "OpenWHM\\Modules\\Registrars\\{$registrarName}\\{$registrarName}";
        
        if (!class_exists($registrarClass)) {
            // Try to load module
            $modulePath = ROOT_PATH . "/modules/registrars/{$registrarName}/{$registrarName}.php";
            
            if (file_exists($modulePath)) {
                require_once $modulePath;
            } else {
                return ['success' => false, 'error' => "Registrar module not found: {$registrarName}"];
            }
        }
        
        if (!class_exists($registrarClass)) {
            return ['success' => false, 'error' => "Registrar class not found: {$registrarClass}"];
        }
        
        $registrar = new $registrarClass();
        
        if (!method_exists($registrar, $function)) {
            return ['success' => false, 'error' => "Registrar function not found: {$function}"];
        }
        
        return $registrar->$function($domain, $params);
    }
    
    /**
     * Check domain availability
     */
    public function checkAvailability($domain, $registrar = 'namesilo')
    {
        $registrarClass = "OpenWHM\\Modules\\Registrars\\{$registrar}\\{$registrar}";
        
        if (!class_exists($registrarClass)) {
            $modulePath = ROOT_PATH . "/modules/registrars/{$registrar}/{$registrar}.php";
            
            if (file_exists($modulePath)) {
                require_once $modulePath;
            } else {
                return ['available' => false, 'error' => "Registrar module not found"];
            }
        }
        
        if (!class_exists($registrarClass)) {
            return ['available' => false, 'error' => "Registrar class not found"];
        }
        
        $registrarObj = new $registrarClass();
        return $registrarObj->checkAvailability($domain);
    }
    
    /**
     * Get domain pricing
     */
    public function getPricing($tld, $registrar = null)
    {
        $sql = "SELECT * FROM {$this->db->table('domain_pricing')} WHERE tld = ? AND enabled = 1";
        $params = [$tld];
        
        if ($registrar) {
            $sql .= " AND registrar = ?";
            $params[] = $registrar;
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Get all enabled TLDs
     */
    public function getEnabledTlds()
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('domain_pricing')} 
             WHERE enabled = 1 
             ORDER BY sort_order ASC, tld ASC"
        );
    }
    
    /**
     * Get domains expiring soon
     */
    public function getExpiringSoon($days = 30)
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->db->fetchAll(
            "SELECT d.*, c.email, c.firstname, c.lastname
             FROM {$this->getTable()} d
             LEFT JOIN {$this->db->table('clients')} c ON d.client_id = c.id
             WHERE d.status = 'active' 
             AND d.do_not_renew = 0
             AND d.expiry_date <= ?
             ORDER BY d.expiry_date ASC",
            [$date]
        );
    }
    
    /**
     * Get statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->count(),
            'active' => $this->count("status = 'active'"),
            'pending' => $this->count("status = 'pending'"),
            'expired' => $this->count("status = 'expired'"),
            'expiring_30_days' => $this->count("status = 'active' AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")
        ];
    }
}
