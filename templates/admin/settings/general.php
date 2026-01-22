<?php
/**
 * Admin General Settings Page
 */
$this->layout = 'admin.layouts.main';
$title = 'General Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-building text-primary me-2"></i> General Settings</h1>
</div>

<!-- Settings Navigation Tabs -->
<div class="mb-4">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/settings">
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
            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/settings/support">
                <i class="fas fa-headset me-2"></i> Support
            </a>
        </li>
    </ul>
</div>

<?php $this->flashMessages(); ?>

<form method="POST" action="<?php echo ADMIN_URL; ?>/settings">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <!-- Main Settings -->
        <div class="col-lg-8">
            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-building me-2"></i> Company Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="settings[company_name]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_name'] ?? APP_NAME); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Email</label>
                            <input type="email" name="settings[company_email]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Company Address</label>
                            <textarea name="settings[company_address]" class="form-control" rows="3" placeholder="123 Main St, City, State 12345"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="settings[company_phone]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo URL</label>
                            <input type="url" name="settings[logo_url]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>"
                                   placeholder="https://example.com/logo.png">
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-cogs me-2"></i> System Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">System URL</label>
                            <input type="url" name="settings[system_url]" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['system_url'] ?? APP_URL); ?>" required>
                            <small class="form-text text-muted">Used in emails and redirects</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="settings[timezone]" class="form-select">
                                <?php foreach (timezone_identifiers_list() as $tz): ?>
                                <option value="<?php echo $tz; ?>" <?php echo ($settings['timezone'] ?? 'UTC') === $tz ? 'selected' : ''; ?>>
                                    <?php echo $tz; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Format</label>
                            <select name="settings[date_format]" class="form-select">
                                <option value="Y-m-d" <?php echo ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : ''; ?>>2024-01-15</option>
                                <option value="d/m/Y" <?php echo ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : ''; ?>>15/01/2024</option>
                                <option value="m/d/Y" <?php echo ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : ''; ?>>01/15/2024</option>
                                <option value="F j, Y" <?php echo ($settings['date_format'] ?? '') === 'F j, Y' ? 'selected' : ''; ?>>January 15, 2024</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Mode</label>
                            <select name="settings[maintenance_mode]" class="form-select">
                                <option value="0" <?php echo empty($settings['maintenance_mode']) ? 'selected' : ''; ?>>Disabled</option>
                                <option value="1" <?php echo ($settings['maintenance_mode'] ?? 0) ? 'selected' : ''; ?>>Enabled</option>
                            </select>
                            <small class="form-text text-muted">Show maintenance message to clients</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Settings -->
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/dashboard" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Configuration Status</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Company Info</span>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i> OK
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>System URL</span>
                            <span class="badge <?php echo !empty($settings['system_url']) ? 'bg-success' : 'bg-warning'; ?>">
                                <i class="fas fa-<?php echo !empty($settings['system_url']) ? 'check' : 'exclamation'; ?> me-1"></i> <?php echo !empty($settings['system_url']) ? 'OK' : 'MISSING'; ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Timezone</span>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i> OK
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Tips</h5>
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-chevron-right text-primary me-2"></i> Keep your company name consistent</li>
                        <li class="mb-2"><i class="fas fa-chevron-right text-primary me-2"></i> System URL must match your domain</li>
                        <li class="mb-2"><i class="fas fa-chevron-right text-primary me-2"></i> Timezone affects invoice dates</li>
                        <li><i class="fas fa-chevron-right text-primary me-2"></i> Use maintenance mode for updates</li>
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
