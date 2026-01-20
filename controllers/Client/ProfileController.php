<?php
namespace OpenWHM\Controllers\Client;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Client;

/**
 * Client Profile Controller
 */
class ProfileController extends Controller
{
    private $clientModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }
    
    /**
     * Show profile
     */
    public function index()
    {
        $this->requireClient();
        
        $clientId = $this->session->getClientId();
        $client = $this->clientModel->find($clientId);
        
        $this->render('client.profile.index', [
            'client' => $client
        ]);
    }
    
    /**
     * Update profile
     */
    public function update()
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        
        $data = [
            'firstname' => $this->input('firstname'),
            'lastname' => $this->input('lastname'),
            'company' => $this->input('company'),
            'address1' => $this->input('address1'),
            'address2' => $this->input('address2'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'postcode' => $this->input('postcode'),
            'country' => $this->input('country'),
            'phone' => $this->input('phone')
        ];
        
        // Validate
        if (empty($data['firstname']) || empty($data['lastname'])) {
            $this->flash('error', 'First name and last name are required');
            $this->redirect(CLIENT_URL . '/profile');
        }
        
        $this->clientModel->update($clientId, $data);
        
        $this->flash('success', 'Profile updated successfully');
        $this->redirect(CLIENT_URL . '/profile');
    }
    
    /**
     * Change password
     */
    public function changePassword()
    {
        $this->requireClient();
        $this->validateCsrf();
        
        $clientId = $this->session->getClientId();
        $client = $this->clientModel->find($clientId);
        
        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');
        
        // Validate current password
        if (!password_verify($currentPassword, $client['password'])) {
            $this->flash('error', 'Current password is incorrect');
            $this->redirect(CLIENT_URL . '/profile');
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            $this->flash('error', 'New password must be at least 8 characters');
            $this->redirect(CLIENT_URL . '/profile');
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match');
            $this->redirect(CLIENT_URL . '/profile');
        }
        
        $this->clientModel->update($clientId, ['password' => $newPassword]);
        
        $this->hooks->execute('ClientChangePassword', ['client_id' => $clientId]);
        
        $this->flash('success', 'Password changed successfully');
        $this->redirect(CLIENT_URL . '/profile');
    }
}
