<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Core\Database;

/**
 * Admin Server Management Controller
 */
class ServerController extends Controller
{
    /**
     * List servers
     */
    public function index()
    {
        $this->requireAdmin();
        
        $servers = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('servers')} ORDER BY name ASC"
        );
        
        // Get usage stats for each server
        foreach ($servers as &$server) {
            $accountCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM {$this->db->table('services')} WHERE server_id = ?",
                [$server['id']]
            );
            $server['account_count'] = $accountCount['count'] ?? 0;
        }
        
        $this->render('admin.servers.index', [
            'servers' => $servers,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Create new server
     */
    public function create()
    {
        $this->requireAdmin();
        
        $this->render('admin.servers.create', [
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Store new server
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => trim($_POST['type'] ?? 'hestiacp'),
            'hostname' => trim($_POST['hostname'] ?? ''),
            'port' => (int) ($_POST['port'] ?? 8083),
            'api_key' => trim($_POST['api_key'] ?? ''),
            'api_secret' => $this->encryptPassword(trim($_POST['api_secret'] ?? '')),
            'max_accounts' => (int) ($_POST['max_accounts'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('servers', $data);
        $this->flash('success', 'Server added successfully');
        $this->redirect(ADMIN_URL . '/servers');
    }
    
    /**
     * Edit server
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $server = $this->db->fetch(
            "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
            [$id]
        );
        
        if (!$server) {
            $this->flash('error', 'Server not found');
            $this->redirect(ADMIN_URL . '/servers');
        }
        
        $this->render('admin.servers.edit', [
            'server' => $server,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update server
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $server = $this->db->fetch(
            "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
            [$id]
        );
        
        if (!$server) {
            $this->flash('error', 'Server not found');
            $this->redirect(ADMIN_URL . '/servers');
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? $server['name']),
            'hostname' => trim($_POST['hostname'] ?? $server['hostname']),
            'port' => (int) ($_POST['port'] ?? $server['port']),
            'api_key' => trim($_POST['api_key'] ?? $server['api_key']),
            'max_accounts' => (int) ($_POST['max_accounts'] ?? $server['max_accounts']),
            'active' => isset($_POST['active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Only update secret if provided
        if (!empty($_POST['api_secret'])) {
            $data['api_secret'] = $this->encryptPassword(trim($_POST['api_secret']));
        }
        
        $this->db->update('servers', $data, 'id = ?', [$id]);
        $this->flash('success', 'Server updated successfully');
        $this->redirect(ADMIN_URL . '/servers');
    }
    
    /**
     * Test server connection
     */
    public function test($id)
    {
        $this->requireAdmin();
        
        $server = $this->db->fetch(
            "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
            [$id]
        );
        
        if (!$server) {
            $this->json(['success' => false, 'error' => 'Server not found'], 404);
            return;
        }
        
        // Load the appropriate server module
        $module = $this->loadServerModule($server['type']);
        
        if (!$module) {
            $this->json(['success' => false, 'error' => 'Server module not found']);
            return;
        }
        
        $result = $module->testConnection($server);
        $this->json($result);
    }
    
    /**
     * Get server statistics
     */
    public function stats($id)
    {
        $this->requireAdmin();
        
        $server = $this->db->fetch(
            "SELECT * FROM {$this->db->table('servers')} WHERE id = ?",
            [$id]
        );
        
        if (!$server) {
            $this->json(['success' => false, 'error' => 'Server not found'], 404);
            return;
        }
        
        $module = $this->loadServerModule($server['type']);
        
        if (!$module) {
            $this->json(['success' => false, 'error' => 'Server module not found']);
            return;
        }
        
        // Decrypt password if using old auth method
        if (isset($server['password'])) {
            $server['password'] = $this->decryptPassword($server['password']);
        }
        
        $result = $module->getServerStats($server);
        $this->json($result);
    }
    
    /**
     * Delete server
     */
    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $accountCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM {$this->db->table('services')} WHERE server_id = ?",
            [$id]
        );
        
        if ($accountCount['count'] > 0) {
            $this->flash('error', 'Cannot delete server with active accounts');
            $this->redirect(ADMIN_URL . '/servers');
        }
        
        $this->db->delete('servers', 'id = ?', [$id]);
        $this->flash('success', 'Server deleted successfully');
        $this->redirect(ADMIN_URL . '/servers');
    }
    
    /**
     * Load server module
     */
    private function loadServerModule($type)
    {
        $modulePath = ROOT_PATH . '/modules/servers/' . $type . '/' . $type . '.php';
        
        if (!file_exists($modulePath)) {
            return null;
        }
        
        require_once $modulePath;
        $className = 'OpenWHM\\Modules\\Servers\\' . $type . '\\' . $type;
        
        return new $className();
    }
    
    /**
     * Encrypt password
     */
    private function encryptPassword($password)
    {
        return openssl_encrypt($password, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
    }
    
    /**
     * Decrypt password
     */
    private function decryptPassword($encrypted)
    {
        return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
    }
}
