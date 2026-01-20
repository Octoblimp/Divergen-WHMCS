<?php
/**
 * OpenWHM Admin - Email Templates Management
 */

use OpenWHM\Database;
use OpenWHM\Helper;

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    if (isset($_POST['save_template'])) {
        $id = (int)$_POST['id'];
        $data = [
            'name' => trim($_POST['name']),
            'subject' => trim($_POST['subject']),
            'body_html' => $_POST['body_html'],
            'body_plain' => $_POST['body_plain'],
            'type' => $_POST['type'],
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($id) {
            $db->update('email_templates', $data, 'id = ?', [$id]);
            setFlash('success', 'Template updated successfully');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('email_templates', $data);
            setFlash('success', 'Template created successfully');
        }
        
        redirect('index.php?page=email_templates');
    }
    
    if (isset($_POST['delete_template'])) {
        $id = (int)$_POST['id'];
        $template = $db->fetch("SELECT * FROM {$db->table('email_templates')} WHERE id = ?", [$id]);
        
        if ($template && !$template['is_system']) {
            $db->query("DELETE FROM {$db->table('email_templates')} WHERE id = ?", [$id]);
            setFlash('success', 'Template deleted successfully');
        } else {
            setFlash('error', 'Cannot delete system template');
        }
        
        redirect('index.php?page=email_templates');
    }
}

