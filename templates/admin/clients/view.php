<?php
$page = 'clients';
$title = 'View Client';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-user me-2"></i>
        <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
        <?php
        $statusColors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'closed' => 'dark'
        ];
        $color = $statusColors[$client['status']] ?? 'secondary';
        ?>
        <span class="badge bg-<?php echo $color; ?> ms-2"><?php echo ucfirst($client['status']); ?></span>
    </h1>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>/edit" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit Client
        </a>
        <a href="<?php echo ADMIN_URL; ?>/clients" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Clients
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Client Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Client Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Client ID</td>
                        <td class="text-end fw-bold">#<?php echo $client['id']; ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td class="text-end"><a href="mailto:<?php echo $client['email']; ?>"><?php echo htmlspecialchars($client['email']); ?></a></td>
                    </tr>
                    <?php if ($client['company']): ?>
                    <tr>
                        <td class="text-muted">Company</td>
                        <td class="text-end"><?php echo htmlspecialchars($client['company']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Phone</td>
                        <td class="text-end"><?php echo htmlspecialchars($client['phone'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address</td>
                        <td class="text-end">
                            <?php echo htmlspecialchars($client['address1']); ?><br>
                            <?php if ($client['address2']) echo htmlspecialchars($client['address2']) . '<br>'; ?>
                            <?php echo htmlspecialchars($client['city'] . ', ' . $client['state'] . ' ' . $client['postcode']); ?><br>
                            <?php echo htmlspecialchars($client['country']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Credit Balance</td>
                        <td class="text-end text-success fw-bold">$<?php echo number_format($client['credit'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Login</td>
                        <td class="text-end"><?php echo $client['last_login'] ? date('M d, Y H:i', strtotime($client['last_login'])) : 'Never'; ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Registered</td>
                        <td class="text-end"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo ADMIN_URL; ?>/orders/add?client_id=<?php echo $client['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Create New Order
                    </a>
                    <a href="<?php echo ADMIN_URL; ?>/invoices/add?client_id=<?php echo $client['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice me-2"></i>Create Invoice
                    </a>
                    <a href="<?php echo ADMIN_URL; ?>/tickets/add?client_id=<?php echo $client['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-ticket-alt me-2"></i>Open Ticket
                    </a>
                    <a href="#" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#addCreditModal">
                        <i class="fas fa-dollar-sign me-2"></i>Add Credit
                    </a>
                    <a href="#" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#emailClientModal">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Services, Invoices, etc. -->
    <div class="col-lg-8">
        <!-- Services -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-cube me-2"></i>Services</h6>
                <span class="badge bg-primary"><?php echo count($services); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Next Due</th>
                                <th>Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No services</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['product_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($service['domain'] ?: '-'); ?></td>
                                    <td>
                                        <?php
                                        $svcColors = [
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'suspended' => 'danger',
                                            'terminated' => 'dark',
                                            'cancelled' => 'secondary'
                                        ];
                                        $svcColor = $svcColors[$service['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $svcColor; ?>"><?php echo ucfirst($service['status']); ?></span>
                                    </td>
                                    <td><?php echo $service['next_due'] ? date('M d, Y', strtotime($service['next_due'])) : '-'; ?></td>
                                    <td>$<?php echo number_format($service['amount'] ?? 0, 2); ?>/<?php echo $service['billing_cycle'] ?? 'mo'; ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/services/<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
        
        <!-- Domains -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-globe me-2"></i>Domains</h6>
                <span class="badge bg-primary"><?php echo count($domains); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Registrar</th>
                                <th>Status</th>
                                <th>Expiry</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($domains)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No domains</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($domains as $domain): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($domain['domain']); ?></td>
                                    <td><?php echo htmlspecialchars($domain['registrar'] ?: 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $domColors = [
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'expired' => 'danger',
                                            'transferred' => 'info'
                                        ];
                                        $domColor = $domColors[$domain['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $domColor; ?>"><?php echo ucfirst($domain['status']); ?></span>
                                    </td>
                                    <td><?php echo $domain['expiry_date'] ? date('M d, Y', strtotime($domain['expiry_date'])) : '-'; ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/domains/<?php echo $domain['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
        
        <!-- Invoices -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-file-invoice-dollar me-2"></i>Recent Invoices</h6>
                <a href="<?php echo ADMIN_URL; ?>/invoices?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
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
                                    <td colspan="6" class="text-center text-muted py-3">No invoices</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($invoices, 0, 5) as $invoice): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>"><?php echo htmlspecialchars($invoice['invoice_number']); ?></a></td>
                                    <td><?php echo date('M d, Y', strtotime($invoice['date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                                    <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                                    <td>
                                        <?php
                                        $invColors = [
                                            'paid' => 'success',
                                            'unpaid' => 'warning',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary',
                                            'refunded' => 'info'
                                        ];
                                        $invColor = $invColors[$invoice['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $invColor; ?>"><?php echo ucfirst($invoice['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/invoices/<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
        
        <!-- Tickets -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-ticket-alt me-2"></i>Recent Tickets</h6>
                <a href="<?php echo ADMIN_URL; ?>/tickets?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Last Reply</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No tickets</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($tickets, 0, 5) as $ticket): ?>
                                <tr>
                                    <td><a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>"><?php echo htmlspecialchars($ticket['ticket_number']); ?></a></td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['department_name'] ?? 'General'); ?></td>
                                    <td>
                                        <?php
                                        $tktColors = [
                                            'open' => 'success',
                                            'answered' => 'primary',
                                            'customer-reply' => 'warning',
                                            'on-hold' => 'secondary',
                                            'in-progress' => 'info',
                                            'closed' => 'dark'
                                        ];
                                        $tktColor = $tktColors[$ticket['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $tktColor; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($ticket['last_reply'])); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
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
</div>

<!-- Notes -->
<?php if ($client['notes']): ?>
<div class="card mt-4">
    <div class="card-header">
        <h6><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
    </div>
    <div class="card-body">
        <p class="mb-0"><?php echo nl2br(htmlspecialchars($client['notes'])); ?></p>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
