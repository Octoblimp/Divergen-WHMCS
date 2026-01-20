<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Service;

/**
 * Admin Service Controller
 */
class ServiceController extends Controller
{
    private $serviceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new Service();
    }
    
    /**
     * List services
     */
    public function index()
    {
        $this->requireAdmin();
        
        $page = (int) ($this->query('page') ?? 1);
        $status = $this->query('status');
        
        if ($status) {
            $conditions = "status = ?";
            $params = [$status];
        } else {
            $conditions = '1';
            $params = [];
        }
        
        $services = $this->serviceModel->paginate($page, 25, $conditions, $params);
        
        // Get details for each service
        foreach ($services['items'] as &$service) {
            $service = $this->serviceModel->getWithDetails($service['id']);
        }
        
        $this->render('admin.services.index', [
            'services' => $services,
            'status' => $status,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * View service
     */
    public function view($id)
    {
        $this->requireAdmin();
        
        $service = $this->serviceModel->getWithDetails($id);
        
        if (!$service) {
            $this->flash('error', 'Service not found');
            $this->redirect(ADMIN_URL . '/services');
        }
        
        // Get module functions
        $moduleFunctions = [];
        if ($service['module']) {
            // Get available module functions
        }
        
        $this->render('admin.services.view', [
            'service' => $service,
            'moduleFunctions' => $moduleFunctions,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Suspend service
     */
    public function suspend($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $reason = $this->input('reason', 'Suspended by administrator');
        
        $result = $this->serviceModel->suspend($id, $reason);
        
        if ($result) {
            $this->flash('success', 'Service suspended');
        } else {
            $this->flash('error', 'Failed to suspend service');
        }
        
        $this->redirect(ADMIN_URL . '/services/' . $id);
    }
    
    /**
     * Unsuspend service
     */
    public function unsuspend($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $result = $this->serviceModel->unsuspend($id);
        
        if ($result) {
            $this->flash('success', 'Service unsuspended');
        } else {
            $this->flash('error', 'Failed to unsuspend service');
        }
        
        $this->redirect(ADMIN_URL . '/services/' . $id);
    }
    
    /**
     * Terminate service
     */
    public function terminate($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $result = $this->serviceModel->terminate($id);
        
        if ($result) {
            $this->flash('success', 'Service terminated');
        } else {
            $this->flash('error', 'Failed to terminate service');
        }
        
        $this->redirect(ADMIN_URL . '/services/' . $id);
    }
}
