<?php
/**
 * Checkout - Manual Payment Instructions Page
 */
$this->layout = 'client.layouts.whmcs';
$title = 'Payment Instructions';
ob_start();
?>

<h1 class="page-title">Payment Instructions</h1>
<nav class="breadcrumb-whmcs">
    <a href="<?php echo CLIENT_URL; ?>">Portal Home</a> / 
    <a href="<?php echo CLIENT_URL; ?>/invoices">Invoices</a> / 
    Invoice #<?php echo $invoice['id']; ?>
</nav>

<div class="row">
    <div class="col-lg-8">
        <div class="panel-whmcs">
            <div class="panel-whmcs-header">
                <h4><i class="<?php echo htmlspecialchars($method['icon'] ?? 'fas fa-money-bill'); ?>"></i> 
                    Pay via <?php echo htmlspecialchars($method['name']); ?></h4>
            </div>
            <div class="panel-whmcs-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Invoice #<?php echo $invoice['id']; ?></strong> - 
                    Amount Due: <strong>$<?php echo number_format($invoice['total'], 2); ?> USD</strong>
                </div>
                
                <div class="payment-instructions p-4 bg-light border rounded">
                    <?php echo nl2br(htmlspecialchars($instructions)); ?>
                </div>
                
                <hr>
                
                <p class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Your invoice status has been updated to <strong>Payment Pending</strong>. 
                    Once we receive and verify your payment, your services will be activated and 
                    you'll receive a confirmation email.
                </p>
                
                <div class="d-flex gap-2">
                    <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-file-invoice me-1"></i> View Invoice
                    </a>
                    <a href="<?php echo CLIENT_URL; ?>/invoices" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> All Invoices
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Instructions
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Already Paid? -->
        <div class="panel-whmcs mt-4">
            <div class="panel-whmcs-header">
                <h4><i class="fas fa-check-circle"></i> Already Made Payment?</h4>
            </div>
            <div class="panel-whmcs-body">
                <p>If you've already sent your payment, please allow time for processing:</p>
                <ul>
                    <li><strong>Zelle/Venmo/CashApp:</strong> 1-24 hours</li>
                    <li><strong>Wire Transfer:</strong> 3-5 business days</li>
                    <li><strong>Check:</strong> 7-10 business days</li>
                </ul>
                <p>Have a transaction ID or confirmation? <a href="<?php echo CLIENT_URL; ?>/tickets/open">Open a support ticket</a> to expedite verification.</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Invoice Summary -->
        <div class="panel-whmcs">
            <div class="panel-whmcs-header">
                <h4><i class="fas fa-file-invoice"></i> Invoice Summary</h4>
            </div>
            <div class="panel-whmcs-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Invoice #</td>
                        <td class="text-end"><strong><?php echo $invoice['id']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td class="text-end"><?php echo date('M j, Y', strtotime($invoice['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td>Due Date</td>
                        <td class="text-end"><?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td class="text-end"><span class="badge bg-warning">Payment Pending</span></td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>Total</strong></td>
                        <td class="text-end"><strong>$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Need Help? -->
        <div class="panel-whmcs mt-4">
            <div class="panel-whmcs-header">
                <h4><i class="fas fa-question-circle"></i> Need Help?</h4>
            </div>
            <div class="panel-whmcs-body">
                <p>If you have any questions about making a payment:</p>
                <a href="<?php echo CLIENT_URL; ?>/tickets/open" class="btn btn-outline-primary w-100">
                    <i class="fas fa-ticket-alt me-1"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar-whmcs, .sidebar, footer, .btn { display: none !important; }
    .content-area { padding: 0 !important; }
    .col-lg-8 { width: 100% !important; }
    .col-lg-4 { display: none !important; }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/whmcs.php';
?>
