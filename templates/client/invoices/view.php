<?php $this->extend('client.layouts.main'); ?>

<?php $this->section('content'); ?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice-dollar me-2"></i>Invoice #<?php echo htmlspecialchars($invoice['invoice_num']); ?></h2>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>/download" class="btn btn-outline-secondary me-2" target="_blank">
                <i class="fas fa-download me-2"></i>Download
            </a>
            <?php if ($invoice['status'] !== 'paid'): ?>
            <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>/pay" class="btn btn-primary">
                <i class="fas fa-credit-card me-2"></i>Pay Now
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($flash = $this->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($flash); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Invoice -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-muted">From</h5>
                    <h4><?php echo COMPANY_NAME; ?></h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="text-muted">Invoice To</h5>
                    <h4><?php echo htmlspecialchars($invoice['firstname'] . ' ' . $invoice['lastname']); ?></h4>
                    <?php if ($invoice['company']): ?>
                    <p class="mb-0"><?php echo htmlspecialchars($invoice['company']); ?></p>
                    <?php endif; ?>
                    <p class="mb-0"><?php echo htmlspecialchars($invoice['email']); ?></p>
                    <?php if ($invoice['address1']): ?>
                    <p class="mb-0 text-muted">
                        <?php echo htmlspecialchars($invoice['address1']); ?><br>
                        <?php if ($invoice['address2']): ?><?php echo htmlspecialchars($invoice['address2']); ?><br><?php endif; ?>
                        <?php echo htmlspecialchars($invoice['city'] . ', ' . $invoice['state'] . ' ' . $invoice['postcode']); ?><br>
                        <?php echo htmlspecialchars($invoice['country']); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <p class="mb-1 text-muted small">Invoice Number</p>
                    <p class="fw-bold">#<?php echo htmlspecialchars($invoice['invoice_num']); ?></p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small">Invoice Date</p>
                    <p><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small">Due Date</p>
                    <p><?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?></p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small">Status</p>
                    <?php
                    $statusClass = 'secondary';
                    if ($invoice['status'] === 'paid') $statusClass = 'success';
                    elseif ($invoice['status'] === 'unpaid') $statusClass = 'warning';
                    elseif ($invoice['status'] === 'overdue') $statusClass = 'danger';
                    ?>
                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($invoice['status']); ?></span>
                </div>
            </div>
            
            <!-- Invoice Items -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td class="text-end">$<?php echo number_format($item['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-end"><strong>Subtotal</strong></td>
                        <td class="text-end">$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                    </tr>
                    <?php if ($invoice['tax'] > 0): ?>
                    <tr>
                        <td class="text-end">Tax</td>
                        <td class="text-end">$<?php echo number_format($invoice['tax'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice['credit'] > 0): ?>
                    <tr class="text-success">
                        <td class="text-end">Credit Applied</td>
                        <td class="text-end">-$<?php echo number_format($invoice['credit'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-primary">
                        <td class="text-end"><strong>Total</strong></td>
                        <td class="text-end"><strong class="fs-5">$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <!-- Transaction History -->
    <?php if (!empty($transactions)): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Gateway</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($transaction['gateway']); ?></td>
                        <td><code><?php echo htmlspecialchars($transaction['transaction_id']); ?></code></td>
                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td>
                            <?php
                            $txStatusClass = 'secondary';
                            if ($transaction['status'] === 'completed') $txStatusClass = 'success';
                            elseif ($transaction['status'] === 'pending') $txStatusClass = 'warning';
                            elseif ($transaction['status'] === 'failed') $txStatusClass = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $txStatusClass; ?>"><?php echo ucfirst($transaction['status']); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>
