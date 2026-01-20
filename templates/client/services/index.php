<?php
$page = 'services';
$title = 'My Services';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cube me-2"></i>My Services</h2>
    <a href="<?php echo BASE_URL; ?>/order" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Order New Service
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product/Service</th>
                        <th>Domain</th>
                        <th>Billing Cycle</th>
                        <th>Amount</th>
                        <th>Next Due</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Services Found</h5>
                                <p class="text-muted mb-3">You don't have any services yet.</p>
                                <a href="<?php echo BASE_URL; ?>/order" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Order Your First Service
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($service['product_name'] ?? 'Service'); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($service['domain'] ?: '-'); ?></td>
                            <td><?php echo ucfirst($service['billing_cycle'] ?? 'Monthly'); ?></td>
                            <td>$<?php echo number_format($service['amount'] ?? 0, 2); ?></td>
                            <td>
                                <?php if ($service['next_due']): ?>
                                    <?php 
                                    $nextDue = strtotime($service['next_due']);
                                    $daysUntil = floor(($nextDue - time()) / 86400);
                                    ?>
                                    <span class="<?php echo $daysUntil < 7 ? 'text-warning fw-bold' : ''; ?>">
                                        <?php echo date('M d, Y', $nextDue); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $colors = [
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'suspended' => 'danger',
                                    'terminated' => 'dark',
                                    'cancelled' => 'secondary'
                                ];
                                $color = $colors[$service['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($service['status']); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo CLIENT_URL; ?>/services/<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-cog me-1"></i>Manage
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
