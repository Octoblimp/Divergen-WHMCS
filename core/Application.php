<?php
namespace OpenWHM\Core;

/**
 * Main Application Class
 */
class Application
{
    private static $instance = null;
    private $router;
    private $db;
    private $session;
    private $extensions;
    private $hooks;
    
    public function __construct()
    {
        self::$instance = $this;
        
        // Initialize core components
        $this->initDatabase();
        $this->initSession();
        $this->initHooks();
        $this->initExtensions();
        $this->initRouter();
    }
    
    /**
     * Get application instance
     */
    public static function getInstance()
    {
        return self::$instance;
    }
    
    /**
     * Initialize database connection
     */
    private function initDatabase()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Initialize session handling
     */
    private function initSession()
    {
        $this->session = new Session();
    }
    
    /**
     * Initialize hook system
     */
    private function initHooks()
    {
        $this->hooks = new Hooks();
    }
    
    /**
     * Initialize extensions
     */
    private function initExtensions()
    {
        $this->extensions = new ExtensionManager();
        
        if (ENABLE_EXTENSIONS) {
            $this->extensions->loadAll();
        }
    }
    
    /**
     * Initialize router
     */
    private function initRouter()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }
    
    /**
     * Register application routes
     */
    private function registerRoutes()
    {
        // Frontend routes
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/hosting', 'HomeController@hosting');
        $this->router->get('/domains', 'HomeController@domains');
        $this->router->get('/domains/check', 'HomeController@checkDomain');
        $this->router->get('/contact', 'HomeController@contact');
        $this->router->post('/contact', 'HomeController@submitContact');
        
        // Cart routes
        $this->router->get('/cart', 'CartController@index');
        $this->router->post('/cart/add-product', 'CartController@addProduct');
        $this->router->post('/cart/add-domain', 'CartController@addDomain');
        $this->router->get('/cart/remove', 'CartController@remove');
        $this->router->get('/cart/clear', 'CartController@clear');
        $this->router->get('/cart/checkout', 'CartController@checkout');
        $this->router->post('/cart/process-checkout', 'CartController@processCheckout');
        
        // Client area routes
        $this->router->group('/client', function($router) {
            $router->get('/', 'Client\\DashboardController@index');
            $router->get('/login', 'Client\\AuthController@loginForm');
            $router->post('/login', 'Client\\AuthController@login');
            $router->get('/register', 'Client\\AuthController@registerForm');
            $router->post('/register', 'Client\\AuthController@register');
            $router->get('/logout', 'Client\\AuthController@logout');
            $router->get('/services', 'Client\\ServiceController@index');
            $router->get('/services/{id}', 'Client\\ServiceController@view');
            $router->post('/services/{id}/cancel', 'Client\\ServiceController@requestCancellation');
            $router->get('/domains', 'Client\\DomainController@index');
            $router->get('/domains/{id}', 'Client\\DomainController@view');
            $router->get('/invoices', 'Client\\InvoiceController@index');
            $router->get('/invoices/{id}', 'Client\\InvoiceController@view');
            $router->get('/invoices/{id}/pay', 'Client\\InvoiceController@pay');
            $router->post('/invoices/{id}/process', 'Client\\InvoiceController@processPayment');
            $router->post('/invoices/{id}/apply-credit', 'Client\\InvoiceController@applyCredit');
            $router->get('/invoices/{id}/download', 'Client\\InvoiceController@download');
            $router->get('/tickets', 'Client\\TicketController@index');
            $router->get('/tickets/new', 'Client\\TicketController@create');
            $router->post('/tickets/new', 'Client\\TicketController@store');
            $router->get('/tickets/{id}', 'Client\\TicketController@view');
            $router->post('/tickets/{id}/reply', 'Client\\TicketController@reply');
            $router->post('/tickets/{id}/close', 'Client\\TicketController@close');
            $router->get('/profile', 'Client\\ProfileController@index');
            $router->post('/profile', 'Client\\ProfileController@update');
            $router->post('/profile/password', 'Client\\ProfileController@changePassword');
        });
        
        // Admin routes
        $this->router->group('/admin', function($router) {
            $router->get('/', 'Admin\\DashboardController@index');
            $router->get('/login', 'Admin\\AuthController@loginForm');
            $router->post('/login', 'Admin\\AuthController@login');
            $router->get('/logout', 'Admin\\AuthController@logout');
            
            // Client management
            $router->get('/clients', 'Admin\\ClientController@index');
            $router->get('/clients/add', 'Admin\\ClientController@create');
            $router->post('/clients/add', 'Admin\\ClientController@store');
            $router->get('/clients/{id}', 'Admin\\ClientController@view');
            $router->get('/clients/{id}/edit', 'Admin\\ClientController@edit');
            $router->post('/clients/{id}/edit', 'Admin\\ClientController@update');
            $router->post('/clients/{id}/delete', 'Admin\\ClientController@delete');
            
            // Order management
            $router->get('/orders', 'Admin\\OrderController@index');
            $router->get('/orders/{id}', 'Admin\\OrderController@view');
            $router->post('/orders/{id}/accept', 'Admin\\OrderController@accept');
            $router->post('/orders/{id}/cancel', 'Admin\\OrderController@cancel');
            
            // Product management
            $router->get('/products', 'Admin\\ProductController@index');
            $router->get('/products/add', 'Admin\\ProductController@create');
            $router->post('/products/add', 'Admin\\ProductController@store');
            $router->get('/products/{id}/edit', 'Admin\\ProductController@edit');
            $router->post('/products/{id}/edit', 'Admin\\ProductController@update');
            $router->post('/products/{id}/delete', 'Admin\\ProductController@delete');
            
            // Invoice management
            $router->get('/invoices', 'Admin\\InvoiceController@index');
            $router->get('/invoices/{id}', 'Admin\\InvoiceController@view');
            $router->post('/invoices/{id}/paid', 'Admin\\InvoiceController@markPaid');
            
            // Service management
            $router->get('/services', 'Admin\\ServiceController@index');
            $router->get('/services/{id}', 'Admin\\ServiceController@view');
            $router->post('/services/{id}/suspend', 'Admin\\ServiceController@suspend');
            $router->post('/services/{id}/unsuspend', 'Admin\\ServiceController@unsuspend');
            $router->post('/services/{id}/terminate', 'Admin\\ServiceController@terminate');
            
            // Domain management
            $router->get('/domains', 'Admin\\DomainController@index');
            $router->get('/domains/{id}', 'Admin\\DomainController@view');
            
            // Support tickets
            $router->get('/tickets', 'Admin\\TicketController@index');
            $router->get('/tickets/{id}', 'Admin\\TicketController@view');
            $router->post('/tickets/{id}/reply', 'Admin\\TicketController@reply');
            $router->post('/tickets/{id}/close', 'Admin\\TicketController@close');
            
            // Server management
            $router->get('/servers', 'Admin\\ServerController@index');
            $router->get('/servers/add', 'Admin\\ServerController@create');
            $router->post('/servers/add', 'Admin\\ServerController@store');
            $router->get('/servers/{id}/edit', 'Admin\\ServerController@edit');
            $router->post('/servers/{id}/edit', 'Admin\\ServerController@update');
            $router->get('/servers/{id}/test', 'Admin\\ServerController@test');
            $router->get('/servers/{id}/stats', 'Admin\\ServerController@stats');
            $router->post('/servers/{id}/delete', 'Admin\\ServerController@delete');
            
            // Extensions
            $router->get('/extensions', 'Admin\\ExtensionController@index');
            $router->post('/extensions/{id}/enable', 'Admin\\ExtensionController@enable');
            $router->post('/extensions/{id}/disable', 'Admin\\ExtensionController@disable');
            $router->get('/extensions/{id}/settings', 'Admin\\ExtensionController@settings');
            $router->post('/extensions/{id}/settings', 'Admin\\ExtensionController@saveSettings');
            
            // Settings
            $router->get('/settings', 'Admin\\SettingsController@index');
            $router->post('/settings', 'Admin\\SettingsController@update');
            $router->get('/settings/email', 'Admin\\SettingsController@email');
            $router->post('/settings/email', 'Admin\\SettingsController@updateEmail');
            $router->get('/settings/payment', 'Admin\\SettingsController@payment');
            $router->post('/settings/payment', 'Admin\\SettingsController@updatePayment');
            $router->get('/settings/support', 'Admin\\SettingsController@support');
            $router->post('/settings/support', 'Admin\\SettingsController@updateSupport');
            
            // Analytics & Reports
            $router->get('/reports', 'Admin\\ReportsController@index');
        });
        
        // API routes
        $this->router->group('/api/v1', function($router) {
            $router->get('/clients', 'Api\\ClientController@index');
            $router->get('/clients/{id}', 'Api\\ClientController@show');
            $router->post('/clients', 'Api\\ClientController@store');
            $router->put('/clients/{id}', 'Api\\ClientController@update');
            $router->delete('/clients/{id}', 'Api\\ClientController@delete');
            
            $router->get('/orders', 'Api\\OrderController@index');
            $router->post('/orders', 'Api\\OrderController@store');
            
            $router->get('/invoices', 'Api\\InvoiceController@index');
            $router->get('/invoices/{id}', 'Api\\InvoiceController@show');
            
            $router->get('/services', 'Api\\ServiceController@index');
            $router->get('/services/{id}', 'Api\\ServiceController@show');
            
            $router->get('/domains', 'Api\\DomainController@index');
            $router->get('/domains/{id}', 'Api\\DomainController@show');
            $router->get('/domains/check/{domain}', 'Api\\DomainController@check');
        });
        
        // Allow extensions to register routes
        $this->hooks->execute('RegisterRoutes', [$this->router]);
    }
    
    /**
     * Run the application
     */
    public function run()
    {
        try {
            // Execute pre-dispatch hooks
            $this->hooks->execute('PreDispatch');
            
            // Dispatch the request
            $this->router->dispatch();
            
            // Execute post-dispatch hooks
            $this->hooks->execute('PostDispatch');
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Handle application errors
     */
    private function handleError(\Exception $e)
    {
        Logger::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if (error_reporting() > 0) {
            echo "<h1>Error</h1>";
            echo "<p>{$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        } else {
            // Show friendly error page
            include TEMPLATE_PATH . '/error/500.php';
        }
    }
    
    /**
     * Get database instance
     */
    public function getDatabase()
    {
        return $this->db;
    }
    
    /**
     * Get session instance
     */
    public function getSession()
    {
        return $this->session;
    }
    
    /**
     * Get hooks instance
     */
    public function getHooks()
    {
        return $this->hooks;
    }
    
    /**
     * Get extension manager
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    /**
     * Get router instance
     */
    public function getRouter()
    {
        return $this->router;
    }
}
