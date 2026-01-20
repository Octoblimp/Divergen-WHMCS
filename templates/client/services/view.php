<?php
$page = 'services';
$title = $service['product_name'] ?? 'Service Details';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-cube me-2"></i>
        <?php echo htmlspecialchars($service['product_name'] ?? 'Service'); ?>
    </h2>
    <a href="<?php echo CLIENT_URL; ?>/services" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Services
    </a>
</div>

<div class="row g-4">
    <!-- Service Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Service Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td class="text-end">
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
                    </tr>
                    <?php if ($service['domain']): ?>
                    <tr>
                        <td class="text-muted">Domain</td>
                        <td class="text-end"><?php echo htmlspecialchars($service['domain']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Billing Cycle</td>
                        <td class="text-end"><?php echo ucfirst($service['billing_cycle'] ?? 'Monthly'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount</td>
                        <td class="text-end fw-bold">$<?php echo number_format($service['amount'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Registration Date</td>
                        <td class="text-end"><?php echo date('M d, Y', strtotime($service['created_at'])); ?></td>
                    </tr>
                    <?php if ($service['next_due']): ?>
                    <tr>
                        <td class="text-muted">Next Due Date</td>
                        <td class="text-end"><?php echo date('M d, Y', strtotime($service['next_due'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($service['status'] === 'active' && $server): ?>
                    <a href="#" class="btn btn-primary" id="loginToPanel">
                        <i class="fas fa-external-link-alt me-2"></i>Login to Control Panel
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo CLIENT_URL; ?>/tickets/create?service_id=<?php echo $service['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-headset me-2"></i>Get Support
                    </a>
                    
                    <?php if ($service['status'] === 'active'): ?>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-times me-2"></i>Request Cancellation
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Server Details -->
    <div class="col-lg-8">
        <?php if ($service['status'] === 'active' && $server): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-server me-2"></i>Server Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Server Name</label>
                        <div class="form-control-plaintext"><?php echo htmlspecialchars($server['name']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Server IP</label>
                        <div class="form-control-plaintext"><?php echo htmlspecialchars($server['ip_address']); ?></div>
                    </div>
                    <?php if ($service['username']): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Username</label>
                        <div class="form-control-plaintext">
                            <code><?php echo htmlspecialchars($service['username']); ?></code>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Hosting Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-chart-bar me-2"></i>Usage Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label d-flex justify-content-between">
                            <span>Disk Space</span>
                            <span class="text-muted">25% used</span>
                        </label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 25%"></div>
                        </div>
                        <small class="text-muted">250 MB of 1 GB</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-flex justify-content-between">
                            <span>Bandwidth</span>
                            <span class="text-muted">10% used</span>
                        </label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 10%"></div>
                        </div>
                        <small class="text-muted">1 GB of 10 GB</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($service['status'] === 'suspended'): ?>
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Service Suspended</h5>
            <p class="mb-0">
                This service has been suspended. 
                <?php if ($service['suspend_reason']): ?>
                    Reason: <?php echo htmlspecialchars($service['suspend_reason']); ?>
                <?php endif; ?>
            </p>
            <p class="mb-0 mt-2">Please <a href="<?php echo CLIENT_URL; ?>/tickets/create">contact support</a> or pay any outstanding invoices to reactivate.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($service['status'] === 'pending'): ?>
        <div class="alert alert-warning">
            <h5><i class="fas fa-clock me-2"></i>Pending Activation</h5>
            <p class="mb-0">This service is pending activation. Once your payment is confirmed, the service will be set up automatically.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo CLIENT_URL; ?>/services/<?php echo $service['id']; ?>/cancel">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Request Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Type</label>
                        <select class="form-select" name="type" required>
                            <option value="end_of_billing">At End of Billing Period</option>
                            <option value="immediate">Immediate</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" name="reason" rows="4" placeholder="Please let us know why you're cancelling..."></textarea>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cancellation requests are reviewed by our team. You will receive confirmation once processed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
