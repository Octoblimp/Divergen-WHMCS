<?php
$page = 'dashboard';
$title = 'Dashboard';
?>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1">Welcome back, <?php echo htmlspecialchars($client['firstname']); ?>!</h2>
        <p class="text-muted mb-0">Here's an overview of your account</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card primary">
            <div class="stat-value"><?php echo $stats['active_services']; ?></div>
            <div class="stat-label">Active Services</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card info">
            <div class="stat-value"><?php echo $stats['active_domains']; ?></div>
            <div class="stat-label">Active Domains</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card warning">
            <div class="stat-value"><?php echo $stats['unpaid_invoices']; ?></div>
            <div class="stat-label">Unpaid Invoices</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card success">
            <div class="stat-value">$<?php echo number_format($stats['credit'], 2); ?></div>
            <div class="stat-label">Credit Balance</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/order" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Order New Service
                    </a>
                    <a href="<?php echo CLIENT_URL; ?>/tickets/create" class="btn btn-outline-primary">
                        <i class="fas fa-headset me-2"></i>Open Support Ticket
                    </a>
                    <a href="<?php echo CLIENT_URL; ?>/invoices" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice-dollar me-2"></i>View Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Services -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-cube me-2"></i>Your Services</h6>
                <a href="<?php echo CLIENT_URL; ?>/services" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product/Service</th>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Next Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                        No services yet. <a href="<?php echo BASE_URL; ?>/order">Order one now!</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($services, 0, 5) as $service): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo CLIENT_URL; ?>/services/<?php echo $service['id']; ?>">
                                            <?php echo htmlspecialchars($service['product_name'] ?? 'Service #' . $service['id']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($service['domain'] ?: '-'); ?></td>
                                    <td>
                                        <?php
                                        $colors = [
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'suspended' => 'danger',
                                            'terminated' => 'dark',
                                            'cancelled' => 'secondary'
                                        ];
                                        $color = $colors[$service['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($service['status']); ?></span>
                                    </td>
                                    <td><?php echo $service['next_due'] ? date('M d, Y', strtotime($service['next_due'])) : '-'; ?></td>
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

<!-- Second Row -->
<div class="row g-4 mt-1">
    <!-- Recent Invoices -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-file-invoice-dollar me-2"></i>Recent Invoices</h6>
                <a href="<?php echo CLIENT_URL; ?>/invoices" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Due Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No invoices</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo CLIENT_URL; ?>/invoices/<?php echo $invoice['id']; ?>">
                                            <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                                    <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                                    <td>
                                        <?php
                                        $invColors = [
                                            'paid' => 'success',
                                            'unpaid' => 'warning',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $isOverdue = $invoice['status'] === 'unpaid' && strtotime($invoice['due_date']) < time();
                                        $status = $isOverdue ? 'overdue' : $invoice['status'];
                                        $invColor = $invColors[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $invColor; ?>"><?php echo ucfirst($status); ?></span>
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
                <h6><i class="fas fa-life-ring me-2"></i>Open Tickets</h6>
                <a href="<?php echo CLIENT_URL; ?>/tickets/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>New Ticket
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                        No open tickets
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo CLIENT_URL; ?>/tickets/<?php echo $ticket['id']; ?>">
                                            <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td>
                                        <?php
                                        $tktColors = [
                                            'open' => 'success',
                                            'answered' => 'primary',
                                            'customer-reply' => 'warning',
                                            'on-hold' => 'secondary',
                                            'in-progress' => 'info'
                                        ];
                                        $tktColor = $tktColors[$ticket['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $tktColor; ?>"><?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?></span>
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
