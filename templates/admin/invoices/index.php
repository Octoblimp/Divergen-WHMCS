<?php
$page = 'invoices';
$title = 'Invoices';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-file-invoice-dollar me-2"></i>Invoices</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="unpaid" <?php echo ($status ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                    <option value="paid" <?php echo ($status ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="overdue" <?php echo ($status ?? '') === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="refunded" <?php echo ($status ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list me-2"></i>Invoice List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices['items'])): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No invoices found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices['items'] as $invoice): ?>
                        <tr>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>">
                                    <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $invoice['client_id']; ?>">
                                    <?php echo htmlspecialchars($invoice['client_name'] ?? 'N/A'); ?>
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
                            <td>$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                            <td class="fw-bold">$<?php echo number_format($invoice['total'], 2); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'paid' => 'success',
                                    'unpaid' => 'warning',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary',
                                    'refunded' => 'info'
                                ];
                                $displayStatus = $isOverdue ? 'overdue' : $invoice['status'];
                                $color = $statusColors[$displayStatus] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($displayStatus); ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($invoice['status'] === 'unpaid'): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>/mark-paid?csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-outline-success" title="Mark Paid">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($invoices['items']) && $invoices['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $invoices['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $invoices['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
