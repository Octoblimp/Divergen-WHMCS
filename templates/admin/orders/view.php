<?php
$page = 'orders';
$title = 'View Order #' . $order['order_number'];
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-shopping-cart me-2"></i>Order #<?php echo htmlspecialchars($order['order_number']); ?>
        <?php
        $statusColors = [
            'pending' => 'warning',
            'active' => 'success',
            'fraud' => 'danger',
            'cancelled' => 'secondary'
        ];
        $color = $statusColors[$order['status']] ?? 'secondary';
        ?>
        <span class="badge bg-<?php echo $color; ?> ms-2"><?php echo ucfirst($order['status']); ?></span>
    </h1>
    <a href="<?php echo ADMIN_URL; ?>/orders" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<div class="row g-4">
    <!-- Order Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Order Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Order Number</td>
                        <td class="text-end fw-bold">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Order Date</td>
                        <td class="text-end"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Method</td>
                        <td class="text-end"><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Invoice</td>
                        <td class="text-end">
                            <?php if ($order['invoice_id']): ?>
                                <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $order['invoice_id']; ?>">
                                    View Invoice
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total</td>
                        <td class="text-end fw-bold text-primary">$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address</td>
                        <td class="text-end"><?php echo htmlspecialchars($order['ip_address'] ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Client Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-user me-2"></i>Client Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1">
                    <strong>
                        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $order['client_id']; ?>">
                            <?php echo htmlspecialchars($order['client_name'] ?? 'N/A'); ?>
                        </a>
                    </strong>
                </p>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($order['client_email'] ?? ''); ?></p>
            </div>
        </div>
        
        <!-- Actions -->
        <?php if ($order['status'] === 'pending'): ?>
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>/accept?csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Accept Order
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                        <i class="fas fa-times me-2"></i>Cancel Order
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#fraudOrderModal">
                        <i class="fas fa-exclamation-triangle me-2"></i>Mark as Fraud
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Items -->
    <div class="col-lg-8">
        <!-- Services -->
        <?php if (!empty($services)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-cube me-2"></i>Services</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Domain</th>
                                <th>Billing Cycle</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['product_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($service['domain'] ?: '-'); ?></td>
                                <td><?php echo ucfirst($service['billing_cycle'] ?? 'monthly'); ?></td>
                                <td>
                                    <?php
                                    $svcColors = [
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'suspended' => 'danger',
                                        'terminated' => 'dark',
                                        'cancelled' => 'secondary'
                                    ];
                                    $svcColor = $svcColors[$service['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $svcColor; ?>"><?php echo ucfirst($service['status']); ?></span>
                                </td>
                                <td>$<?php echo number_format($service['amount'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Domains -->
        <?php if (!empty($domains)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-globe me-2"></i>Domains</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domains as $domain): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($domain['domain']); ?></td>
                                <td><?php echo ucfirst($domain['registration_type'] ?? 'register'); ?></td>
                                <td><?php echo $domain['registration_period'] ?? 1; ?> year(s)</td>
                                <td>
                                    <?php
                                    $domColors = [
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'expired' => 'danger',
                                        'transferred' => 'info'
                                    ];
                                    $domColor = $domColors[$domain['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $domColor; ?>"><?php echo ucfirst($domain['status']); ?></span>
                                </td>
                                <td>$<?php echo number_format($domain['amount'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Notes -->
        <?php if ($order['notes']): ?>
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-sticky-note me-2"></i>Order Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>/cancel">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
