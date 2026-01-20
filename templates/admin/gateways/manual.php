<?php
/**
 * Admin Manual Payment Methods Settings
 */
$this->layout = 'admin.layouts.main';
$title = 'Manual Payment Methods';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-hand-holding-usd text-success me-2"></i> Manual Payment Methods</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
        <i class="fas fa-plus me-1"></i> Add Method
    </button>
</div>

<?php $this->flashMessages(); ?>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-4">
            Configure offline payment methods like Zelle, Wire Transfer, Check, etc. 
            Customers will see payment instructions after selecting a method.
        </p>
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50"></th>
                    <th>Method Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Order</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody id="methodsTable">
                <?php if (empty($methods)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No manual payment methods configured</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($methods as $method): ?>
                    <tr data-id="<?php echo $method['id']; ?>">
                        <td class="handle text-muted"><i class="fas fa-grip-vertical"></i></td>
                        <td>
                            <i class="<?php echo htmlspecialchars($method['icon'] ?? 'fas fa-money-bill'); ?> text-primary me-2"></i>
                            <strong><?php echo htmlspecialchars($method['name']); ?></strong>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo ucfirst($method['type']); ?></span></td>
                        <td>
                            <?php if ($method['active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-warning">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $method['sort_order']; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editMethod(<?php echo $method['id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-<?php echo $method['active'] ? 'warning' : 'success'; ?>" 
                                        onclick="toggleMethod(<?php echo $method['id']; ?>)" 
                                        title="<?php echo $method['active'] ? 'Disable' : 'Enable'; ?>">
                                    <i class="fas fa-<?php echo $method['active'] ? 'pause' : 'play'; ?>"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteMethod(<?php echo $method['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- General Settings -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">General Settings</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo ADMIN_URL; ?>/gateways/manual/settings">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Initial Invoice Status</label>
                        <select class="form-select" name="pending_status">
                            <option value="unpaid" <?php echo ($settings['pending_status'] ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                            <option value="payment_pending" <?php echo ($settings['pending_status'] ?? 'payment_pending') === 'payment_pending' ? 'selected' : ''; ?>>Payment Pending</option>
                        </select>
                        <div class="text-muted small">Status to set when customer selects manual payment</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Notifications</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmation_email" name="confirmation_email" 
                                   <?php echo ($settings['confirmation_email'] ?? true) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="confirmation_email">
                                Send payment instructions to customer
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="admin_notification" name="admin_notification" 
                                   <?php echo ($settings['admin_notification'] ?? true) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="admin_notification">
                                Notify admin when customer selects manual payment
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
        </form>
    </div>
</div>

<!-- Add/Edit Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="methodModalTitle">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="methodForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="method_id" id="method_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Method Name *</label>
                                <input type="text" class="form-control" name="name" id="method_name" required placeholder="e.g., Zelle, Wire Transfer">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" id="method_type">
                                    <option value="custom">Custom</option>
                                    <option value="zelle">Zelle</option>
                                    <option value="wire">Wire Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="cashapp">Cash App</option>
                                    <option value="crypto">Cryptocurrency</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Icon Class</label>
                        <div class="input-group">
                            <span class="input-group-text" id="iconPreview"><i class="fas fa-money-bill"></i></span>
                            <input type="text" class="form-control" name="icon" id="method_icon" value="fas fa-money-bill" placeholder="fas fa-money-bill">
                        </div>
                        <div class="text-muted small">Font Awesome class. <a href="https://fontawesome.com/icons" target="_blank">Browse icons</a></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Instructions *</label>
                        <textarea class="form-control" name="instructions" id="method_instructions" rows="8" required 
                                  placeholder="Enter instructions the customer will see..."></textarea>
                        <div class="text-muted small mt-1">
                            Available placeholders: <code>{{INVOICE_ID}}</code>, <code>{{AMOUNT}}</code>, <code>{{CLIENT_NAME}}</code>, <code>{{DUE_DATE}}</code>, <code>{{COMPANY_NAME}}</code>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Custom Fields</label>
                        <div id="customFields">
                            <!-- Dynamic fields will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addCustomField()">
                            <i class="fas fa-plus me-1"></i> Add Field
                        </button>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="method_active" name="active" checked>
                        <label class="form-check-label" for="method_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let customFieldCount = 0;

// Icon preview
document.getElementById('method_icon').addEventListener('input', function() {
    document.getElementById('iconPreview').innerHTML = '<i class="' + this.value + '"></i>';
});

// Add custom field
function addCustomField(key = '', label = '', value = '') {
    const html = `
        <div class="row mb-2 custom-field-row">
            <div class="col-4">
                <input type="text" class="form-control form-control-sm" name="fields[${customFieldCount}][key]" 
                       placeholder="Field key" value="${key}">
            </div>
            <div class="col-3">
                <input type="text" class="form-control form-control-sm" name="fields[${customFieldCount}][label]" 
                       placeholder="Label" value="${label}">
            </div>
            <div class="col-4">
                <input type="text" class="form-control form-control-sm" name="fields[${customFieldCount}][value]" 
                       placeholder="Value" value="${value}">
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.custom-field-row').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('customFields').insertAdjacentHTML('beforeend', html);
    customFieldCount++;
}

// Edit method
function editMethod(id) {
    // Fetch method data and populate form
    fetch('<?php echo ADMIN_URL; ?>/gateways/manual/get/' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('method_id').value = data.id;
            document.getElementById('method_name').value = data.name;
            document.getElementById('method_type').value = data.type;
            document.getElementById('method_icon').value = data.icon;
            document.getElementById('method_instructions').value = data.instructions;
            document.getElementById('method_active').checked = data.active;
            
            // Clear and add custom fields
            document.getElementById('customFields').innerHTML = '';
            customFieldCount = 0;
            if (data.fields) {
                Object.entries(data.fields).forEach(([key, field]) => {
                    addCustomField(key, field.label, field.value);
                });
            }
            
            document.getElementById('methodModalTitle').textContent = 'Edit Payment Method';
            new bootstrap.Modal(document.getElementById('addMethodModal')).show();
        });
}

// Toggle method
function toggleMethod(id) {
    fetch('<?php echo ADMIN_URL; ?>/gateways/manual/toggle/' + id, { method: 'POST' })
        .then(() => location.reload());
}

// Delete method
function deleteMethod(id) {
    if (confirm('Are you sure you want to delete this payment method?')) {
        fetch('<?php echo ADMIN_URL; ?>/gateways/manual/delete/' + id, { method: 'POST' })
            .then(() => location.reload());
    }
}

// Reset modal on close
document.getElementById('addMethodModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('methodForm').reset();
    document.getElementById('method_id').value = '';
    document.getElementById('customFields').innerHTML = '';
    document.getElementById('methodModalTitle').textContent = 'Add Payment Method';
    customFieldCount = 0;
});
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
