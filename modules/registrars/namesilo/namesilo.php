<?php
namespace OpenWHM\Modules\Registrars\namesilo;

use OpenWHM\Core\Database;
use OpenWHM\Core\Logger;

/**
 * NameSilo Registrar Module for OpenWHM
 * 
 * API Documentation: https://www.namesilo.com/api-reference
 */
class namesilo
{
    private $apiKey;
    private $sandbox;
    private $apiUrl;
    
    public function __construct()
    {
        $this->apiKey = NAMESILO_API_KEY;
        $this->sandbox = NAMESILO_SANDBOX;
        $this->apiUrl = $this->sandbox 
            ? 'https://sandbox.namesilo.com/api/'
            : 'https://www.namesilo.com/api/';
    }
    
    /**
     * Get module configuration fields
     */
    public static function getConfigFields()
    {
        return [
            'api_key' => [
                'label' => 'API Key',
                'type' => 'text',
                'description' => 'Your NameSilo API key'
            ],
            'sandbox' => [
                'label' => 'Sandbox Mode',
                'type' => 'checkbox',
                'description' => 'Enable sandbox/testing mode'
            ]
        ];
    }
    
    /**
     * Check domain availability
     */
    public function checkAvailability($domain)
    {
        $response = $this->apiCall('checkRegisterAvailability', [
            'domains' => $domain
        ]);
        
        if (!$response['success']) {
            return ['available' => false, 'error' => $response['error']];
        }
        
        $available = isset($response['reply']['available']['domain']);
        $price = null;
        
        if ($available && isset($response['reply']['available']['price'])) {
            $price = (float) $response['reply']['available']['price'];
        }
        
        return [
            'available' => $available,
            'price' => $price,
            'premium' => isset($response['reply']['available']['premium'])
        ];
    }
    
    /**
     * Register a new domain
     */
    public function register($domain, $params = [])
    {
        // Build contact info
        $contact = $this->buildContactInfo($domain);
        
        $apiParams = array_merge([
            'domain' => $domain['domain'],
            'years' => $domain['registration_period'] ?? 1,
            'private' => $domain['id_protection'] ? 1 : 0,
            'auto_renew' => $domain['do_not_renew'] ? 0 : 1
        ], $contact);
        
        // Add nameservers if specified
        if (!empty($domain['nameserver1'])) {
            $apiParams['ns1'] = $domain['nameserver1'];
            $apiParams['ns2'] = $domain['nameserver2'] ?? '';
            $apiParams['ns3'] = $domain['nameserver3'] ?? '';
            $apiParams['ns4'] = $domain['nameserver4'] ?? '';
        }
        
        $response = $this->apiCall('registerDomain', $apiParams);
        
        if (!$response['success']) {
            Logger::error("NameSilo register failed: " . $response['error']);
            return ['success' => false, 'error' => $response['error']];
        }
        
        return [
            'success' => true,
            'domain' => $domain['domain']
        ];
    }
    
    /**
     * Renew a domain
     */
    public function renew($domain, $params = [])
    {
        $years = $params['years'] ?? 1;
        
        $response = $this->apiCall('renewDomain', [
            'domain' => $domain['domain'],
            'years' => $years
        ]);
        
        if (!$response['success']) {
            Logger::error("NameSilo renew failed: " . $response['error']);
            return ['success' => false, 'error' => $response['error']];
        }
        
        return [
            'success' => true,
            'expiry_date' => $response['reply']['expires'] ?? null
        ];
    }
    
    /**
     * Transfer a domain
     */
    public function transfer($domain, $params = [])
    {
        $contact = $this->buildContactInfo($domain);
        
        $apiParams = array_merge([
            'domain' => $domain['domain'],
            'auth' => $params['epp_code'] ?? '',
            'private' => $domain['id_protection'] ? 1 : 0,
            'auto_renew' => $domain['do_not_renew'] ? 0 : 1
        ], $contact);
        
        $response = $this->apiCall('transferDomain', $apiParams);
        
        if (!$response['success']) {
            Logger::error("NameSilo transfer failed: " . $response['error']);
            return ['success' => false, 'error' => $response['error']];
        }
        
        return [
            'success' => true,
            'order_id' => $response['reply']['order_id'] ?? null
        ];
    }
    
    /**
     * Update nameservers
     */
    public function updateNameservers($domain, $params = [])
    {
        $nameservers = $params['nameservers'] ?? [];
        
        $apiParams = [
            'domain' => $domain['domain']
        ];
        
        foreach ($nameservers as $i => $ns) {
            $apiParams['ns' . ($i + 1)] = $ns;
        }
        
        $response = $this->apiCall('changeNameServers', $apiParams);
        
        if (!$response['success']) {
            Logger::error("NameSilo nameserver update failed: " . $response['error']);
            return ['success' => false, 'error' => $response['error']];
        }
        
        return ['success' => true];
    }
    
    /**
     * Get nameservers
     */
    public function getNameservers($domain, $params = [])
    {
        $response = $this->apiCall('getDomainInfo', [
            'domain' => $domain['domain']
        ]);
        
        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }
        
        $nameservers = [];
        
        if (isset($response['reply']['nameservers']['nameserver'])) {
            $ns = $response['reply']['nameservers']['nameserver'];
            $nameservers = is_array($ns) ? $ns : [$ns];
        }
        
