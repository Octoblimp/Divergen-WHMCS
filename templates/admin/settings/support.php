<?php
/**
 * Admin Support Settings Page
 */
$this->layout = 'admin.layouts.main';
$title = 'Support Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-headset text-info me-2"></i> Support Settings</h1>
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
            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/settings/payment">
                <i class="fas fa-credit-card me-2"></i> Payment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/settings/support">
                <i class="fas fa-headset me-2"></i> Support
            </a>
        </li>
    </ul>
</div>

<?php $this->flashMessages(); ?>

<form method="POST" action="<?php echo ADMIN_URL; ?>/settings/support">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <!-- Main Settings -->
        <div class="col-lg-8">
            <!-- Ticket Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-ticket-alt me-2"></i> Ticket Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Department</label>
                            <select name="default_department" class="form-select">
                                <?php if ($departments): ?>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($settings['default_department'] ?? 1) == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name'] ?? ''); ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ticket Number Prefix</label>
                            <input type="text" name="ticket_prefix" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['ticket_prefix'] ?? 'TKT-'); ?>" required>
                            <small class="form-text text-muted">e.g., TKT-0001</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Priority</label>
                            <select name="default_priority" class="form-select">
                                <option value="low" <?php echo ($settings['default_priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo ($settings['default_priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo ($settings['default_priority'] ?? 'medium') === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto-Close Tickets After (Days)</label>
                            <input type="number" name="ticket_auto_close_days" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['ticket_auto_close_days'] ?? '7'); ?>" min="0">
                            <small class="form-text text-muted">Days of inactivity before auto-close (0 to disable)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Permissions & Features -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-lock me-2"></i> Permissions & Features
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="require_login_tickets" name="require_login_tickets" 
                               value="1" <?php echo ($settings['require_login_tickets'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="require_login_tickets">
                            <strong>Require Login to Open Tickets</strong>
                        </label>
                        <div class="text-muted small">Users must be logged in to create support tickets</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="ticket_rating_enabled" name="ticket_rating_enabled" 
                               value="1" <?php echo ($settings['ticket_rating_enabled'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ticket_rating_enabled">
                            <strong>Enable Ticket Rating/Feedback</strong>
                        </label>
                        <div class="text-muted small">Allow clients to rate support quality</div>
                    </div>
                </div>
            </div>

            <!-- Auto-Response Settings -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-robot me-2"></i> Auto-Response Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_response_enabled" name="auto_response_enabled" 
                               value="1" <?php echo ($settings['auto_response_enabled'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="auto_response_enabled">
                            <strong>Enable Auto-Response</strong>
                        </label>
                        <div class="text-muted small">Send automatic response when ticket is opened</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Auto-Response Message</label>
                        <textarea name="auto_response_message" class="form-control" rows="4" placeholder="Thank you for contacting us. We will respond to your ticket as soon as possible..."><?php echo htmlspecialchars($settings['auto_response_message'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">This message is sent automatically to new tickets</small>
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

            <!-- Configuration Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Current Configuration</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Ticket Prefix:</span>
                            <strong><?php echo htmlspecialchars($settings['ticket_prefix'] ?? 'TKT-'); ?></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Default Priority:</span>
                            <strong class="text-capitalize"><?php echo htmlspecialchars($settings['default_priority'] ?? 'medium'); ?></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Auto-Close Days:</span>
                            <strong><?php echo htmlspecialchars($settings['ticket_auto_close_days'] ?? '7'); ?></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Login Required:</span>
                            <strong>
                                <i class="fas fa-<?php echo ($settings['require_login_tickets'] ?? 1) ? 'check text-success' : 'times text-danger'; ?>"></i>
                            </strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Auto-Response:</span>
                            <strong>
                                <i class="fas fa-<?php echo ($settings['auto_response_enabled'] ?? 0) ? 'check text-success' : 'times text-danger'; ?>"></i>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Support Tips</h5>
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-chevron-right text-info me-2"></i> Keep prefix consistent</li>
                        <li class="mb-2"><i class="fas fa-chevron-right text-info me-2"></i> Auto-close helps cleanup</li>
                        <li class="mb-2"><i class="fas fa-chevron-right text-info me-2"></i> Rating improves service</li>
                        <li><i class="fas fa-chevron-right text-info me-2"></i> Auto-response sets expectations</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
