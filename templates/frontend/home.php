<?php
$title = COMPANY_NAME . ' - Premium Web Hosting';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1>Lightning-Fast Web Hosting</h1>
                <p class="lead">Power your website with enterprise-grade hosting. 99.9% uptime guaranteed, free SSL, and 24/7 expert support.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                    <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket me-2"></i>View Plans
                    </a>
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-headset me-2"></i>Contact Sales
                    </a>
                </div>
                
                <!-- Domain Search -->
                <div class="domain-search-box">
                    <form action="<?php echo BASE_URL; ?>/domains/check" method="GET">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="text" class="form-control form-control-lg" name="domain" placeholder="Find your perfect domain name..." required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="tld-prices mt-3">
                        <span>.com <strong>$9.99</strong></span>
                        <span>.net <strong>$12.99</strong></span>
                        <span>.org <strong>$11.99</strong></span>
                        <span>.io <strong>$29.99</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Choose Us?</h2>
            <p class="section-subtitle">Everything you need to succeed online</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4>Lightning Fast</h4>
                    <p>NVMe SSD storage, LiteSpeed web servers, and optimized caching for blazing-fast load times.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Rock-Solid Security</h4>
                    <p>Free SSL certificates, DDoS protection, malware scanning, and automatic backups included.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>24/7 Expert Support</h4>
                    <p>Our friendly support team is available around the clock to help you with any issues.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h4>99.9% Uptime</h4>
                    <p>Enterprise-grade infrastructure with redundant systems ensures your site stays online.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h4>Daily Backups</h4>
                    <p>Automatic daily backups with one-click restore, so your data is always safe and secure.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h4>Easy Control Panel</h4>
                    <p>Manage your hosting with our intuitive control panel. No technical knowledge required.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Hosting Plans</h2>
            <p class="section-subtitle">Choose the perfect plan for your needs</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($products)): ?>
                <?php foreach (array_slice($products, 0, 3) as $index => $product): ?>
                <div class="col-lg-4">
                    <div class="pricing-card <?php echo $index === 1 ? 'featured' : ''; ?>">
                        <?php if ($index === 1): ?>
                        <span class="badge-popular">Most Popular</span>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($product['group_name'] ?? 'Web Hosting'); ?></p>
                        <div class="price">
                            <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price_monthly'], 2); ?>
                            <small>/month</small>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Unlimited Bandwidth</li>
                            <li><i class="fas fa-check"></i> Free SSL Certificate</li>
                            <li><i class="fas fa-check"></i> 24/7 Support</li>
                            <li><i class="fas fa-check"></i> 99.9% Uptime</li>
                            <li><i class="fas fa-check"></i> Free Migration</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/cart/add-product?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100">
                            Order Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default pricing if no products -->
                <div class="col-lg-4">
                    <div class="pricing-card">
                        <h3>Starter</h3>
                        <p class="text-muted">Perfect for small websites</p>
                        <div class="price"><?php echo CURRENCY_SYMBOL; ?>4.99<small>/month</small></div>
                        <ul>
                            <li><i class="fas fa-check"></i> 10 GB SSD Storage</li>
                            <li><i class="fas fa-check"></i> Unlimited Bandwidth</li>
                            <li><i class="fas fa-check"></i> 1 Website</li>
                            <li><i class="fas fa-check"></i> Free SSL</li>
                            <li><i class="fas fa-check"></i> 24/7 Support</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-primary w-100">Order Now</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="pricing-card featured">
                        <span class="badge-popular">Most Popular</span>
                        <h3>Professional</h3>
                        <p class="text-muted">For growing businesses</p>
                        <div class="price"><?php echo CURRENCY_SYMBOL; ?>9.99<small>/month</small></div>
                        <ul>
                            <li><i class="fas fa-check"></i> 50 GB SSD Storage</li>
                            <li><i class="fas fa-check"></i> Unlimited Bandwidth</li>
                            <li><i class="fas fa-check"></i> Unlimited Websites</li>
                            <li><i class="fas fa-check"></i> Free SSL + Domain</li>
                            <li><i class="fas fa-check"></i> Priority Support</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-primary w-100">Order Now</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="pricing-card">
                        <h3>Business</h3>
                        <p class="text-muted">For high-traffic sites</p>
                        <div class="price"><?php echo CURRENCY_SYMBOL; ?>19.99<small>/month</small></div>
                        <ul>
                            <li><i class="fas fa-check"></i> 100 GB NVMe Storage</li>
                            <li><i class="fas fa-check"></i> Unlimited Everything</li>
                            <li><i class="fas fa-check"></i> Free CDN</li>
                            <li><i class="fas fa-check"></i> Staging Environment</li>
                            <li><i class="fas fa-check"></i> Dedicated IP</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-primary w-100">Order Now</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Get Started?</h2>
        <p class="mb-4">Join thousands of satisfied customers and launch your website today!</p>
        <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-light btn-lg">
            <i class="fas fa-rocket me-2"></i>Get Started Now
        </a>
    </div>
</section>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                    <h4>Easy Scaling</h4>
                    <p class="text-muted">Seamlessly upgrade your resources as your website grows. No downtime required.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Choose the perfect plan for your needs</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach (array_slice($products, 0, 3) as $index => $product): ?>
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card position-relative <?php echo $index === 1 ? 'featured' : ''; ?>">
                    <?php if ($index === 1): ?>
                    <span class="badge bg-primary">Most Popular</span>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)); ?></p>
                    
                    <div class="price">
                        $<?php echo number_format($product['price_monthly'] ?? 0, 2); ?>
                        <small>/month</small>
                    </div>
                    
                    <ul>
                        <?php 
                        $features = explode("\n", $product['features'] ?? '');
                        foreach (array_slice($features, 0, 6) as $feature):
                            if (trim($feature)):
                        ?>
                        <li><i class="fas fa-check"></i><?php echo htmlspecialchars(trim($feature)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                    
                    <a href="<?php echo BASE_URL; ?>/order/product/<?php echo $product['id']; ?>" class="btn <?php echo $index === 1 ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                        <i class="fas fa-shopping-cart me-2"></i>Order Now
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-outline-primary btn-lg">
                View All Hosting Plans <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <h2>Ready to Get Started?</h2>
        <p>Join thousands of satisfied customers who trust us with their websites.</p>
        <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-light btn-lg">
            <i class="fas fa-rocket me-2"></i>Get Started Now
        </a>
    </div>
</section>

