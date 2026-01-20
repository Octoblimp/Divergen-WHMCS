<?php
$title = 'Shopping Cart - ' . COMPANY_NAME;
ob_start();
?>

<!-- Hero Section -->
<section class="hero" style="padding: 10rem 0 4rem;">
    <div class="container text-center">
        <h1><i class="fas fa-shopping-cart me-3"></i>Shopping Cart</h1>
        <p>Review your order before checkout</p>
    </div>
</section>

<section style="margin-top: -3rem; padding-bottom: 5rem;">
    <div class="container">
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Cart Items</h5>
                        <?php if (!empty($cart['items'])): ?>
                        <a href="<?php echo BASE_URL; ?>/cart/clear" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>Clear Cart
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($cart['items'])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Your cart is empty</h4>
                            <p class="text-muted">Browse our products and add them to your cart.</p>
                            <div class="mt-4">
                                <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-primary me-2">
                                    <i class="fas fa-server me-2"></i>Web Hosting
                                </a>
                                <a href="<?php echo BASE_URL; ?>/domains" class="btn btn-outline-primary">
                                    <i class="fas fa-globe me-2"></i>Domains
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Details</th>
                                        <th class="text-end">Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['type'] === 'product'): ?>
                                            <i class="fas fa-cube text-primary me-2"></i>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <?php else: ?>
                                            <i class="fas fa-globe text-info me-2"></i>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['type'] === 'product'): ?>
                                            <span class="text-muted">
                                                <?php echo htmlspecialchars($item['billing_label']); ?>
                                                <?php if ($item['domain']): ?>
                                                    <br><small>Domain: <?php echo htmlspecialchars($item['domain']); ?></small>
                                                <?php endif; ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-muted">
                                                <?php echo htmlspecialchars($item['action']); ?> - <?php echo htmlspecialchars($item['period']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold">$<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>/cart/remove?type=<?php echo $item['type']; ?>&index=<?php echo $item['index']; ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>/hosting" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">$<?php echo number_format($cart['subtotal'], 2); ?></td>
                            </tr>
                            <?php if ($cart['tax'] > 0): ?>
                            <tr>
                                <td>Tax</td>
                                <td class="text-end">$<?php echo number_format($cart['tax'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong class="text-primary fs-5">$<?php echo number_format($cart['total'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    <?php if (!empty($cart['items'])): ?>
                    <div class="card-footer">
                        <a href="<?php echo BASE_URL; ?>/cart/checkout" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock me-2"></i>Checkout
                        </a>
                        <p class="text-center text-muted small mt-2 mb-0">
                            <i class="fas fa-shield-alt me-1"></i>Secure checkout with SSL encryption
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Promo Code -->
                <?php if (!empty($cart['items'])): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-tag me-2"></i>Promo Code</h6>
                        <form class="input-group">
                            <input type="text" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-primary" type="submit">Apply</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
