<?php
namespace OpenWHM\Models;

use OpenWHM\Core\Application;
use OpenWHM\Core\Logger;

/**
 * Product Model
 */
class Product extends Model
{
    protected $table = 'products';
    
    protected $fillable = [
        'group_id', 'type', 'name', 'slug', 'description', 'features',
        'welcome_email', 'stock_control', 'stock_quantity', 'pay_type',
        'price_monthly', 'price_quarterly', 'price_semiannually', 'price_annually',
        'price_biennially', 'price_triennially', 'setup_fee', 'module',
        'server_group_id', 'config_option1', 'config_option2', 'config_option3',
        'config_option4', 'config_option5', 'config_option6', 'custom_fields',
        'sort_order', 'hidden', 'retired'
    ];
    
    /**
     * Get products by group
     */
    public function getByGroup($groupId)
    {
        return $this->where(
            'group_id = ? AND hidden = 0 AND retired = 0',
            [$groupId],
            'sort_order ASC, name ASC'
        );
    }
    
    /**
     * Get product with group
     */
    public function getWithGroup($productId)
    {
        return $this->db->fetch(
            "SELECT p.*, g.name as group_name, g.slug as group_slug
             FROM {$this->getTable()} p
             LEFT JOIN {$this->db->table('product_groups')} g ON p.group_id = g.id
             WHERE p.id = ?",
            [$productId]
        );
    }
    
    /**
     * Get price for billing cycle
     */
    public function getPrice($product, $billingCycle)
    {
        $priceField = 'price_' . $billingCycle;
        return $product[$priceField] ?? 0;
    }
    
    /**
     * Get all billing cycles with prices
     */
    public function getBillingCycles($product)
    {
        $cycles = [
            'monthly' => ['name' => 'Monthly', 'months' => 1],
            'quarterly' => ['name' => 'Quarterly', 'months' => 3],
            'semiannually' => ['name' => 'Semi-Annually', 'months' => 6],
            'annually' => ['name' => 'Annually', 'months' => 12],
            'biennially' => ['name' => 'Biennially', 'months' => 24],
            'triennially' => ['name' => 'Triennially', 'months' => 36]
        ];
        
        $available = [];
        
        foreach ($cycles as $key => $cycle) {
            $price = $this->getPrice($product, $key);
            
            if ($price > 0 || ($product['pay_type'] === 'free' && $key === 'monthly')) {
                $available[$key] = array_merge($cycle, [
                    'price' => $price,
                    'price_monthly' => $price / $cycle['months'],
                    'setup_fee' => $product['setup_fee']
                ]);
            }
        }
        
        return $available;
    }
    
    /**
     * Check stock availability
     */
    public function isInStock($productId)
    {
        $product = $this->find($productId);
        
        if (!$product) {
            return false;
        }
        
        if (!$product['stock_control']) {
            return true;
        }
        
        return $product['stock_quantity'] > 0;
    }
    
    /**
     * Decrease stock
     */
    public function decreaseStock($productId)
    {
        $product = $this->find($productId);
        
        if ($product && $product['stock_control'] && $product['stock_quantity'] > 0) {
            $this->db->query(
                "UPDATE {$this->getTable()} SET stock_quantity = stock_quantity - 1 WHERE id = ?",
                [$productId]
            );
        }
    }
    
    /**
     * Get module configuration
     */
    public function getModuleConfig($product)
    {
        return [
            'option1' => $product['config_option1'],
            'option2' => $product['config_option2'],
            'option3' => $product['config_option3'],
            'option4' => $product['config_option4'],
            'option5' => $product['config_option5'],
            'option6' => $product['config_option6']
        ];
    }
    
    /**
     * Get custom fields
     */
    public function getCustomFields($product)
    {
        if (empty($product['custom_fields'])) {
            return [];
        }
        
        return json_decode($product['custom_fields'], true) ?? [];
    }
    
    /**
     * Get related server group
     */
    public function getServerGroup($product)
    {
        if (!$product['server_group_id']) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT * FROM {$this->db->table('server_groups')} WHERE id = ?",
            [$product['server_group_id']]
        );
    }
    
    /**
     * Get available server for product
     */
    public function getAvailableServer($product)
    {
        $serverGroup = $this->getServerGroup($product);
        
        if (!$serverGroup) {
            return null;
        }
        
        if ($serverGroup['fill_type'] === 'fill') {
            // Get least loaded server
            return $this->db->fetch(
                "SELECT s.*, 
                        (SELECT COUNT(*) FROM {$this->db->table('services')} 
                         WHERE server_id = s.id AND status IN ('active', 'suspended')) as account_count
                 FROM {$this->db->table('servers')} s
                 JOIN {$this->db->table('server_group_relations')} sgr ON s.id = sgr.server_id
                 WHERE sgr.group_id = ? AND s.active = 1 AND s.disabled = 0
                 ORDER BY account_count ASC
                 LIMIT 1",
                [$serverGroup['id']]
            );
        } else {
            // Round robin
            return $this->db->fetch(
                "SELECT s.* 
                 FROM {$this->db->table('servers')} s
                 JOIN {$this->db->table('server_group_relations')} sgr ON s.id = sgr.server_id
                 WHERE sgr.group_id = ? AND s.active = 1 AND s.disabled = 0
                 ORDER BY RAND()
                 LIMIT 1",
                [$serverGroup['id']]
            );
        }
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 6)
    {
        return $this->db->fetchAll(
            "SELECT p.*, g.name as group_name
             FROM {$this->getTable()} p
             LEFT JOIN {$this->db->table('product_groups')} g ON p.group_id = g.id
             WHERE p.hidden = 0 AND p.retired = 0
             ORDER BY p.sort_order ASC
             LIMIT ?",
            [$limit]
        );
    }
}
