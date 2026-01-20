<?php $this->extend('client.layouts.main'); ?>

<?php $this->section('content'); ?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice-dollar me-2"></i>Pay Invoice #<?php echo htmlspecialchars($invoice['invoice_num']); ?></h2>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Invoice
            </a>
        </div>
    </div>
    
    <?php if ($flash = $this->session->getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($flash); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Payment Methods -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Select Payment Method</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($gateways)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No payment gateways are currently available. Please contact support for payment instructions.
                    </div>
                    <?php else: ?>
                    <form action="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>/process" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row g-3">
                            <?php foreach ($gateways as $gateway): ?>
                            <div class="col-md-6">
                                <div class="form-check payment-option border rounded p-3 h-100">
                                    <input class="form-check-input" type="radio" name="payment_method" value="<?php echo htmlspecialchars($gateway['name']); ?>" id="gateway_<?php echo $gateway['id']; ?>" <?php echo $gateway === reset($gateways) ? 'checked' : ''; ?>>
                                    <label class="form-check-label w-100 d-flex align-items-center" for="gateway_<?php echo $gateway['id']; ?>">
                                        <?php 
                                        $icon = 'fas fa-money-bill';
                                        $color = 'text-success';
                                        if (stripos($gateway['name'], 'paypal') !== false) {
                                            $icon = 'fab fa-paypal';
                                            $color = 'text-primary';
                                        } elseif (stripos($gateway['name'], 'stripe') !== false) {
                                            $icon = 'fab fa-stripe';
                                            $color = 'text-primary';
                                        } elseif (stripos($gateway['name'], 'bank') !== false) {
                                            $icon = 'fas fa-university';
                                            $color = 'text-secondary';
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?> fa-2x <?php echo $color; ?> me-3"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($gateway['name']); ?></strong>
                                            <?php if (!empty($gateway['description'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($gateway['description']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Pay $<?php echo number_format($invoice['total'], 2); ?>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Use Credit -->
            <?php if ($client['credit'] > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Apply Account Credit</h5>
                </div>
                <div class="card-body">
                    <p>You have <strong class="text-success">$<?php echo number_format($client['credit'], 2); ?></strong> credit available.</p>
                    <form action="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>/apply-credit" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-check me-2"></i>Apply Credit to Invoice
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Invoice Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Invoice Number</td>
                            <td class="text-end"><strong>#<?php echo htmlspecialchars($invoice['invoice_num']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Invoice Date</td>
                            <td class="text-end"><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td>Due Date</td>
                            <td class="text-end">
                                <?php if ($invoice['due_date'] && strtotime($invoice['due_date']) < time()): ?>
                                <span class="text-danger"><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></span>
                                <?php else: ?>
                                <?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td class="text-end">
                                <?php
                                $statusClass = 'secondary';
                                if ($invoice['status'] === 'paid') $statusClass = 'success';
                                elseif ($invoice['status'] === 'unpaid') $statusClass = 'warning';
                                elseif ($invoice['status'] === 'overdue') $statusClass = 'danger';
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($invoice['status']); ?></span>
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Subtotal</strong></td>
                            <td class="text-end">$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                        </tr>
                        <?php if ($invoice['tax'] > 0): ?>
                        <tr>
                            <td>Tax</td>
                            <td class="text-end">$<?php echo number_format($invoice['tax'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($invoice['credit'] > 0): ?>
                        <tr>
                            <td class="text-success">Credit Applied</td>
                            <td class="text-end text-success">-$<?php echo number_format($invoice['credit'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="border-top">
                            <td><strong>Total Due</strong></td>
                            <td class="text-end"><strong class="fs-4 text-primary">$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Security Badge -->
            <div class="card mt-4 bg-light">
                <div class="card-body text-center">
                    <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                    <p class="mb-0 small">Your payment is secured with SSL encryption</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-option {
    cursor: pointer;
    transition: all 0.2s;
}
.payment-option:hover {
    border-color: var(--bs-primary) !important;
    background-color: #f8f9fc;
}
.payment-option:has(input:checked) {
    border-color: var(--bs-primary) !important;
    background-color: #e7f0ff;
}
</style>
<?php $this->endSection(); ?>
