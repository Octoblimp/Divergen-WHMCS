<?php
$page = 'orders';
$title = 'Orders';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-shopping-cart me-2"></i>Orders Management</h1>
        <small class="text-muted">View and manage all customer orders</small>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Orders</small>
                <div class="h5 fw-bold"><?php echo $orders['total'] ?? 0; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-warning">Pending Review</small>
                <div class="h5 fw-bold text-warning">
                    <?php echo count(array_filter($orders['items'] ?? [], fn($o) => $o['status'] === 'pending')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-success">Active</small>
                <div class="h5 fw-bold text-success">
                    <?php echo count(array_filter($orders['items'] ?? [], fn($o) => $o['status'] === 'active')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-danger">Fraud/Cancelled</small>
                <div class="h5 fw-bold text-danger">
                    <?php echo count(array_filter($orders['items'] ?? [], fn($o) => in_array($o['status'], ['fraud', 'cancelled']))); ?>
                </div>
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
                    <option value="pending" <?php echo ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                    <option value="active" <?php echo ($status ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="fraud" <?php echo ($status ?? '') === 'fraud' ? 'selected' : ''; ?>>Fraud</option>
                    <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Sort By</label>
                <select class="form-select" name="sort">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="highest">Highest Amount</option>
                    <option value="lowest">Lowest Amount</option>
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

<!-- Orders Table -->
<div class="card shadow-sm">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Order List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Client</th>
                        <th>Date & Time</th>
                        <th class="text-center">Items</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders['items'])): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                                No orders found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders['items'] as $order): ?>
                        <tr>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>" class="fw-bold text-decoration-none">
                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $order['client_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($order['client_name'] ?? 'N/A'); ?>
                                </a>
                            </td>
                            <td>
                                <small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">
                                    <?php echo ($order['service_count'] ?? 0) + ($order['domain_count'] ?? 0); ?>
                                </span>
                            </td>
                            <td class="text-right fw-bold">$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'active' => 'success',
                                    'fraud' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'active' => 'check-circle',
                                    'fraud' => 'exclamation-circle',
                                    'cancelled' => 'times-circle'
                                ];
                                $color = $statusColors[$order['status']] ?? 'secondary';
                                $icon = $statusIcons[$order['status']] ?? 'info-circle';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <i class="fas fa-<?php echo $icon; ?> me-1"></i><?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($orders['items']) && $orders['total_pages'] > 1): ?>
    <div class="card-footer bg-light border-top">
        <nav aria-label="Pagination">
            <ul class="pagination mb-0 justify-content-center">
                <?php if ($orders['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $orders['current_page'] - 1; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $orders['current_page'] - 2);
                $end = min($orders['total_pages'], $orders['current_page'] + 2);
                if ($start > 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $i === $orders['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end < $orders['total_pages']): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php if ($orders['current_page'] < $orders['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $orders['current_page'] + 1; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $orders['total_pages']; ?><?php echo ($status ?? '') ? '&status=' . urlencode($status) : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
