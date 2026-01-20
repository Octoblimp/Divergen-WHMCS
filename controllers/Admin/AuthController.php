<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;

/**
 * Admin Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function loginForm()
    {
        if ($this->session->isAdminLoggedIn()) {
            $this->redirect(ADMIN_URL);
        }
        
        $this->render('admin.auth.login');
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
            $this->redirect(ADMIN_URL . '/login');
        }
        
        $admin = $this->db->fetch(
            "SELECT * FROM {$this->db->table('admins')} WHERE email = ? AND active = 1",
            [$email]
        );
        
        if (!$admin || !password_verify($password, $admin['password'])) {
            $this->flash('error', 'Invalid email or password');
            $this->redirect(ADMIN_URL . '/login');
        }
        
        // Update last login
        $this->db->update('admins', [
            'last_login' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR']
        ], 'id = ?', [$admin['id']]);
        
        // Set session
        $this->session->setAdmin($admin);
        
        // Fire hook
        $this->hooks->execute('AdminLogin', ['admin_id' => $admin['id']]);
        
        $this->flash('success', 'Welcome back, ' . $admin['name']);
        $this->redirect(ADMIN_URL);
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $adminId = $this->session->getAdminId();
        
        if ($adminId) {
            $this->hooks->execute('AdminLogout', ['admin_id' => $adminId]);
        }
        
        $this->session->logoutAdmin();
        $this->flash('success', 'You have been logged out');
        $this->redirect(ADMIN_URL . '/login');
    }
}
