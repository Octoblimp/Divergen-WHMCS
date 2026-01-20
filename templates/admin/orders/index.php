<?php
$page = 'orders';
$title = 'Orders';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-shopping-cart me-2"></i>Orders</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo ($status ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="fraud" <?php echo ($status ?? '') === 'fraud' ? 'selected' : ''; ?>>Fraud</option>
                    <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list me-2"></i>Order List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders['items'])): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders['items'] as $order): ?>
                        <tr>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>">
                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $order['client_id']; ?>">
                                    <?php echo htmlspecialchars($order['client_name'] ?? 'N/A'); ?>
                                </a>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo ($order['service_count'] ?? 0) + ($order['domain_count'] ?? 0); ?></td>
                            <td>$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'active' => 'success',
                                    'fraud' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                                $color = $statusColors[$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>/accept?csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-outline-success" title="Accept">
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
    
    <?php if (!empty($orders['items']) && $orders['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $orders['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $orders['current_page'] ? 'active' : ''; ?>">
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
