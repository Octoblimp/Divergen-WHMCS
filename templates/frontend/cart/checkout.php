<?php
$title = 'Checkout - ' . COMPANY_NAME;
ob_start();
?>

<!-- Hero Section -->
<section class="hero" style="padding: 10rem 0 4rem;">
    <div class="container text-center">
        <h1><i class="fas fa-lock me-3"></i>Secure Checkout</h1>
        <p>Complete your order</p>
    </div>
</section>

<section style="margin-top: -3rem; padding-bottom: 5rem;">
    <div class="container">
        <form method="POST" action="<?php echo BASE_URL; ?>/cart/process-checkout">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row g-4">
                <!-- Checkout Form -->
                <div class="col-lg-8">
                    <?php if (!$isLoggedIn): ?>
                    <!-- Account Options -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills mb-4" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#new-account" type="button">
                                        <i class="fas fa-user-plus me-2"></i>New Customer
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#existing-account" type="button">
                                        <i class="fas fa-sign-in-alt me-2"></i>Existing Customer
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- New Customer -->
                                <div class="tab-pane fade show active" id="new-account">
                                    <input type="hidden" name="existing_client" value="0">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="firstname" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="lastname" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="address1" placeholder="Street Address">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control" name="state">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" name="postcode">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Country</label>
                                            <select class="form-select" name="country">
                                                <option value="US">United States</option>
                                                <option value="GB">United Kingdom</option>
                                                <option value="CA">Canada</option>
                                                <option value="AU">Australia</option>
                                                <option value="DE">Germany</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password_confirm" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Existing Customer -->
                                <div class="tab-pane fade" id="existing-account">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control" name="login_email">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" name="login_password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Logged in user info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Payment Method -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($gateways)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Payment will be arranged after order placement. We will contact you with payment instructions.
                            </div>
                            <input type="hidden" name="payment_method" value="Bank Transfer">
                            <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($gateways as $gateway): ?>
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3">
                                        <input class="form-check-input" type="radio" name="payment_method" value="<?php echo htmlspecialchars($gateway['name']); ?>" id="gateway_<?php echo $gateway['id']; ?>" <?php echo $gateway === reset($gateways) ? 'checked' : ''; ?>>
                                        <label class="form-check-label w-100" for="gateway_<?php echo $gateway['id']; ?>">
                                            <?php if ($gateway['name'] === 'PayPal'): ?>
                                            <i class="fab fa-paypal fa-lg text-primary me-2"></i>
                                            <?php elseif ($gateway['name'] === 'Stripe'): ?>
                                            <i class="fab fa-stripe fa-lg text-primary me-2"></i>
                                            <?php else: ?>
                                            <i class="fas fa-money-bill fa-lg text-primary me-2"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($gateway['name']); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 100px;">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($cart['items'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php if ($item['type'] === 'product'): ?>
                                            <?php echo htmlspecialchars($item['billing_label']); ?>
                                            <?php else: ?>
                                            <?php echo htmlspecialchars($item['action'] . ' - ' . $item['period']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span>$<?php echo number_format($item['price'], 2); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-body border-top">
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
                                    <td><strong>Total Due Today</strong></td>
                                    <td class="text-end"><strong class="text-primary fs-4">$<?php echo number_format($cart['total'], 2); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree_tos" required>
                                <label class="form-check-label" for="agree_tos">
                                    I agree to the <a href="<?php echo BASE_URL; ?>/terms" target="_blank">Terms of Service</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-lock me-2"></i>Complete Order
                            </button>
                            
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your payment information is secure
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
// Switch hidden field when switching between new/existing customer
document.querySelectorAll('[data-bs-target="#existing-account"]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('input[name="existing_client"]').value = '1';
    });
});

document.querySelectorAll('[data-bs-target="#new-account"]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('input[name="existing_client"]').value = '0';
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
