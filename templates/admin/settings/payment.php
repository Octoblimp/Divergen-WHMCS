<?php
/**
 * Admin Payment Settings Page
 */
$this->layout = 'admin.layouts.main';
$title = 'Payment Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-credit-card text-success me-2"></i> Payment Settings</h1>
</div>

<!-- Settings Navigation Tabs -->
<div class="mb-4">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/settings">
                <i class="fas fa-building me-2"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/settings/email">
                <i class="fas fa-envelope me-2"></i> Email
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/settings/payment">
                <i class="fas fa-credit-card me-2"></i> Payment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/settings/support">
                <i class="fas fa-headset me-2"></i> Support
            </a>
        </li>
    </ul>
</div>

<?php $this->flashMessages(); ?>

<form method="POST" action="<?php echo ADMIN_URL; ?>/settings/payment">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <!-- Main Settings -->
        <div class="col-lg-8">
            <!-- Billing Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Billing Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency Code</label>
                            <input type="text" name="settings[currency_code]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?>" maxlength="3" required>
                            <small class="form-text text-muted">ISO 4217 (USD, EUR, GBP, etc.)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="settings[currency_symbol]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>" maxlength="5" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" name="settings[invoice_prefix]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-'); ?>">
                            <small class="form-text text-muted">e.g., INV-0001</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Terms (Days)</label>
                            <input type="number" name="settings[payment_terms]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['payment_terms'] ?? '7'); ?>" min="0" required>
                            <small class="form-text text-muted">Due date offset from invoice date</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Generate Invoices (Days Before)</label>
                            <input type="number" name="settings[invoice_days_before]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['invoice_days_before'] ?? '14'); ?>" min="0" required>
                            <small class="form-text text-muted">How early to create invoices</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-receipt me-2"></i> Tax Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="settings[tax_rate]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="settings[tax_type]" class="form-select">
                                <option value="none" <?php echo ($settings['tax_type'] ?? 'none') === 'none' ? 'selected' : ''; ?>>No Tax</option>
                                <option value="inclusive" <?php echo ($settings['tax_type'] ?? '') === 'inclusive' ? 'selected' : ''; ?>>Tax Inclusive</option>
                                <option value="exclusive" <?php echo ($settings['tax_type'] ?? '') === 'exclusive' ? 'selected' : ''; ?>>Tax Exclusive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Late Fee (%)</label>
                            <input type="number" step="0.01" name="settings[late_fee_percent]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['late_fee_percent'] ?? '0'); ?>" min="0" max="100">
                            <small class="form-text text-muted">Added to overdue invoices</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Footer/Notes</label>
                        <textarea name="settings[invoice_notes]" class="form-control" rows="3" placeholder="Payment terms, thank you message, etc."><?php echo htmlspecialchars($settings['invoice_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Automated Actions -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-bolt me-2"></i> Automated Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_cancel_unpaid" name="settings[auto_cancel_unpaid]" 
                               value="1" <?php echo ($settings['auto_cancel_unpaid'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="auto_cancel_unpaid">
                            <strong>Auto-Cancel Unpaid Orders</strong>
                        </label>
                        <div class="text-muted small">Automatically cancel orders if payment not received</div>
                    </div>

                    <?php if ($settings['auto_cancel_unpaid'] ?? 0): ?>
                    <div class="ms-4 mb-3">
                        <label class="form-label">Cancel After (Days)</label>
                        <input type="number" name="settings[auto_cancel_days]" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['auto_cancel_days'] ?? '30'); ?>" min="1">
                    </div>
                    <?php endif; ?>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_suspend_services" name="settings[auto_suspend_services]" 
                               value="1" <?php echo ($settings['auto_suspend_services'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="auto_suspend_services">
                            <strong>Auto-Suspend Services on Overdue Payment</strong>
                        </label>
                        <div class="text-muted small">Suspend active services when invoice becomes overdue</div>
                    </div>

                    <?php if ($settings['auto_suspend_services'] ?? 0): ?>
                    <div class="ms-4 mb-3">
                        <label class="form-label">Suspend After (Days Overdue)</label>
                        <input type="number" name="settings[auto_suspend_days]" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['auto_suspend_days'] ?? '14'); ?>" min="1">
                    </div>
                    <?php endif; ?>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="require_invoice_signing" name="settings[require_invoice_signing]" 
                               value="1" <?php echo ($settings['require_invoice_signing'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="require_invoice_signing">
                            <strong>Require Invoice Acknowledgment</strong>
                        </label>
                        <div class="text-muted small">Clients must acknowledge invoices before proceeding</div>
                    </div>
                </div>
            </div>

            <!-- Payment Reminders -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Payment Reminders</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Send Reminder After (Days)</label>
                        <input type="number" name="settings[payment_reminder_days]" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['payment_reminder_days'] ?? '7'); ?>" min="0">
                        <small class="form-text text-muted">Days after due date to send payment reminder (0 to disable)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/settings" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- Active Payment Gateways -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Payment Gateways</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($gateways): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($gateways as $gateway): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($gateway['name'] ?? ''); ?></strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars($gateway['type'] ?? ''); ?></div>
                                </div>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Active
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            <p class="mb-0">No payment gateways configured</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Configuration</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Currency:</span>
                            <strong><?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Payment Terms:</span>
                            <strong><?php echo htmlspecialchars($settings['payment_terms'] ?? '7'); ?> days</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Tax Rate:</span>
                            <strong><?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>%</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Late Fee:</span>
                            <strong><?php echo htmlspecialchars($settings['late_fee_percent'] ?? '0'); ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
