<?php
$page = 'invoices';
$title = 'My Invoices';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice-dollar me-2"></i>My Invoices</h2>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Invoices</h5>
                                <p class="text-muted">You don't have any invoices yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>">
                                    <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                </a>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($invoice['date'])); ?></td>
                            <td>
                                <?php 
                                $dueDate = strtotime($invoice['due_date']);
                                $isOverdue = $invoice['status'] === 'unpaid' && $dueDate < time();
                                ?>
                                <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                    <?php echo date('M d, Y', $dueDate); ?>
                                </span>
                            </td>
                            <td class="fw-bold">$<?php echo number_format($invoice['total'], 2); ?></td>
                            <td>
                                <?php
                                $colors = [
                                    'paid' => 'success',
                                    'unpaid' => 'warning',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary',
                                    'refunded' => 'info'
                                ];
                                $status = $isOverdue ? 'overdue' : $invoice['status'];
                                $color = $colors[$status] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($status); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <?php if ($invoice['status'] === 'unpaid'): ?>
                                <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>/pay" class="btn btn-sm btn-success">
                                    <i class="fas fa-credit-card me-1"></i>Pay
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