// Seed default templates if none exist
$templateCount = $db->fetch("SELECT COUNT(*) as count FROM {$db->table('email_templates')}")['count'];
if ($templateCount == 0) {
    $defaultTemplates = [
        [
            'name' => 'Invoice Created',
            'type' => 'invoice',
            'subject' => 'Invoice #{invoice_number} Generated',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>A new invoice has been generated for your account.</p>
<p><strong>Invoice #:</strong> {invoice_number}<br>
<strong>Due Date:</strong> {due_date}<br>
<strong>Total:</strong> {currency_symbol}{total}</p>
<p>Please login to your client area to view and pay this invoice.</p>
<p><a href="{client_area_url}/invoices/view/{invoice_id}" class="btn">View Invoice</a></p>
<p>Thank you for your business!</p>',
            'body_plain' => 'Dear {client_first_name},

A new invoice has been generated for your account.

Invoice #: {invoice_number}
Due Date: {due_date}
Total: {currency_symbol}{total}

Please login to your client area to view and pay this invoice:
{client_area_url}/invoices/view/{invoice_id}

Thank you for your business!',
            'is_system' => 1
        ],
        [
            'name' => 'Invoice Paid',
            'type' => 'invoice',
            'subject' => 'Payment Received - Invoice #{invoice_number}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Thank you! We have received your payment for Invoice #{invoice_number}.</p>
<p><strong>Amount Paid:</strong> {currency_symbol}{amount}<br>
<strong>Payment Method:</strong> {payment_method}<br>
<strong>Transaction ID:</strong> {transaction_id}</p>
<p>You can view your invoice and payment receipt in the client area.</p>
<p>Thank you for your business!</p>',
            'body_plain' => 'Dear {client_first_name},

Thank you! We have received your payment for Invoice #{invoice_number}.

Amount Paid: {currency_symbol}{amount}
Payment Method: {payment_method}
Transaction ID: {transaction_id}

You can view your invoice and payment receipt in the client area.

Thank you for your business!',
            'is_system' => 1
        ],
        [
            'name' => 'Invoice Reminder',
            'type' => 'invoice',
            'subject' => 'Payment Reminder - Invoice #{invoice_number}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>This is a friendly reminder that Invoice #{invoice_number} is due on {due_date}.</p>
<p><strong>Amount Due:</strong> {currency_symbol}{total}</p>
<p>Please login to your client area to make your payment.</p>
<p><a href="{client_area_url}/invoices/view/{invoice_id}" class="btn">Pay Invoice</a></p>
<p>If you have already made this payment, please disregard this notice.</p>',
            'body_plain' => 'Dear {client_first_name},

This is a friendly reminder that Invoice #{invoice_number} is due on {due_date}.

Amount Due: {currency_symbol}{total}

Please login to your client area to make your payment:
{client_area_url}/invoices/view/{invoice_id}

If you have already made this payment, please disregard this notice.',
            'is_system' => 1
        ],
        [
            'name' => 'Invoice Overdue',
            'type' => 'invoice',
            'subject' => 'OVERDUE: Invoice #{invoice_number}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p><strong>IMPORTANT:</strong> Invoice #{invoice_number} is now overdue.</p>
<p><strong>Amount Due:</strong> {currency_symbol}{total}<br>
<strong>Due Date:</strong> {due_date}</p>
<p>Please make payment immediately to avoid service interruption.</p>
<p><a href="{client_area_url}/invoices/view/{invoice_id}" class="btn">Pay Now</a></p>
<p>If you are experiencing difficulties, please contact our support team.</p>',
            'body_plain' => 'Dear {client_first_name},

IMPORTANT: Invoice #{invoice_number} is now overdue.

Amount Due: {currency_symbol}{total}
Due Date: {due_date}

Please make payment immediately to avoid service interruption:
{client_area_url}/invoices/view/{invoice_id}

If you are experiencing difficulties, please contact our support team.',
            'is_system' => 1
        ],
        [
            'name' => 'Service Welcome',
            'type' => 'service',
            'subject' => 'Welcome! Your {product_name} is Ready',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Thank you for your order! Your {product_name} has been activated and is ready to use.</p>
<p><strong>Service Details:</strong></p>
<ul>
<li>Product: {product_name}</li>
<li>Domain: {domain}</li>
<li>Username: {username}</li>
<li>Server: {server_hostname}</li>
</ul>
<p>You can manage your service from the client area.</p>
<p><a href="{client_area_url}/services/view/{service_id}" class="btn">Manage Service</a></p>
<p>Welcome aboard!</p>',
            'body_plain' => 'Dear {client_first_name},

Thank you for your order! Your {product_name} has been activated and is ready to use.

Service Details:
- Product: {product_name}
- Domain: {domain}
- Username: {username}
- Server: {server_hostname}

You can manage your service from the client area:
{client_area_url}/services/view/{service_id}

Welcome aboard!',
            'is_system' => 1
        ],
        [
            'name' => 'Service Suspended',
            'type' => 'service',
            'subject' => 'Service Suspended - {product_name}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Your service {product_name} ({domain}) has been suspended.</p>
<p><strong>Reason:</strong> {suspend_reason}</p>
<p>To reactivate your service, please log in to your client area and pay any outstanding invoices.</p>
<p><a href="{client_area_url}/services" class="btn">View Services</a></p>
<p>If you believe this is in error, please contact support.</p>',
            'body_plain' => 'Dear {client_first_name},

Your service {product_name} ({domain}) has been suspended.

Reason: {suspend_reason}

To reactivate your service, please log in to your client area and pay any outstanding invoices:
{client_area_url}/services

If you believe this is in error, please contact support.',
            'is_system' => 1
        ],
        [
            'name' => 'Service Terminated',
            'type' => 'service',
            'subject' => 'Service Terminated - {product_name}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Your service {product_name} ({domain}) has been terminated.</p>
<p>All data associated with this service has been removed.</p>
<p>If you wish to reactivate or have any questions, please contact our support team.</p>',
            'body_plain' => 'Dear {client_first_name},

Your service {product_name} ({domain}) has been terminated.

All data associated with this service has been removed.

If you wish to reactivate or have any questions, please contact our support team.',
            'is_system' => 1
        ],
        [
            'name' => 'Ticket Opened',
            'type' => 'support',
            'subject' => '[Ticket #{ticket_id}] {subject}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Your support ticket has been opened and assigned ticket ID #{ticket_id}.</p>
<p><strong>Department:</strong> {department}<br>
<strong>Subject:</strong> {subject}<br>
<strong>Priority:</strong> {priority}</p>
<p>Our team will respond as soon as possible.</p>
<p><a href="{client_area_url}/tickets/view/{ticket_id}" class="btn">View Ticket</a></p>',
            'body_plain' => 'Dear {client_first_name},

Your support ticket has been opened and assigned ticket ID #{ticket_id}.

Department: {department}
Subject: {subject}
Priority: {priority}

Our team will respond as soon as possible.

View your ticket: {client_area_url}/tickets/view/{ticket_id}',
            'is_system' => 1
        ],
        [
            'name' => 'Ticket Reply',
            'type' => 'support',
            'subject' => '[Ticket #{ticket_id}] {subject}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>A new reply has been posted to your ticket #{ticket_id}.</p>
<hr>
<p>{reply_message}</p>
<hr>
<p><a href="{client_area_url}/tickets/view/{ticket_id}" class="btn">View & Reply</a></p>',
            'body_plain' => 'Dear {client_first_name},

A new reply has been posted to your ticket #{ticket_id}.

---
{reply_message}
---

View & Reply: {client_area_url}/tickets/view/{ticket_id}',
            'is_system' => 1
        ],
        [
            'name' => 'Domain Expiry Notice',
            'type' => 'domain',
            'subject' => 'Domain Expiry Notice - {domain_name}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Your domain {domain_name} is expiring on {expiry_date}.</p>
<p>To ensure continued ownership, please renew your domain before it expires.</p>
<p><a href="{client_area_url}/domains" class="btn">Manage Domains</a></p>
<p>If you do not wish to renew, no action is needed.</p>',
            'body_plain' => 'Dear {client_first_name},

Your domain {domain_name} is expiring on {expiry_date}.

To ensure continued ownership, please renew your domain before it expires.

Manage Domains: {client_area_url}/domains

If you do not wish to renew, no action is needed.',
            'is_system' => 1
        ],
        [
            'name' => 'Password Reset',
            'type' => 'client',
            'subject' => 'Password Reset Request',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>We received a request to reset your account password.</p>
<p><a href="{reset_url}" class="btn">Reset Password</a></p>
<p>This link will expire in 1 hour.</p>
<p>If you did not request this, please ignore this email.</p>',
            'body_plain' => 'Dear {client_first_name},

We received a request to reset your account password.

Reset Password: {reset_url}

This link will expire in 1 hour.

If you did not request this, please ignore this email.',
            'is_system' => 1
        ],
        [
            'name' => 'Welcome Email',
            'type' => 'client',
            'subject' => 'Welcome to {company_name}!',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Welcome to {company_name}! Your account has been created successfully.</p>
<p><strong>Your Account Details:</strong></p>
<ul>
<li>Email: {email}</li>
<li>Client Area: <a href="{client_area_url}">{client_area_url}</a></li>
</ul>
<p>You can now login to your client area to manage services, view invoices, and open support tickets.</p>
<p><a href="{client_area_url}/login" class="btn">Login Now</a></p>
<p>Thank you for choosing us!</p>',
            'body_plain' => 'Dear {client_first_name},

Welcome to {company_name}! Your account has been created successfully.

Your Account Details:
- Email: {email}
- Client Area: {client_area_url}

You can now login to your client area to manage services, view invoices, and open support tickets.

Login: {client_area_url}/login

Thank you for choosing us!',
            'is_system' => 1
        ],
        [
            'name' => 'Order Confirmation',
            'type' => 'order',
            'subject' => 'Order Confirmation #{order_number}',
            'body_html' => '<p>Dear {client_first_name},</p>
<p>Thank you for your order! Your order #{order_number} has been received.</p>
<p><strong>Order Summary:</strong></p>
{order_items}
<p><strong>Total:</strong> {currency_symbol}{total}</p>
<p>Once payment is received, we will begin processing your order.</p>
<p><a href="{client_area_url}/invoices/view/{invoice_id}" class="btn">Pay Invoice</a></p>',
            'body_plain' => 'Dear {client_first_name},

Thank you for your order! Your order #{order_number} has been received.

Order Summary:
{order_items}

Total: {currency_symbol}{total}

Once payment is received, we will begin processing your order.

Pay Invoice: {client_area_url}/invoices/view/{invoice_id}',
            'is_system' => 1
        ]
    ];
    
    foreach ($defaultTemplates as $template) {
        $template['enabled'] = 1;
        $template['created_at'] = date('Y-m-d H:i:s');
        $db->insert('email_templates', $template);
    }
}

