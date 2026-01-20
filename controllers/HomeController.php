<?php
namespace OpenWHM\Controllers;

use OpenWHM\Core\Controller;

/**
 * Frontend Home Controller
 */
class HomeController extends Controller
{
    /**
     * Show home page
     */
    public function index()
    {
        // Get featured products
        $products = $this->db->fetchAll(
            "SELECT p.*, g.name as group_name 
             FROM {$this->db->table('products')} p
             LEFT JOIN {$this->db->table('product_groups')} g ON p.group_id = g.id
             WHERE p.hidden = 0 AND p.retired = 0
             ORDER BY p.sort_order ASC
             LIMIT 6"
        );
        
        // Get product groups
        $groups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('product_groups')} 
             WHERE hidden = 0 
             ORDER BY sort_order ASC"
        );
        
        $this->render('frontend.home', [
            'products' => $products,
            'groups' => $groups
        ]);
    }
    
    /**
     * Show hosting page
     */
    public function hosting()
    {
        $products = $this->db->fetchAll(
            "SELECT p.*, g.name as group_name 
             FROM {$this->db->table('products')} p
             LEFT JOIN {$this->db->table('product_groups')} g ON p.group_id = g.id
             WHERE p.hidden = 0 AND p.retired = 0 AND p.type = 'hosting'
             ORDER BY g.sort_order ASC, p.sort_order ASC"
        );
        
        $groups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('product_groups')} 
             WHERE hidden = 0 
             ORDER BY sort_order ASC"
        );
        
        $this->render('frontend.hosting', [
            'products' => $products,
            'groups' => $groups
        ]);
    }
    
    /**
     * Show domains page
     */
    public function domains()
    {
        $pricing = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('domain_pricing')} 
             ORDER BY sort_order ASC"
        );
        
        $this->render('frontend.domains', [
            'pricing' => $pricing
        ]);
    }
    
    /**
     * Domain availability check
     */
    public function checkDomain()
    {
        $domain = $this->input('domain');
        
        if (empty($domain)) {
            $this->json(['error' => 'Please enter a domain name']);
        }
        
        // Clean domain
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $domain);
        
        // Split domain and TLD
        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            // Add default TLD
            $domain .= '.com';
            $sld = $parts[0];
            $tld = 'com';
        } else {
            $tld = array_pop($parts);
            $sld = implode('.', $parts);
        }
        
        // Get pricing
        $pricing = $this->db->fetch(
            "SELECT * FROM {$this->db->table('domain_pricing')} WHERE tld = ?",
            ['.' . $tld]
        );
        
        if (!$pricing) {
            $this->json(['error' => 'This TLD is not available for registration']);
        }
        
        // Check availability using NameSilo
        $domainModel = new \OpenWHM\Models\Domain();
        $available = $domainModel->checkAvailability($sld . '.' . $tld);
        
        $this->json([
            'domain' => $sld . '.' . $tld,
            'available' => $available,
            'pricing' => [
                'register' => $pricing['register_price'],
                'renew' => $pricing['renew_price'],
                'transfer' => $pricing['transfer_price']
            ]
        ]);
    }
    
    /**
     * Show contact page
     */
    public function contact()
    {
        $this->render('frontend.contact');
    }
    
    /**
     * Process contact form
     */
    public function submitContact()
    {
        $this->validateCsrf();
        
        $name = $this->input('name');
        $email = $this->input('email');
        $subject = $this->input('subject');
        $message = $this->input('message');
        
        // Validate
        if (empty($name) || empty($email) || empty($message)) {
            $this->flash('error', 'Please fill in all required fields');
            $this->redirect(BASE_URL . '/contact');
        }
        
        // Create ticket
        $ticketModel = new \OpenWHM\Models\Ticket();
        $ticketModel->create([
            'department_id' => 1, // General
            'subject' => $subject ?: 'Contact Form Submission',
            'message' => $message,
            'name' => $name,
            'email' => $email,
            'priority' => 'medium'
        ]);
        
        $this->flash('success', 'Thank you for your message. We will get back to you shortly.');
        $this->redirect(BASE_URL . '/contact');
    }
}
