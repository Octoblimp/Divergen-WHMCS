<?php
$title = 'Web Hosting Plans - ' . COMPANY_NAME;
ob_start();
?>

<!-- Hero Section -->
<section class="hero" style="padding: 10rem 0 4rem;">
    <div class="container text-center">
        <h1>Web Hosting Plans</h1>
        <p>Reliable, fast, and secure hosting for websites of all sizes</p>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing" style="margin-top: -3rem;">
    <div class="container">
        <div class="row g-4">
            <?php 
            $currentGroup = null;
            foreach ($products as $index => $product): 
                if ($product['group_name'] !== $currentGroup):
                    $currentGroup = $product['group_name'];
                    if ($index > 0) echo '</div>';
            ?>
            <?php if ($index > 0): ?></div><?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="text-center mb-4"><?php echo htmlspecialchars($currentGroup ?? 'Web Hosting'); ?></h3>
                </div>
            </div>
            <div class="row g-4">
            <?php endif; ?>
            
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card position-relative h-100">
                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p class="text-muted small"><?php echo htmlspecialchars(substr($product['description'] ?? 'Perfect for growing websites', 0, 100)); ?></p>
                    
                    <div class="price">
                        $<?php echo number_format($product['price_monthly'] ?? 0, 2); ?>
                        <small>/month</small>
                    </div>
                    
                    <?php if ($product['price_annually'] > 0): ?>
                    <p class="text-success small mb-3">
                        <i class="fas fa-tag me-1"></i>
                        Save <?php echo round(100 - ($product['price_annually'] / ($product['price_monthly'] * 12) * 100)); ?>% with annual billing
                    </p>
                    <?php endif; ?>
                    
                    <ul>
                        <?php 
                        $features = explode("\n", $product['features'] ?? '10GB SSD Storage\nUnlimited Bandwidth\nFree SSL Certificate\n5 Email Accounts\n24/7 Support');
                        foreach ($features as $feature):
                            $feature = trim($feature);
                            if ($feature):
                        ?>
                        <li><i class="fas fa-check"></i><?php echo htmlspecialchars($feature); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                    
                    <form action="<?php echo BASE_URL; ?>/cart/add-product" method="POST" class="mt-auto">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="mb-3">
                            <select class="form-select form-select-sm" name="billing_cycle">
                                <?php if ($product['price_monthly'] > 0): ?>
                                <option value="monthly">Monthly - $<?php echo number_format($product['price_monthly'], 2); ?></option>
                                <?php endif; ?>
                                <?php if ($product['price_quarterly'] > 0): ?>
                                <option value="quarterly">Quarterly - $<?php echo number_format($product['price_quarterly'], 2); ?></option>
                                <?php endif; ?>
                                <?php if ($product['price_semiannually'] > 0): ?>
                                <option value="semiannually">Semi-Annually - $<?php echo number_format($product['price_semiannually'], 2); ?></option>
                                <?php endif; ?>
                                <?php if ($product['price_annually'] > 0): ?>
                                <option value="annually">Annually - $<?php echo number_format($product['price_annually'], 2); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Order Now
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="features py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">All Plans Include</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-lock fa-2x text-primary mb-3"></i>
                <h6>Free SSL Certificate</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-hdd fa-2x text-primary mb-3"></i>
                <h6>NVMe SSD Storage</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-cloud-download-alt fa-2x text-primary mb-3"></i>
                <h6>Daily Backups</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-headset fa-2x text-primary mb-3"></i>
                <h6>24/7 Support</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                <h6>Email Hosting</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                <h6>DDoS Protection</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-tachometer-alt fa-2x text-primary mb-3"></i>
                <h6>Free CDN</h6>
            </div>
            <div class="col-md-3 col-6 text-center">
                <i class="fas fa-redo fa-2x text-primary mb-3"></i>
                <h6>30-Day Money Back</h6>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
?>
