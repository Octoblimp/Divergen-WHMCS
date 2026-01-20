<?php
$page = 'dashboard';
$title = 'Dashboard';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
    <div>
        <span class="text-muted">Welcome back, <?php echo $admin['name']; ?>!</span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <!-- Clients -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card primary h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Total Clients</div>
                        <div class="stat-value"><?php echo number_format($stats['clients']['total'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Services -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Active Services</div>
                        <div class="stat-value"><?php echo number_format($stats['services']['active'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cube stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue This Month -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card info h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Revenue (Month)</div>
                        <div class="stat-value">$<?php echo number_format($stats['invoices']['revenue_month'] ?? 0, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Open Tickets -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card warning h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Open Tickets</div>
                        <div class="stat-value"><?php echo number_format($stats['tickets']['open'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ticket-alt stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card danger h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Overdue Invoices</div>
                        <div class="stat-value"><?php echo number_format($stats['invoices']['overdue'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card primary h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Pending Orders</div>
                        <div class="stat-value"><?php echo number_format($stats['orders']['pending'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Orders (Month)</div>
                        <div class="stat-value"><?php echo number_format($stats['orders']['this_month'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card info h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label">Unpaid Invoices</div>
                        <div class="stat-value">$<?php echo number_format($stats['invoices']['unpaid_total'] ?? 0, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pending Orders -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-shopping-cart me-2"></i>Pending Orders</h6>
                <a href="<?php echo ADMIN_URL; ?>/orders?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingOrders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No pending orders</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingOrders as $order): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>">#<?php echo $order['order_number']; ?></a></td>
                                    <td><?php echo htmlspecialchars($order['client_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>/accept" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Open Tickets -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-ticket-alt me-2"></i>Recent Tickets</h6>
                <a href="<?php echo ADMIN_URL; ?>/tickets" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Client</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTickets)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No open tickets</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>"><?php echo htmlspecialchars($ticket['ticket_number']); ?></a></td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['client_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'open' => 'success',
                                            'answered' => 'primary',
                                            'customer-reply' => 'warning',
                                            'on-hold' => 'secondary',
                                            'in-progress' => 'info',
                                            'closed' => 'dark'
                                        ];
                                        $color = $statusColors[$ticket['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Invoices -->
<?php if (!empty($overdueInvoices)): ?>
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Overdue Invoices</h6>
                <a href="<?php echo ADMIN_URL; ?>/invoices?status=overdue" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Days Overdue</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdueInvoices as $invoice): ?>
                            <tr>
                                <td><a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>"><?php echo htmlspecialchars($invoice['invoice_number']); ?></a></td>
                                <td><?php echo htmlspecialchars($invoice['client_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                                <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                                <td class="text-danger fw-bold">
                                    <?php echo floor((time() - strtotime($invoice['due_date'])) / 86400); ?> days
                                </td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