// Edit template
if ($action === 'edit' && isset($_GET['id'])) {
    $template = $db->fetch(
        "SELECT * FROM {$db->table('email_templates')} WHERE id = ?",
        [(int)$_GET['id']]
    );
    
    if (!$template) {
        setFlash('error', 'Template not found');
        redirect('index.php?page=email_templates');
    }
}

// New template
if ($action === 'new') {
    $template = [
        'id' => 0,
        'name' => '',
        'subject' => '',
        'body_html' => '',
        'body_plain' => '',
        'type' => 'general',
        'enabled' => 1,
        'is_system' => 0
    ];
}
?>

<?php if ($action === 'list'): ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="fas fa-envelope me-2"></i> Email Templates</h1>
    <a href="index.php?page=email_templates&action=new" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> New Template
    </a>
</div>

<?php
$types = ['invoice', 'service', 'support', 'domain', 'client', 'order', 'general'];
$templates = $db->fetchAll("SELECT * FROM {$db->table('email_templates')} ORDER BY type, name");
$grouped = [];
foreach ($templates as $t) {
    $grouped[$t['type']][] = $t;
}
?>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($types as $i => $type): ?>
            <li class="nav-item">
                <a class="nav-link <?= $i === 0 ? 'active' : '' ?>" 
                   data-bs-toggle="tab" href="#tab-<?= $type ?>">
                    <?= ucfirst($type) ?>
                    <?php if (isset($grouped[$type])): ?>
                    <span class="badge bg-secondary"><?= count($grouped[$type]) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="tab-content pt-3">
            <?php foreach ($types as $i => $type): ?>
            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="tab-<?= $type ?>">
                <?php if (isset($grouped[$type]) && count($grouped[$type]) > 0): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped[$type] as $t): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($t['name']) ?>
                                <?php if ($t['is_system']): ?>
                                <span class="badge bg-info">System</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['subject']) ?></td>
                            <td>
                                <?php if ($t['enabled']): ?>
                                <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=email_templates&action=edit&id=<?= $t['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (!$t['is_system']): ?>
                                <form method="post" class="d-inline" 
                                      onsubmit="return confirm('Delete this template?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="submit" name="delete_template" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">No templates in this category.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Available Variables Reference -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-code me-2"></i> Available Template Variables</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Client Variables</h6>
                <code>{client_first_name}</code><br>
                <code>{client_last_name}</code><br>
                <code>{client_email}</code><br>
                <code>{company_name}</code><br>
                <code>{client_area_url}</code>
            </div>
            <div class="col-md-4">
                <h6>Invoice Variables</h6>
                <code>{invoice_id}</code><br>
                <code>{invoice_number}</code><br>
                <code>{due_date}</code><br>
                <code>{total}</code><br>
                <code>{currency_symbol}</code>
            </div>
            <div class="col-md-4">
                <h6>Service Variables</h6>
                <code>{service_id}</code><br>
                <code>{product_name}</code><br>
                <code>{domain}</code><br>
                <code>{username}</code><br>
                <code>{server_hostname}</code>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <h6>Ticket Variables</h6>
                <code>{ticket_id}</code><br>
                <code>{subject}</code><br>
                <code>{department}</code><br>
                <code>{priority}</code><br>
                <code>{reply_message}</code>
            </div>
            <div class="col-md-4">
                <h6>Domain Variables</h6>
                <code>{domain_name}</code><br>
                <code>{expiry_date}</code><br>
                <code>{registration_date}</code>
            </div>
            <div class="col-md-4">
                <h6>Order Variables</h6>
                <code>{order_number}</code><br>
                <code>{order_items}</code><br>
                <code>{order_date}</code>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Edit/New Template Form -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-envelope me-2"></i>
        <?= $action === 'new' ? 'New Template' : 'Edit Template' ?>
    </h1>
    <a href="index.php?page=email_templates" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back
    </a>
</div>

<form method="post">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $template['id'] ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Template Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($template['name']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="invoice" <?= $template['type'] === 'invoice' ? 'selected' : '' ?>>Invoice</option>
                                <option value="service" <?= $template['type'] === 'service' ? 'selected' : '' ?>>Service</option>
                                <option value="support" <?= $template['type'] === 'support' ? 'selected' : '' ?>>Support</option>
                                <option value="domain" <?= $template['type'] === 'domain' ? 'selected' : '' ?>>Domain</option>
                                <option value="client" <?= $template['type'] === 'client' ? 'selected' : '' ?>>Client</option>
                                <option value="order" <?= $template['type'] === 'order' ? 'selected' : '' ?>>Order</option>
                                <option value="general" <?= $template['type'] === 'general' ? 'selected' : '' ?>>General</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required
                               value="<?= htmlspecialchars($template['subject']) ?>">
                    </div>
                    
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#html-body">HTML Body</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#plain-body">Plain Text Body</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#preview">Preview</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content border border-top-0 p-3">
                        <div class="tab-pane fade show active" id="html-body">
                            <textarea name="body_html" id="body_html" class="form-control" rows="15"><?= htmlspecialchars($template['body_html']) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="plain-body">
                            <textarea name="body_plain" class="form-control font-monospace" rows="15"><?= htmlspecialchars($template['body_plain']) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="preview">
                            <div id="email-preview" style="background: #fff; padding: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input type="checkbox" name="enabled" class="form-check-input" value="1"
                               <?= $template['enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label">Enabled</label>
                    </div>
                    
                    <?php if ($template['is_system'] ?? false): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is a system template and cannot be deleted.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Insert Variable</h5>
                </div>
                <div class="card-body">
                    <select id="variable-select" class="form-select mb-2">
                        <optgroup label="Client">
                            <option value="{client_first_name}">Client First Name</option>
                            <option value="{client_last_name}">Client Last Name</option>
                            <option value="{client_email}">Client Email</option>
                            <option value="{company_name}">Company Name</option>
                            <option value="{client_area_url}">Client Area URL</option>
                        </optgroup>
                        <optgroup label="Invoice">
                            <option value="{invoice_id}">Invoice ID</option>
                            <option value="{invoice_number}">Invoice Number</option>
                            <option value="{due_date}">Due Date</option>
                            <option value="{total}">Total Amount</option>
                            <option value="{currency_symbol}">Currency Symbol</option>
                            <option value="{amount}">Amount Paid</option>
                            <option value="{payment_method}">Payment Method</option>
                            <option value="{transaction_id}">Transaction ID</option>
                        </optgroup>
                        <optgroup label="Service">
                            <option value="{service_id}">Service ID</option>
                            <option value="{product_name}">Product Name</option>
                            <option value="{domain}">Domain</option>
                            <option value="{username}">Username</option>
                            <option value="{password}">Password</option>
                            <option value="{server_hostname}">Server Hostname</option>
                            <option value="{suspend_reason}">Suspend Reason</option>
                        </optgroup>
                        <optgroup label="Ticket">
                            <option value="{ticket_id}">Ticket ID</option>
                            <option value="{subject}">Subject</option>
                            <option value="{department}">Department</option>
                            <option value="{priority}">Priority</option>
                            <option value="{reply_message}">Reply Message</option>
                        </optgroup>
                        <optgroup label="Domain">
                            <option value="{domain_name}">Domain Name</option>
                            <option value="{expiry_date}">Expiry Date</option>
                            <option value="{registration_date}">Registration Date</option>
                        </optgroup>
                        <optgroup label="Order">
                            <option value="{order_number}">Order Number</option>
                            <option value="{order_items}">Order Items</option>
                            <option value="{order_date}">Order Date</option>
                        </optgroup>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm w-100" onclick="insertVariable()">
                        <i class="fas fa-plus me-2"></i> Insert
                    </button>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="save_template" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Save Template
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function insertVariable() {
    const variable = document.getElementById('variable-select').value;
    const textarea = document.getElementById('body_html');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + variable + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + variable.length, start + variable.length);
}

// Preview tab
document.querySelector('a[href="#preview"]').addEventListener('click', function() {
    const html = document.getElementById('body_html').value;
    document.getElementById('email-preview').innerHTML = html;
});
</script>

<?php endif; ?>