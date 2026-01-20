<?php
/**
 * OpenWHM Admin - Settings Management
 */

use OpenWHM\Database;
use OpenWHM\Settings;
use OpenWHM\Helper;

$db = Database::getInstance();
$tab = $_GET['tab'] ?? 'general';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $settings = $_POST['settings'] ?? [];
    
    foreach ($settings as $key => $value) {
        // Check if setting exists
        $existing = $db->fetch(
            "SELECT id FROM {$db->table('settings')} WHERE `key` = ?",
            [$key]
        );
        
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('settings', [
                'key' => $key,
                'value' => $value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Clear settings cache
    Settings::clearCache();
    
    setFlash('success', 'Settings saved successfully');
    redirect('index.php?page=settings&tab=' . $tab);
}

// Get all settings
$allSettings = [];
$rows = $db->fetchAll("SELECT * FROM {$db->table('settings')}");
foreach ($rows as $row) {
    $allSettings[$row['key']] = $row['value'];
}

// Helper function
function getSetting($key, $default = '') {
    global $allSettings;
    return $allSettings[$key] ?? $default;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="fas fa-cogs me-2"></i> Settings</h1>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="list-group list-group-flush">
                <a href="index.php?page=settings&tab=general" class="list-group-item list-group-item-action <?= $tab === 'general' ? 'active' : '' ?>">
                    <i class="fas fa-building me-2"></i> General
                </a>
                <a href="index.php?page=settings&tab=billing" class="list-group-item list-group-item-action <?= $tab === 'billing' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice me-2"></i> Billing
                </a>
                <a href="index.php?page=settings&tab=support" class="list-group-item list-group-item-action <?= $tab === 'support' ? 'active' : '' ?>">
                    <i class="fas fa-life-ring me-2"></i> Support
                </a>
                <a href="index.php?page=settings&tab=automation" class="list-group-item list-group-item-action <?= $tab === 'automation' ? 'active' : '' ?>">
                    <i class="fas fa-robot me-2"></i> Automation
                </a>
                <a href="index.php?page=settings&tab=mail" class="list-group-item list-group-item-action <?= $tab === 'mail' ? 'active' : '' ?>">
                    <i class="fas fa-envelope me-2"></i> Mail
                </a>
                <a href="index.php?page=settings&tab=security" class="list-group-item list-group-item-action <?= $tab === 'security' ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt me-2"></i> Security
                </a>
                <a href="index.php?page=settings&tab=social" class="list-group-item list-group-item-action <?= $tab === 'social' ? 'active' : '' ?>">
                    <i class="fas fa-share-alt me-2"></i> Social Links
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <?= csrfField() ?>
                    
                    <?php if ($tab === 'general'): ?>
                    <h5 class="mb-4">General Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="settings[company_name]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('company_name', APP_NAME)) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Email</label>
                            <input type="email" name="settings[company_email]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('company_email')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">System URL</label>
                            <input type="url" name="settings[system_url]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('system_url', APP_URL)) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo URL</label>
                            <input type="text" name="settings[logo_url]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('logo_url')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="settings[company_phone]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('company_phone')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="settings[timezone]" class="form-select">
                                <?php foreach (timezone_identifiers_list() as $tz): ?>
                                <option value="<?= $tz ?>" <?= getSetting('timezone', 'UTC') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Company Address</label>
                            <textarea name="settings[company_address]" class="form-control" rows="3"><?= htmlspecialchars(getSetting('company_address')) ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Format</label>
                            <select name="settings[date_format]" class="form-select">
                                <option value="Y-m-d" <?= getSetting('date_format') === 'Y-m-d' ? 'selected' : '' ?>>2024-01-15</option>
                                <option value="d/m/Y" <?= getSetting('date_format') === 'd/m/Y' ? 'selected' : '' ?>>15/01/2024</option>
                                <option value="m/d/Y" <?= getSetting('date_format') === 'm/d/Y' ? 'selected' : '' ?>>01/15/2024</option>
                                <option value="F j, Y" <?= getSetting('date_format') === 'F j, Y' ? 'selected' : '' ?>>January 15, 2024</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maintenance Mode</label>
                            <select name="settings[maintenance_mode]" class="form-select">
                                <option value="0" <?= !getSetting('maintenance_mode') ? 'selected' : '' ?>>Disabled</option>
                                <option value="1" <?= getSetting('maintenance_mode') ? 'selected' : '' ?>>Enabled</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php elseif ($tab === 'billing'): ?>
                    <h5 class="mb-4">Billing Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency Code</label>
                            <input type="text" name="settings[currency_code]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('currency_code', 'USD')) ?>" maxlength="3">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="settings[currency_symbol]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('currency_symbol', '$')) ?>" maxlength="5">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Invoice Number Prefix</label>
                            <input type="text" name="settings[invoice_prefix]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('invoice_prefix', 'INV-')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Generate Invoices (Days Before Due)</label>
                            <input type="number" name="settings[invoice_days_before]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('invoice_days_before', '14')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Terms (Days)</label>
                            <input type="number" name="settings[payment_terms]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('payment_terms', '7')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="settings[tax_rate]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('tax_rate', '0')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="settings[tax_type]" class="form-select">
                                <option value="none" <?= getSetting('tax_type') === 'none' ? 'selected' : '' ?>>No Tax</option>
                                <option value="inclusive" <?= getSetting('tax_type') === 'inclusive' ? 'selected' : '' ?>>Tax Inclusive</option>
                                <option value="exclusive" <?= getSetting('tax_type') === 'exclusive' ? 'selected' : '' ?>>Tax Exclusive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Late Fee (%)</label>
                            <input type="number" step="0.01" name="settings[late_fee_percent]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('late_fee_percent', '0')) ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Invoice Footer/Notes</label>
                            <textarea name="settings[invoice_notes]" class="form-control" rows="3"><?= htmlspecialchars(getSetting('invoice_notes')) ?></textarea>
                        </div>
                    </div>
                    
                    <?php elseif ($tab === 'support'): ?>
                    <h5 class="mb-4">Support Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Department</label>
                            <select name="settings[default_department]" class="form-select">
                                <?php 
                                $departments = $db->fetchAll("SELECT * FROM {$db->table('departments')} ORDER BY name");
                                foreach ($departments as $dept): 
                                ?>
                                <option value="<?= $dept['id'] ?>" <?= getSetting('default_department') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto-Close Tickets After (Days)</label>
                            <input type="number" name="settings[ticket_auto_close_days]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('ticket_auto_close_days', '7')) ?>">
                            <div class="form-text">Close answered tickets with no response after X days</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ticket Number Prefix</label>
                            <input type="text" name="settings[ticket_prefix]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('ticket_prefix', 'TKT-')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Ticket Priority</label>
                            <select name="settings[default_priority]" class="form-select">
                                <option value="low" <?= getSetting('default_priority') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= getSetting('default_priority', 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= getSetting('default_priority') === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="settings[require_login_tickets]" class="form-check-input" value="1"
                                       <?= getSetting('require_login_tickets', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Require login to open tickets</label>
                            </div>
                        </div>
                    </div>
                    
                    <?php elseif ($tab === 'automation'): ?>
                    <h5 class="mb-4">Automation Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Suspend After Overdue (Days)</label>
                            <input type="number" name="settings[suspend_after_days]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('suspend_after_days', '7')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Terminate After Overdue (Days)</label>
                            <input type="number" name="settings[terminate_after_days]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('terminate_after_days', '30')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Overdue Grace Period (Days)</label>
                            <input type="number" name="settings[overdue_grace_period]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('overdue_grace_period', '3')) ?>">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[auto_suspend]" class="form-check-input" value="1"
                                       <?= getSetting('auto_suspend', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Auto-suspend overdue services</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[auto_terminate]" class="form-check-input" value="1"
                                       <?= getSetting('auto_terminate') ? 'checked' : '' ?>>
                                <label class="form-check-label">Auto-terminate suspended services</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[auto_provision]" class="form-check-input" value="1"
                                       <?= getSetting('auto_provision', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Auto-provision new services on payment</label>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Cron Job Status</h5>
                    <?php
                    $lastCron = $db->fetch("SELECT * FROM {$db->table('cron_log')} ORDER BY id DESC LIMIT 1");
                    ?>
                    <div class="alert alert-info">
                        <p class="mb-2"><strong>Cron Command:</strong></p>
                        <code>*/5 * * * * php <?= ROOT_PATH ?>/cron/cron.php</code>
                        <?php if ($lastCron): ?>
                        <hr>
                        <p class="mb-0">
                            <strong>Last Run:</strong> <?= Helper::formatDateTime($lastCron['created_at']) ?>
                            (<?= $lastCron['runtime'] ?>s)
                        </p>
                        <?php else: ?>
                        <hr>
                        <p class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Cron has never run</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php elseif ($tab === 'mail'): ?>
                    <h5 class="mb-4">Mail Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mail Type</label>
                            <select name="settings[mail_type]" class="form-select" id="mail_type">
                                <option value="php" <?= getSetting('mail_type', 'php') === 'php' ? 'selected' : '' ?>>PHP Mail</option>
                                <option value="smtp" <?= getSetting('mail_type') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Email</label>
                            <input type="email" name="settings[mail_from_email]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('mail_from_email', EMAIL_FROM)) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Name</label>
                            <input type="text" name="settings[mail_from_name]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('mail_from_name', EMAIL_FROM_NAME)) ?>">
                        </div>
                    </div>
                    
                    <div id="smtp_settings" style="<?= getSetting('mail_type') !== 'smtp' ? 'display:none' : '' ?>">
                        <hr>
                        <h6>SMTP Configuration</h6>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="settings[smtp_host]" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_host')) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" name="settings[smtp_port]" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_port', '587')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="settings[smtp_username]" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_username')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" name="settings[smtp_password]" class="form-control" 
                                       value="<?= htmlspecialchars(getSetting('smtp_password')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Encryption</label>
                                <select name="settings[smtp_encryption]" class="form-select">
                                    <option value="" <?= !getSetting('smtp_encryption') ? 'selected' : '' ?>>None</option>
                                    <option value="tls" <?= getSetting('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= getSetting('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    document.getElementById('mail_type').addEventListener('change', function() {
                        document.getElementById('smtp_settings').style.display = this.value === 'smtp' ? '' : 'none';
                    });
                    </script>
                    
                    <?php elseif ($tab === 'security'): ?>
                    <h5 class="mb-4">Security Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Timeout (Minutes)</label>
                            <input type="number" name="settings[session_timeout]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('session_timeout', '120')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Login Attempts</label>
                            <input type="number" name="settings[max_login_attempts]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('max_login_attempts', '5')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lockout Duration (Minutes)</label>
                            <input type="number" name="settings[lockout_duration]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('lockout_duration', '30')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Minimum Length</label>
                            <input type="number" name="settings[password_min_length]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('password_min_length', '8')) ?>">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[require_email_verification]" class="form-check-input" value="1"
                                       <?= getSetting('require_email_verification') ? 'checked' : '' ?>>
                                <label class="form-check-label">Require email verification for new accounts</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[enable_2fa]" class="form-check-input" value="1"
                                       <?= getSetting('enable_2fa') ? 'checked' : '' ?>>
                                <label class="form-check-label">Enable Two-Factor Authentication option</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="settings[log_client_activity]" class="form-check-input" value="1"
                                       <?= getSetting('log_client_activity', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Log client activity</label>
                            </div>
                        </div>
                    </div>
                    
                    <?php elseif ($tab === 'social'): ?>
                    <h5 class="mb-4">Social Links</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-facebook me-2"></i> Facebook</label>
                            <input type="url" name="settings[social_facebook]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_facebook')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-twitter me-2"></i> Twitter / X</label>
                            <input type="url" name="settings[social_twitter]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_twitter')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-linkedin me-2"></i> LinkedIn</label>
                            <input type="url" name="settings[social_linkedin]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_linkedin')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-instagram me-2"></i> Instagram</label>
                            <input type="url" name="settings[social_instagram]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_instagram')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-youtube me-2"></i> YouTube</label>
                            <input type="url" name="settings[social_youtube]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_youtube')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fab fa-discord me-2"></i> Discord</label>
                            <input type="url" name="settings[social_discord]" class="form-control" 
                                   value="<?= htmlspecialchars(getSetting('social_discord')) ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>