<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Client;

/**
 * Admin Client Controller
 */
class ClientController extends Controller
{
    private $clientModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }
    
    /**
     * List clients
     */
    public function index()
    {
        $this->requireAdmin();
        
        $page = (int) ($this->query('page') ?? 1);
        $search = $this->query('search');
        
        if ($search) {
            $conditions = "firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR company LIKE ?";
            $params = array_fill(0, 4, "%{$search}%");
        } else {
            $conditions = '1';
            $params = [];
        }
        
        $clients = $this->clientModel->paginate($page, 25, $conditions, $params);
        
        $this->render('admin.clients.index', [
            'clients' => $clients,
            'search' => $search,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAdmin();
        
        $this->render('admin.clients.create', [
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Store new client
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $data = [
            'firstname' => $this->input('firstname'),
            'lastname' => $this->input('lastname'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'company' => $this->input('company'),
            'address1' => $this->input('address1'),
            'address2' => $this->input('address2'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'postcode' => $this->input('postcode'),
            'country' => $this->input('country'),
            'phone' => $this->input('phone'),
            'status' => $this->input('status', 'active')
        ];
        
        // Validate
        if (empty($data['email']) || empty($data['firstname']) || empty($data['lastname'])) {
            $this->flash('error', 'Please fill in all required fields');
            $this->redirect(ADMIN_URL . '/clients/add');
        }
        
        // Check email exists
        if ($this->clientModel->findByEmail($data['email'])) {
            $this->flash('error', 'A client with this email already exists');
            $this->redirect(ADMIN_URL . '/clients/add');
        }
        
        $clientId = $this->clientModel->create($data);
        
        $this->flash('success', 'Client created successfully');
        $this->redirect(ADMIN_URL . '/clients/' . $clientId);
    }
    
    /**
     * View client
     */
    public function view($id)
    {
        $this->requireAdmin();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->flash('error', 'Client not found');
            $this->redirect(ADMIN_URL . '/clients');
        }
        
        $services = $this->clientModel->getServices($id);
        $domains = $this->clientModel->getDomains($id);
        $invoices = $this->clientModel->getInvoices($id);
        $tickets = $this->clientModel->getTickets($id);
        
        $this->render('admin.clients.view', [
            'client' => $client,
            'services' => $services,
            'domains' => $domains,
            'invoices' => $invoices,
            'tickets' => $tickets,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->flash('error', 'Client not found');
            $this->redirect(ADMIN_URL . '/clients');
        }
        
        $this->render('admin.clients.edit', [
            'client' => $client,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update client
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->flash('error', 'Client not found');
            $this->redirect(ADMIN_URL . '/clients');
        }
        
        $data = [
            'firstname' => $this->input('firstname'),
            'lastname' => $this->input('lastname'),
            'email' => $this->input('email'),
            'company' => $this->input('company'),
            'address1' => $this->input('address1'),
            'address2' => $this->input('address2'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'postcode' => $this->input('postcode'),
            'country' => $this->input('country'),
            'phone' => $this->input('phone'),
            'status' => $this->input('status'),
            'notes' => $this->input('notes')
        ];
        
        // Update password if provided
        if ($this->input('password')) {
            $data['password'] = $this->input('password');
        }
        
        $this->clientModel->update($id, $data);
        
        $this->flash('success', 'Client updated successfully');
        $this->redirect(ADMIN_URL . '/clients/' . $id);
    }
    
    /**
     * Delete client
     */
    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->flash('error', 'Client not found');
            $this->redirect(ADMIN_URL . '/clients');
        }
        
        $this->clientModel->delete($id);
        
        // Fire hook
        $this->hooks->execute('ClientDelete', ['client_id' => $id]);
        
        $this->flash('success', 'Client deleted successfully');
        $this->redirect(ADMIN_URL . '/clients');
    }
}
