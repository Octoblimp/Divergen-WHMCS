<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;
use OpenWHM\Models\Product;

/**
 * Admin Product Controller
 */
class ProductController extends Controller
{
    private $productModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
    }
    
    /**
     * List products
     */
    public function index()
    {
        $this->requireAdmin();
        
        $products = $this->db->fetchAll(
            "SELECT p.*, g.name as group_name 
             FROM {$this->db->table('products')} p
             LEFT JOIN {$this->db->table('product_groups')} g ON p.group_id = g.id
             ORDER BY g.sort_order ASC, p.sort_order ASC"
        );
        
        $groups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('product_groups')} ORDER BY sort_order ASC"
        );
        
        $this->render('admin.products.index', [
            'products' => $products,
            'groups' => $groups,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAdmin();
        
        $groups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('product_groups')} ORDER BY sort_order ASC"
        );
        
        $serverGroups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('server_groups')} ORDER BY name ASC"
        );
        
        $this->render('admin.products.create', [
            'groups' => $groups,
            'serverGroups' => $serverGroups,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Store new product
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $data = [
            'group_id' => $this->input('group_id'),
            'type' => $this->input('type', 'hosting'),
            'name' => $this->input('name'),
            'slug' => $this->input('slug') ?: $this->createSlug($this->input('name')),
            'description' => $this->input('description'),
            'features' => $this->input('features'),
            'pay_type' => $this->input('pay_type', 'recurring'),
            'price_monthly' => $this->input('price_monthly', 0),
            'price_quarterly' => $this->input('price_quarterly', 0),
            'price_semiannually' => $this->input('price_semiannually', 0),
            'price_annually' => $this->input('price_annually', 0),
            'price_biennially' => $this->input('price_biennially', 0),
            'price_triennially' => $this->input('price_triennially', 0),
            'setup_fee' => $this->input('setup_fee', 0),
            'module' => $this->input('module'),
            'server_group_id' => $this->input('server_group_id'),
            'config_option1' => $this->input('config_option1'),
            'config_option2' => $this->input('config_option2'),
            'config_option3' => $this->input('config_option3'),
            'hidden' => $this->input('hidden', 0)
        ];
        
        $productId = $this->productModel->create($data);
        
        $this->flash('success', 'Product created successfully');
        $this->redirect(ADMIN_URL . '/products/' . $productId . '/edit');
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->flash('error', 'Product not found');
            $this->redirect(ADMIN_URL . '/products');
        }
        
        $groups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('product_groups')} ORDER BY sort_order ASC"
        );
        
        $serverGroups = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('server_groups')} ORDER BY name ASC"
        );
        
        $this->render('admin.products.edit', [
            'product' => $product,
            'groups' => $groups,
            'serverGroups' => $serverGroups,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update product
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->flash('error', 'Product not found');
            $this->redirect(ADMIN_URL . '/products');
        }
        
        $data = [
            'group_id' => $this->input('group_id'),
            'type' => $this->input('type'),
            'name' => $this->input('name'),
            'slug' => $this->input('slug'),
            'description' => $this->input('description'),
            'features' => $this->input('features'),
            'pay_type' => $this->input('pay_type'),
            'price_monthly' => $this->input('price_monthly', 0),
            'price_quarterly' => $this->input('price_quarterly', 0),
            'price_semiannually' => $this->input('price_semiannually', 0),
            'price_annually' => $this->input('price_annually', 0),
            'price_biennially' => $this->input('price_biennially', 0),
            'price_triennially' => $this->input('price_triennially', 0),
            'setup_fee' => $this->input('setup_fee', 0),
            'module' => $this->input('module'),
            'server_group_id' => $this->input('server_group_id'),
            'config_option1' => $this->input('config_option1'),
            'config_option2' => $this->input('config_option2'),
            'config_option3' => $this->input('config_option3'),
            'hidden' => $this->input('hidden', 0),
            'retired' => $this->input('retired', 0)
        ];
        
        $this->productModel->update($id, $data);
        
        $this->flash('success', 'Product updated successfully');
        $this->redirect(ADMIN_URL . '/products/' . $id . '/edit');
    }
    
    /**
     * Delete product
     */
    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $this->productModel->delete($id);
        
        $this->flash('success', 'Product deleted successfully');
        $this->redirect(ADMIN_URL . '/products');
    }
    
    /**
     * Create URL slug
     */
    private function createSlug($string)
    {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
