<?php
namespace OpenWHM\Core;

/**
 * Session Handler
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }
    
    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Clear all session data
     */
    public function clear()
    {
        session_unset();
    }
    
    /**
     * Destroy session
     */
    public function destroy()
    {
        session_destroy();
    }
    
    /**
     * Set flash message
     */
    public function flash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get flash message
     */
    public function getFlash($key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken()
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($token)
    {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Set logged in client
     */
    public function setClient($client)
    {
        $_SESSION['client_id'] = $client['id'];
        $_SESSION['client_email'] = $client['email'];
        $_SESSION['client_name'] = $client['firstname'] . ' ' . $client['lastname'];
    }
    
    /**
     * Get logged in client ID
     */
    public function getClientId()
    {
        return $_SESSION['client_id'] ?? null;
    }
    
    /**
     * Check if client is logged in
     */
    public function isClientLoggedIn()
    {
        return isset($_SESSION['client_id']);
    }
    
    /**
     * Set logged in admin
     */
    public function setAdmin($admin)
    {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
    }
    
    /**
     * Get logged in admin ID
     */
    public function getAdminId()
    {
        return $_SESSION['admin_id'] ?? null;
    }
    
    /**
     * Check if admin is logged in
     */
    public function isAdminLoggedIn()
    {
        return isset($_SESSION['admin_id']);
    }
    
    /**
     * Logout client
     */
    public function logoutClient()
    {
        unset($_SESSION['client_id']);
        unset($_SESSION['client_email']);
        unset($_SESSION['client_name']);
    }
    
    /**
     * Logout admin
     */
    public function logoutAdmin()
    {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_role']);
    }
}
