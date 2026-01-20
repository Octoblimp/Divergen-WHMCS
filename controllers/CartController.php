<?php
namespace OpenWHM\Controllers;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Product;
use OpenWHM\Models\Domain;

/**
 * Cart Controller
 */
class CartController extends Controller
{
    private $productModel;
    private $domainModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->domainModel = new Domain();
        
        // Initialize cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'products' => [],
                'domains' => []
            ];
        }
    }
    
    /**
     * Show cart
     */
    public function index()
    {
        $cart = $this->getCartDetails();
        
        $this->render('frontend.cart.index', [
            'cart' => $cart
        ]);
    }
    
    /**
     * Add product to cart
     */
    public function addProduct()
    {
        $productId = (int) $this->input('product_id');
        $billingCycle = $this->input('billing_cycle', 'monthly');
        $domain = $this->input('domain');
        
        $product = $this->productModel->find($productId);
        
        if (!$product) {
            $this->flash('error', 'Product not found');
            $this->redirect(BASE_URL . '/hosting');
        }
        
        // Add to cart
        $_SESSION['cart']['products'][] = [
            'product_id' => $productId,
            'billing_cycle' => $billingCycle,
            'domain' => $domain,
            'config_options' => []
        ];
        
        $this->flash('success', 'Product added to cart');
        $this->redirect(BASE_URL . '/cart');
    }
    
    /**
     * Add domain to cart
     */
    public function addDomain()
    {
        $domain = $this->input('domain');
        $type = $this->input('type', 'register'); // register, transfer
        $period = (int) $this->input('period', 1);
        
        // Get TLD
        $parts = explode('.', $domain, 2);
        if (count($parts) < 2) {
            $this->flash('error', 'Invalid domain name');
            $this->redirect(BASE_URL . '/domains');
        }
        
        $tld = '.' . $parts[1];
        
        // Get pricing
        $pricing = $this->db->fetch(
            "SELECT * FROM {$this->db->table('domain_pricing')} WHERE tld = ?",
            [$tld]
        );
        
        if (!$pricing) {
            $this->flash('error', 'This TLD is not available');
            $this->redirect(BASE_URL . '/domains');
        }
        
        // Add to cart
        $_SESSION['cart']['domains'][] = [
            'domain' => strtolower($domain),
            'type' => $type,
            'period' => $period,
            'tld' => $tld
        ];
        
        $this->flash('success', 'Domain added to cart');
        $this->redirect(BASE_URL . '/cart');
    }
    
    /**
     * Remove item from cart
     */
    public function remove()
    {
        $type = $this->input('type'); // product or domain
        $index = (int) $this->input('index');
        
        if ($type === 'product' && isset($_SESSION['cart']['products'][$index])) {
            array_splice($_SESSION['cart']['products'], $index, 1);
        } elseif ($type === 'domain' && isset($_SESSION['cart']['domains'][$index])) {
            array_splice($_SESSION['cart']['domains'], $index, 1);
        }
        
        $this->flash('success', 'Item removed from cart');
        $this->redirect(BASE_URL . '/cart');
    }
    
    /**
     * Clear cart
     */
    public function clear()
    {
        $_SESSION['cart'] = [
            'products' => [],
            'domains' => []
        ];
        
        $this->flash('success', 'Cart cleared');
        $this->redirect(BASE_URL . '/cart');
    }
    
    /**
     * Show checkout
     */
    public function checkout()
    {
        $cart = $this->getCartDetails();
        
        if (empty($cart['items'])) {
            $this->flash('error', 'Your cart is empty');
            $this->redirect(BASE_URL . '/cart');
        }
        
        // Get payment gateways
        $gateways = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('payment_gateways')} WHERE active = 1 ORDER BY sort_order ASC"
        );
        
        $client = null;
        if ($this->session->isClientLoggedIn()) {
            $clientModel = new \OpenWHM\Models\Client();
            $client = $clientModel->find($this->session->getClientId());
        }
        
        $this->render('frontend.cart.checkout', [
            'cart' => $cart,
            'gateways' => $gateways,
            'client' => $client,
            'isLoggedIn' => $this->session->isClientLoggedIn()
        ]);
    }
    
    /**
     * Process checkout
     */
    public function processCheckout()
    {
        $this->validateCsrf();
        
        $cart = $this->getCartDetails();
        
        if (empty($cart['items'])) {
            $this->flash('error', 'Your cart is empty');
            $this->redirect(BASE_URL . '/cart');
        }
        
        // Check if logged in or create/get client
        $clientId = $this->session->getClientId();
        
        if (!$clientId) {
            // Check if registering or logging in
            $existingClient = $this->input('existing_client');
            
            if ($existingClient) {
                // Login
                $email = $this->input('login_email');
                $password = $this->input('login_password');
                
                $client = $this->db->fetch(
                    "SELECT * FROM {$this->db->table('clients')} WHERE email = ? AND status = 'active'",
                    [$email]
                );
                
                if (!$client || !password_verify($password, $client['password'])) {
                    $this->flash('error', 'Invalid email or password');
                    $this->redirect(BASE_URL . '/cart/checkout');
                }
                
                $this->session->setClient($client);
                $clientId = $client['id'];
            } else {
                // Register new client
                $clientModel = new \OpenWHM\Models\Client();
                
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
                if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password'])) {
                    $this->flash('error', 'Please fill in all required fields');
                    $this->redirect(BASE_URL . '/cart/checkout');
                }
                
                // Check email exists
                $existing = $this->db->fetch(
                    "SELECT id FROM {$this->db->table('clients')} WHERE email = ?",
                    [$data['email']]
                );
                
                if ($existing) {
                    $this->flash('error', 'An account with this email already exists. Please login.');
                    $this->redirect(BASE_URL . '/cart/checkout');
                }
                
                $clientId = $clientModel->create($data);
                
                // Auto-login
                $client = $clientModel->find($clientId);
                $this->session->setClient($client);
            }
        }
        
        // Create order
        $orderModel = new \OpenWHM\Models\Order();
        $paymentMethod = $this->input('payment_method', 'Bank Transfer');
        
        $orderId = $orderModel->create([
            'client_id' => $clientId,
            'payment_method' => $paymentMethod,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // Add products to order
        foreach ($_SESSION['cart']['products'] as $item) {
            $product = $this->productModel->find($item['product_id']);
            if (!$product) continue;
            
            $pricing = $this->productModel->getBillingCycles($item['product_id']);
            $price = $pricing[$item['billing_cycle']]['price'] ?? $product['price_monthly'];
            
            // Create service
            $this->db->insert('services', [
                'client_id' => $clientId,
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'domain' => $item['domain'] ?? null,
                'billing_cycle' => $item['billing_cycle'],
                'amount' => $price,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Add domains to order
        foreach ($_SESSION['cart']['domains'] as $item) {
            $pricing = $this->db->fetch(
                "SELECT * FROM {$this->db->table('domain_pricing')} WHERE tld = ?",
                [$item['tld']]
            );
            
            if (!$pricing) continue;
            
            $price = $item['type'] === 'transfer' ? $pricing['transfer_price'] : $pricing['register_price'];
            $price = $price * $item['period'];
            
            // Create domain
            $this->db->insert('domains', [
                'client_id' => $clientId,
                'order_id' => $orderId,
                'domain' => $item['domain'],
                'registration_type' => $item['type'],
                'registration_period' => $item['period'],
                'amount' => $price,
                'registrar' => 'namesilo',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Create invoice
        $invoiceModel = new \OpenWHM\Models\Invoice();
        $invoiceId = $invoiceModel->createForOrder($orderId);
        
        // Update order with invoice
        $this->db->update('orders', [
            'invoice_id' => $invoiceId,
            'total' => $cart['total']
        ], 'id = ?', [$orderId]);
        
        // Clear cart
        $_SESSION['cart'] = [
            'products' => [],
            'domains' => []
        ];
        
        // Fire hook
        $this->hooks->execute('OrderCreated', ['order_id' => $orderId]);
        
        // Redirect to payment
        $this->flash('success', 'Order placed successfully!');
        $this->redirect(CLIENT_URL . '/invoices/' . $invoiceId . '/pay');
    }
    
    /**
     * Get cart details with pricing
     */
    private function getCartDetails()
    {
        $items = [];
        $subtotal = 0;
        
        // Products
        foreach ($_SESSION['cart']['products'] as $index => $item) {
            $product = $this->productModel->find($item['product_id']);
            if (!$product) continue;
            
            $pricing = $this->productModel->getBillingCycles($item['product_id']);
            $cycle = $pricing[$item['billing_cycle']] ?? null;
            
            if ($cycle) {
                $price = $cycle['price'] + ($product['setup_fee'] ?? 0);
                $subtotal += $price;
                
                $items[] = [
                    'type' => 'product',
                    'index' => $index,
                    'name' => $product['name'],
                    'domain' => $item['domain'] ?? null,
                    'billing_cycle' => $item['billing_cycle'],
                    'billing_label' => $cycle['label'],
                    'price' => $price,
                    'recurring' => $cycle['price'],
                    'setup' => $product['setup_fee'] ?? 0
                ];
            }
        }
        
        // Domains
        foreach ($_SESSION['cart']['domains'] as $index => $item) {
            $pricing = $this->db->fetch(
                "SELECT * FROM {$this->db->table('domain_pricing')} WHERE tld = ?",
                [$item['tld']]
            );
            
            if ($pricing) {
                $price = $item['type'] === 'transfer' ? $pricing['transfer_price'] : $pricing['register_price'];
                $price = $price * $item['period'];
                $subtotal += $price;
                
                $items[] = [
                    'type' => 'domain',
                    'index' => $index,
                    'name' => $item['domain'],
                    'action' => ucfirst($item['type']),
                    'period' => $item['period'] . ' year(s)',
                    'price' => $price
                ];
            }
        }
        
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal
        ];
    }
}
