<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;

/**
 * Client Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function loginForm()
    {
        if ($this->session->isClientLoggedIn()) {
            $this->redirect(CLIENT_URL);
        }
        
        $this->render('client.auth.login');
    }
    
    /**
     * Process login
     */
    public function login()
    {
        $email = $this->input('email');
        $password = $this->input('password');
        
        if (empty($email) || empty($password)) {
            $this->flash('error', 'Please enter your email and password');
            $this->redirect(CLIENT_URL . '/login');
        }
        
        $client = $this->db->fetch(
            "SELECT * FROM {$this->db->table('clients')} WHERE email = ? AND status = 'active'",
            [$email]
        );
        
        if (!$client || !password_verify($password, $client['password'])) {
            $this->flash('error', 'Invalid email or password');
            $this->redirect(CLIENT_URL . '/login');
        }
        
        // Update last login
        $this->db->update('clients', [
            'last_login' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR']
        ], 'id = ?', [$client['id']]);
        
        // Set session
        $this->session->setClient($client);
        
        // Fire hook
        $this->hooks->execute('ClientLogin', ['client_id' => $client['id']]);
        
        $this->flash('success', 'Welcome back!');
        $this->redirect(CLIENT_URL);
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $clientId = $this->session->getClientId();
        
        if ($clientId) {
            $this->hooks->execute('ClientLogout', ['client_id' => $clientId]);
        }
        
        $this->session->logoutClient();
        $this->flash('success', 'You have been logged out');
        $this->redirect(CLIENT_URL . '/login');
    }
    
    /**
     * Show registration form
     */
    public function registerForm()
    {
        if ($this->session->isClientLoggedIn()) {
            $this->redirect(CLIENT_URL);
        }
        
        $this->render('client.auth.register');
    }
    
    /**
     * Process registration
     */
    public function register()
    {
        $this->validateCsrf();
        
        $data = [
            'firstname' => $this->input('firstname'),
            'lastname' => $this->input('lastname'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'phone' => $this->input('phone'),
            'company' => $this->input('company'),
            'address1' => $this->input('address1'),
            'address2' => $this->input('address2'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'postcode' => $this->input('postcode'),
            'country' => $this->input('country', 'US')
        ];
        
        // Validate
        $errors = [];
        
        if (empty($data['firstname'])) $errors[] = 'First name is required';
        if (empty($data['lastname'])) $errors[] = 'Last name is required';
        if (empty($data['email'])) $errors[] = 'Email is required';
        if (empty($data['password'])) $errors[] = 'Password is required';
        if (strlen($data['password']) < 8) $errors[] = 'Password must be at least 8 characters';
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        // Check email exists
        $existing = $this->db->fetch(
            "SELECT id FROM {$this->db->table('clients')} WHERE email = ?",
            [$data['email']]
        );
        
        if ($existing) {
            $errors[] = 'An account with this email already exists';
        }
        
        if (!empty($errors)) {
            $this->flash('error', implode('<br>', $errors));
            $this->redirect(CLIENT_URL . '/register');
        }
        
        // Create client
        $clientModel = new \OpenWHM\Models\Client();
        $clientId = $clientModel->create($data);
        
        // Fire hook
        $this->hooks->execute('ClientAdd', ['client_id' => $clientId]);
        
        // Auto-login
        $client = $clientModel->find($clientId);
        $this->session->setClient($client);
        
        $this->flash('success', 'Welcome! Your account has been created.');
        $this->redirect(CLIENT_URL);
    }
}
