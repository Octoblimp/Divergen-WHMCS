<?php
/**
 * Admin Email Settings Page
 */
$this->layout = 'admin.layouts.main';
$title = 'Email Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-envelope text-primary me-2"></i> Email Settings</h1>
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
            <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/settings/email">
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

<form method="POST" action="<?php echo ADMIN_URL; ?>/settings/email">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Global Settings -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white"><i class="fas fa-cog me-2"></i> Global Email Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="mail_enabled" name="mail_enabled" 
                               <?php echo ($settings['mail_enabled'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="mail_enabled">
                            <strong>Enable Email Sending</strong>
                        </label>
                        <div class="text-muted small">Turn off to disable ALL outgoing emails</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Disabling emails will prevent invoice notifications, order confirmations, and support ticket replies from being sent.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">From Email Address</label>
                                <input type="email" class="form-control" name="mail_from_email" 
                                       value="<?php echo htmlspecialchars($settings['mail_from_email'] ?? ''); ?>" 
                                       placeholder="noreply@yourdomain.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" name="mail_from_name" 
                                       value="<?php echo htmlspecialchars($settings['mail_from_name'] ?? COMPANY_NAME); ?>" 
                                       placeholder="Company Name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Sending Method</label>
                        <select class="form-select" name="mail_method" id="mailMethod">
                            <option value="mail" <?php echo ($settings['mail_method'] ?? '') === 'mail' ? 'selected' : ''; ?>>PHP mail() Function</option>
                            <option value="smtp" <?php echo ($settings['mail_method'] ?? '') === 'smtp' ? 'selected' : ''; ?>>SMTP Server</option>
                            <option value="sendgrid" <?php echo ($settings['mail_method'] ?? '') === 'sendgrid' ? 'selected' : ''; ?>>SendGrid API</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- SMTP Settings -->
            <div class="card mb-4" id="smtpSettings" style="<?php echo ($settings['mail_method'] ?? '') !== 'smtp' ? 'display:none;' : ''; ?>">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0 text-white"><i class="fas fa-server me-2"></i> SMTP Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" name="mail_smtp_host" 
                                       value="<?php echo htmlspecialchars($settings['mail_smtp_host'] ?? ''); ?>" 
                                       placeholder="smtp.example.com">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" class="form-control" name="mail_smtp_port" 
                                       value="<?php echo htmlspecialchars($settings['mail_smtp_port'] ?? '587'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" name="mail_smtp_user" 
                                       value="<?php echo htmlspecialchars($settings['mail_smtp_user'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" name="mail_smtp_pass" 
                                       value="<?php echo htmlspecialchars($settings['mail_smtp_pass'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Encryption</label>
                        <select class="form-select" name="mail_smtp_secure">
                            <option value="tls" <?php echo ($settings['mail_smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['mail_smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="" <?php echo empty($settings['mail_smtp_secure']) ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary" onclick="testSMTP()">
                        <i class="fas fa-paper-plane me-1"></i> Test SMTP Connection
                    </button>
                </div>
            </div>
            
            <!-- SendGrid Settings -->
            <div class="card mb-4" id="sendgridSettings" style="<?php echo ($settings['mail_method'] ?? '') !== 'sendgrid' ? 'display:none;' : ''; ?>">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <h5 class="mb-0 text-white"><i class="fas fa-cloud me-2"></i> SendGrid Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">SendGrid API Key</label>
                        <input type="password" class="form-control" name="mail_sendgrid_api_key" 
                               value="<?php echo htmlspecialchars($settings['mail_sendgrid_api_key'] ?? ''); ?>">
                        <div class="text-muted small">
                            Get your API key from <a href="https://sendgrid.com/" target="_blank">sendgrid.com</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Disable Individual Email Types -->
            <div class="card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <h5 class="mb-0 text-white"><i class="fas fa-ban me-2"></i> Disable Email Types</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Selectively disable specific types of emails:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_invoice" name="mail_disable_invoice" 
                                       <?php echo ($settings['mail_disable_invoice'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_invoice">
                                    <i class="fas fa-file-invoice text-warning me-2"></i> Invoice Emails
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_order" name="mail_disable_order" 
                                       <?php echo ($settings['mail_disable_order'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_order">
                                    <i class="fas fa-shopping-cart text-primary me-2"></i> Order Confirmation Emails
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_support" name="mail_disable_support" 
                                       <?php echo ($settings['mail_disable_support'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_support">
                                    <i class="fas fa-ticket-alt text-info me-2"></i> Support Ticket Emails
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_service" name="mail_disable_service" 
                                       <?php echo ($settings['mail_disable_service'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_service">
                                    <i class="fas fa-server text-success me-2"></i> Service Notification Emails
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_domain" name="mail_disable_domain" 
                                       <?php echo ($settings['mail_disable_domain'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_domain">
                                    <i class="fas fa-globe text-danger me-2"></i> Domain Notification Emails
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="mail_disable_general" name="mail_disable_general" 
                                       <?php echo ($settings['mail_disable_general'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mail_disable_general">
                                    <i class="fas fa-envelope text-secondary me-2"></i> General/Other Emails
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Options -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i> Additional Options</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="mail_log_emails" name="mail_log_emails" 
                               <?php echo ($settings['mail_log_emails'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="mail_log_emails">
                            Log all sent emails (for debugging)
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="mail_cc_admin" name="mail_cc_admin" 
                               <?php echo ($settings['mail_cc_admin'] ?? false) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="mail_cc_admin">
                            CC admin on all client emails
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="mail_bcc_admin" name="mail_bcc_admin" 
                               <?php echo ($settings['mail_bcc_admin'] ?? false) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="mail_bcc_admin">
                            BCC admin on all client emails
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                    
                    <a href="<?php echo ADMIN_URL; ?>/settings" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i> Send Test Email</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="email" class="form-control" id="testEmailRecipient" 
                               value="<?php echo htmlspecialchars($_SESSION['admin']['email'] ?? ''); ?>">
                    </div>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="sendTestEmail()">
                        <i class="fas fa-paper-plane me-1"></i> Send Test Email
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Tips</h5>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>SMTP is more reliable than PHP mail()</li>
                        <li>TLS on port 587 is recommended</li>
                        <li>SendGrid is good for high volume</li>
                        <li>Enable logging during setup to debug issues</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('mailMethod').addEventListener('change', function() {
    document.getElementById('smtpSettings').style.display = this.value === 'smtp' ? 'block' : 'none';
    document.getElementById('sendgridSettings').style.display = this.value === 'sendgrid' ? 'block' : 'none';
});

function testSMTP() {
    alert('SMTP test would run here. Check your email for a test message.');
}

function sendTestEmail() {
    const email = document.getElementById('testEmailRecipient').value;
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    fetch('<?php echo ADMIN_URL; ?>/settings/email/test', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email: email})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Test email sent successfully!');
        } else {
            alert('Failed to send: ' + (data.error || 'Unknown error'));
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
