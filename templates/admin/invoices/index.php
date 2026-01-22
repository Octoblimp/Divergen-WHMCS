<?php
$page = 'invoices';
$title = 'Invoices';
ob_start();

// Calculate summary stats
$totalValue = 0;
$paidValue = 0;
$unpaidValue = 0;
$overdueValue = 0;
foreach ($invoices['items'] ?? [] as $inv) {
    $totalValue += $inv['total'];
    if ($inv['status'] === 'paid') {
        $paidValue += $inv['total'];
    } else {
        $unpaidValue += $inv['total'];
        if (strtotime($inv['due_date']) < time()) {
            $overdueValue += $inv['total'];
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-file-invoice-dollar me-2"></i>Invoices Management</h1>
        <small class="text-muted">Track and manage all client invoices</small>
    </div>
</div>

<!-- Financial Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient-primary text-white">
            <div class="card-body">
                <small class="opacity-75">Total Invoiced</small>
                <div class="h5 fw-bold">$<?php echo number_format($totalValue, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient-success text-white">
            <div class="card-body">
                <small class="opacity-75">Paid Invoices</small>
                <div class="h5 fw-bold">$<?php echo number_format($paidValue, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient-warning text-white">
            <div class="card-body">
                <small class="opacity-75">Unpaid Invoices</small>
                <div class="h5 fw-bold">$<?php echo number_format($unpaidValue, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient-danger text-white">
            <div class="card-body">
                <small class="opacity-75">Overdue Amount</small>
                <div class="h5 fw-bold">$<?php echo number_format($overdueValue, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="paid" <?php echo ($status ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="unpaid" <?php echo ($status ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                    <option value="overdue" <?php echo ($status ?? '') === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="refunded" <?php echo ($status ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Date Range</label>
                <select class="form-select" name="date_range">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <?php if (($status ?? '')): ?>
            <div class="col-md-2">
                <a href="?page=1" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card shadow-sm">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Invoice List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Issued</th>
                        <th>Due Date</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Paid</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices['items'])): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-2x mb-3 d-block opacity-25"></i>
                                No invoices found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices['items'] as $invoice): ?>
                        <?php 
                        $dueDate = strtotime($invoice['due_date']);
                        $isOverdue = $invoice['status'] === 'unpaid' && $dueDate < time();
                        $displayStatus = $isOverdue ? 'overdue' : $invoice['status'];
                        ?>
                        <tr <?php echo $isOverdue ? 'class="table-danger"' : ''; ?>>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="fw-bold text-decoration-none">
                                    <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $invoice['client_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($invoice['client_name'] ?? 'N/A'); ?>
                                </a>
                            </td>
                            <td>
                                <small><?php echo date('M d, Y', strtotime($invoice['date'])); ?></small>
                            </td>
                            <td>
                                <small <?php echo $isOverdue ? 'class="fw-bold text-danger"' : ''; ?>>
                                    <?php echo date('M d, Y', $dueDate); ?>
                                    <?php if ($isOverdue): ?>
                                        <br><span class="badge bg-danger">OVERDUE</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td class="text-right fw-bold">$<?php echo number_format($invoice['total'], 2); ?></td>
                            <td class="text-right">
                                <?php if ($invoice['status'] === 'paid'): ?>
                                    <span class="text-success fw-bold">$<?php echo number_format($invoice['total'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">$0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'paid' => 'success',
                                    'unpaid' => 'warning',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary',
                                    'refunded' => 'info'
                                ];
                                $statusIcons = [
                                    'paid' => 'check-circle',
                                    'unpaid' => 'clock',
                                    'overdue' => 'exclamation-circle',
                                    'cancelled' => 'ban',
                                    'refunded' => 'undo'
                                ];
                                $color = $statusColors[$displayStatus] ?? 'secondary';
                                $icon = $statusIcons[$displayStatus] ?? 'info-circle';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <i class="fas fa-<?php echo $icon; ?> me-1"></i><?php echo ucfirst($displayStatus); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary" title="View Invoice">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($invoice['status'] === 'unpaid' || $invoice['status'] === 'partially_paid'): ?>
                                    <button type="button" class="btn btn-outline-success" onclick="markAsPaid(<?php echo $invoice['id']; ?>)" title="Mark as Paid">
                                        <i class="fas fa-check"></i>
                                    </button>
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
    <div class="card-footer bg-light border-top">
        <nav aria-label="Pagination">
            <ul class="pagination mb-0 justify-content-center">
                <?php if ($invoices['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $invoices['current_page'] - 1; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $invoices['current_page'] - 2);
                $end = min($invoices['total_pages'], $invoices['current_page'] + 2);
                if ($start > 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $i === $invoices['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end < $invoices['total_pages']): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php if ($invoices['current_page'] < $invoices['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $invoices['current_page'] + 1; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $invoices['total_pages']; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important; }
.bg-gradient-danger { background: linear-gradient(135deg, #eb3b5a 0%, #fc5c65 100%) !important; }
</style>

<script>
function markAsPaid(invoiceId) {
    if (confirm('Mark this invoice as paid?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/invoices/' + invoiceId + '/mark-paid';
    }
}
</script>
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
