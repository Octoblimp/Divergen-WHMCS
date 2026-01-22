<?php
$page = 'dashboard';
$title = 'Dashboard';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
        <small class="text-muted">Welcome back, <?php echo htmlspecialchars($admin['name']); ?>!</small>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- Financial Overview -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-lg-6">
        <div class="card stat-card bg-gradient-primary h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label text-white-50">Total Revenue</div>
                        <div class="stat-value text-white">$<?php echo number_format($stats['revenue'] ?? 0, 2); ?></div>
                        <small class="text-white-50">All time</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Recurring Revenue -->
    <div class="col-xl-3 col-lg-6">
        <div class="card stat-card bg-gradient-success h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label text-white-50">MRR</div>
                        <div class="stat-value text-white">$<?php echo number_format($stats['mrr'] ?? 0, 2); ?></div>
                        <small class="text-white-50">This month</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-piggy-bank stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Revenue -->
    <div class="col-xl-3 col-lg-6">
        <div class="card stat-card bg-gradient-warning h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label text-white-50">Pending Revenue</div>
                        <div class="stat-value text-white">$<?php echo number_format($stats['pending_revenue'] ?? 0, 2); ?></div>
                        <small class="text-white-50">Unpaid invoices</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Overdue Invoices Count -->
    <div class="col-xl-3 col-lg-6">
        <div class="card stat-card bg-gradient-danger h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-label text-white-50">Overdue</div>
                        <div class="stat-value text-white"><?php echo count($overdueInvoices ?? []); ?></div>
                        <small class="text-white-50">Requires attention</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Business Metrics -->
<div class="row g-4 mb-4">
    <!-- Total Clients -->
    <div class="col-xl-3 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Clients</div>
                        <div class="h4 mb-0 fw-bold"><?php echo number_format($stats['clients']['total'] ?? 0); ?></div>
                        <div class="small text-success">
                            <i class="fas fa-arrow-up"></i> 
                            <?php echo ($stats['clients']['this_month'] ?? 0); ?> this month
                        </div>
                    </div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Services -->
    <div class="col-xl-3 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Active Services</div>
                        <div class="h4 mb-0 fw-bold"><?php echo number_format($stats['services']['active'] ?? 0); ?></div>
                        <div class="small">
                            <?php echo ($stats['services']['suspended'] ?? 0); ?> suspended
                        </div>
                    </div>
                    <i class="fas fa-cube fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Orders -->
    <div class="col-xl-3 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Pending Orders</div>
                        <div class="h4 mb-0 fw-bold"><?php echo number_format($stats['orders']['pending'] ?? 0); ?></div>
                        <div class="small text-warning">
                            <i class="fas fa-clock"></i> Needs review
                        </div>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Support Tickets -->
    <div class="col-xl-3 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Open Tickets</div>
                        <div class="h4 mb-0 fw-bold"><?php echo $supportStats['open_tickets'] ?? 0; ?></div>
                        <div class="small">
                            <?php echo round($supportStats['avg_response_time'] ?? 0, 1); ?>h avg response
                        </div>
                    </div>
                    <i class="fas fa-headset fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pending Orders -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i>Pending Orders</h6>
                    <a href="<?php echo ADMIN_URL; ?>/orders?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingOrders)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-check-circle fa-2x mb-3 text-success"></i>
                        <p>No pending orders</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingOrders as $order): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>" class="fw-bold text-decoration-none">#<?php echo $order['order_number']; ?></a></td>
                                    <td><?php echo htmlspecialchars(substr($order['client_name'] ?? 'N/A', 0, 20)); ?></td>
                                    <td class="fw-bold">$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/orders/<?php echo $order['id']; ?>" class="btn btn-sm btn-link">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Tickets -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-ticket-alt me-2 text-info"></i>Recent Tickets</h6>
                    <a href="<?php echo ADMIN_URL; ?>/tickets" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentTickets)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-3 text-success"></i>
                        <p>No open tickets</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>" class="fw-bold text-decoration-none">#<?php echo $ticket['ticket_number']; ?></a></td>
                                    <td><?php echo htmlspecialchars(substr($ticket['subject'], 0, 20)); ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'open' => 'danger',
                                            'answered' => 'primary',
                                            'customer-reply' => 'warning',
                                            'on-hold' => 'secondary',
                                            'in-progress' => 'info',
                                            'closed' => 'success'
                                        ];
                                        $color = $statusColors[$ticket['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?> rounded-pill"><?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recently Added Clients & Overdue Invoices -->
<div class="row g-4 mt-2">
    <!-- New Clients -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0"><i class="fas fa-user-plus me-2 text-success"></i>Recently Added Clients</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($newClients)): ?>
                    <div class="text-center text-muted py-4">No new clients</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($newClients as $client): ?>
                        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                </div>
                                <small class="text-muted"><?php echo date('M d', strtotime($client['created_at'])); ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Overdue Invoices -->
    <div class="col-lg-6">
        <div class="card shadow-sm <?php echo count($overdueInvoices ?? []) > 0 ? 'border-danger' : ''; ?>">
            <div class="card-header <?php echo count($overdueInvoices ?? []) > 0 ? 'bg-danger text-white' : 'bg-light'; ?> border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Overdue Invoices</h6>
                    <?php if (count($overdueInvoices ?? []) > 0): ?>
                        <a href="<?php echo ADMIN_URL; ?>/invoices?status=overdue" class="btn btn-sm btn-light">View All</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($overdueInvoices)): ?>
                    <div class="text-center text-muted py-4">No overdue invoices</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                <?php foreach (array_slice($overdueInvoices, 0, 5) as $invoice): ?>
                                <tr class="table-danger">
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="fw-bold">
                                            #<?php echo $invoice['invoice_number']; ?>
                                        </a>
                                    </td>
                                    <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?php echo floor((time() - strtotime($invoice['due_date'])) / 86400); ?>d
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important; }
.bg-gradient-danger { background: linear-gradient(135deg, #eb3b5a 0%, #fc5c65 100%) !important; }
.stat-icon { opacity: 0.3; font-size: 2.5rem; }
.card { transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }
.text-white-50 { color: rgba(255, 255, 255, 0.7) !important; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
