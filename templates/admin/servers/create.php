<?php
$page = 'servers';
$title = 'Add Server';
ob_start();
?>

<!-- Page Header -->
<div class="page-header mb-4">
    <h1><i class="fas fa-server me-2"></i>Add New Server</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Server Configuration</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo ADMIN_URL; ?>/servers/add">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Server Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g., Production Server 1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Server Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="hestiacp">HestiaCP</option>
                            <option value="cpanel">cPanel (Coming Soon)</option>
                            <option value="plesk">Plesk (Coming Soon)</option>
                        </select>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Connection Details</h6>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Hostname <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="hostname" required placeholder="server.example.com or IP address">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="port" value="8083" required>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3">API Credentials</h6>
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> Use API Key authentication instead of admin credentials for better security.
                        Generate an API key in your HestiaCP admin panel under "API".
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="api_key" required placeholder="Your API key">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">API Secret <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="api_secret" required placeholder="Your API secret">
                    </div>
                    
                    <h6 class="mt-4 mb-3">Capacity</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Accounts (0 for unlimited)</label>
                        <input type="number" class="form-control" name="max_accounts" value="0" min="0">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="active" id="active" checked>
                        <label class="form-check-label" for="active">
                            Active (Server is available for provisioning)
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm bg-light">
            <div class="card-header">
                <h6 class="mb-0">Getting Started</h6>
            </div>
            <div class="card-body small">
                <h6 class="fw-bold mt-3">Generate API Key in HestiaCP</h6>
                <ol class="ps-3 mb-3">
                    <li>Log in to HestiaCP admin panel</li>
                    <li>Go to Settings → API</li>
                    <li>Click "Generate Key"</li>
                    <li>Copy the Key and Secret</li>
                    <li>Paste them here</li>
                </ol>
                
                <h6 class="fw-bold mt-3">API Benefits</h6>
                <ul class="ps-3">
                    <li>✓ More secure than admin credentials</li>
                    <li>✓ Can be revoked independently</li>
                    <li>✓ Limited scope access</li>
                    <li>✓ Audit trail support</li>
                </ul>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <small><i class="fas fa-lock me-1"></i> Keep your API secret secure. Don't share it.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