        return [
            'success' => true,
            'nameservers' => $nameservers
        ];
    }
    
    /**
     * Get EPP code
     */
    public function getEppCode($domain, $params = [])
    {
        $response = $this->apiCall('retrieveAuthCode', [
            'domain' => $domain['domain']
        ]);
        
        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }
        
        return [
            'success' => true,
            'epp_code' => $response['reply']['code'] ?? ''
        ];
    }
    
    /**
     * Get domain info
     */
    public function getDomainInfo($domain, $params = [])
    {
        $response = $this->apiCall('getDomainInfo', [
            'domain' => $domain['domain']
        ]);
        
        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }
        
        $info = $response['reply'];
        
        return [
            'success' => true,
            'status' => $info['status'] ?? 'Active',
            'expiry_date' => $info['expires'] ?? null,
            'created' => $info['created'] ?? null,
            'locked' => ($info['locked'] ?? 'No') === 'Yes',
            'private' => ($info['private'] ?? 'No') === 'Yes',
            'auto_renew' => ($info['auto_renew'] ?? 'No') === 'Yes'
        ];
    }
    
    /**
     * Enable/disable registrar lock
     */
    public function setRegistrarLock($domain, $params = [])
    {
        $lock = $params['lock'] ?? true;
        $action = $lock ? 'domainLock' : 'domainUnlock';
        
        $response = $this->apiCall($action, [
            'domain' => $domain['domain']
        ]);
        
        return ['success' => $response['success'], 'error' => $response['error'] ?? null];
    }
    
    /**
     * Enable/disable ID protection
     */
    public function setIdProtection($domain, $params = [])
    {
        $enable = $params['enable'] ?? true;
        $action = $enable ? 'addPrivacy' : 'removePrivacy';
        
        $response = $this->apiCall($action, [
            'domain' => $domain['domain']
        ]);
        
        return ['success' => $response['success'], 'error' => $response['error'] ?? null];
    }
    
    /**
     * Get DNS records
     */
    public function getDnsRecords($domain, $params = [])
    {
        $response = $this->apiCall('dnsListRecords', [
            'domain' => $domain['domain']
        ]);
        
        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }
        
        $records = [];
        
        if (isset($response['reply']['resource_record'])) {
            $rr = $response['reply']['resource_record'];
            $records = is_array($rr) && isset($rr[0]) ? $rr : [$rr];
        }
        
        return [
            'success' => true,
            'records' => $records
        ];
    }
    
    /**
     * Add DNS record
     */
    public function addDnsRecord($domain, $params = [])
    {
        $response = $this->apiCall('dnsAddRecord', [
            'domain' => $domain['domain'],
            'rrtype' => $params['type'] ?? 'A',
            'rrhost' => $params['host'] ?? '',
            'rrvalue' => $params['value'] ?? '',
            'rrttl' => $params['ttl'] ?? 7200,
            'rrdistance' => $params['priority'] ?? 0
        ]);
        
        return ['success' => $response['success'], 'error' => $response['error'] ?? null];
    }
    
    /**
     * Delete DNS record
     */
    public function deleteDnsRecord($domain, $params = [])
    {
        $response = $this->apiCall('dnsDeleteRecord', [
            'domain' => $domain['domain'],
            'rrid' => $params['record_id']
        ]);
        
        return ['success' => $response['success'], 'error' => $response['error'] ?? null];
    }
    
    /**
     * Update contact info
     */
    public function updateContact($domain, $params = [])
    {
        $contact = $this->buildContactInfo($domain);
        
        $response = $this->apiCall('contactDomainAssociate', array_merge([
            'domain' => $domain['domain']
        ], $contact));
        
        return ['success' => $response['success'], 'error' => $response['error'] ?? null];
    }
    
    /**
     * Build contact info from domain data
     */
    private function buildContactInfo($domain)
    {
        return [
            'fn' => $domain['firstname'] ?? '',
            'ln' => $domain['lastname'] ?? '',
            'ad' => $domain['address1'] ?? '',
            'cy' => $domain['city'] ?? '',
            'st' => $domain['state'] ?? '',
            'zp' => $domain['postcode'] ?? '',
            'ct' => $domain['country'] ?? 'US',
            'em' => $domain['client_email'] ?? $domain['email'] ?? '',
            'ph' => $domain['phone'] ?? ''
        ];
    }
    
    /**
     * Make API call to NameSilo
     */
    private function apiCall($operation, $params = [])
    {
        $params['version'] = 1;
        $params['type'] = 'xml';
        $params['key'] = $this->apiKey;
        
        $url = $this->apiUrl . $operation . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Logger::error("NameSilo API error: " . $error);
            return ['success' => false, 'error' => 'Connection error: ' . $error];
        }
        
        // Parse XML response
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            return ['success' => false, 'error' => 'Invalid API response'];
        }
        
        $result = json_decode(json_encode($xml), true);
        
        $code = (int) ($result['reply']['code'] ?? 0);
        $detail = $result['reply']['detail'] ?? 'Unknown error';
        
        // Success codes: 300, 301, 302
        if ($code >= 300 && $code <= 302) {
            return [
                'success' => true,
                'reply' => $result['reply']
            ];
        }
        
        return [
            'success' => false,
            'error' => $detail,
            'code' => $code
        ];
    }
    
    /**
     * Sync domain info from registrar
     */
    public function sync($domain, $params = [])
    {
        $info = $this->getDomainInfo($domain, $params);
        
        if (!$info['success']) {
            return $info;
        }
        
        $db = Database::getInstance();
        
        // Update domain in database
        $db->update('domains', [
            'expiry_date' => $info['expiry_date'],
            'id_protection' => $info['private'] ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$domain['id']]);
        
        return [
            'success' => true,
            'expiry_date' => $info['expiry_date'],
            'status' => $info['status']
        ];
    }
}
