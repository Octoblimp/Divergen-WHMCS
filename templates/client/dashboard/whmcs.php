<?php
/**
 * Client Dashboard - WHMCS Style
 */
$this->layout = 'client.layouts.whmcs';
$title = 'Client Area';
ob_start();
?>

<!-- Welcome -->
<h1 class="page-title">Welcome Back, <?php echo htmlspecialchars($client['firstname'] ?? 'User'); ?></h1>
<nav class="breadcrumb-whmcs">
    <a href="<?php echo CLIENT_URL; ?>">Portal Home</a> / Client Area
</nav>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-cube"></i></div>
        <div class="stat-number"><?php echo $stats['services'] ?? 0; ?></div>
        <div class="stat-label">Services</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-number"><?php echo $stats['domains'] ?? 0; ?></div>
        <div class="stat-label">Domains</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-comments"></i></div>
        <div class="stat-number"><?php echo $stats['tickets'] ?? 0; ?></div>
        <div class="stat-label">Tickets</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="stat-number"><?php echo $stats['invoices'] ?? 0; ?></div>
        <div class="stat-label">Invoices</div>
    </div>
</div>

<!-- Knowledgebase Search -->
<div class="kb-search">
    <form action="<?php echo BASE_URL; ?>/knowledgebase/search" method="GET">
        <div class="input-group">
            <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control border-0" name="q" placeholder="Enter a question here to search our knowledgebase for answers...">
        </div>
    </form>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-6">
        <!-- Knowledgebase -->
        <div class="panel-whmcs">
            <div class="panel-whmcs-header">
                <h4><i class="fas fa-book"></i> Knowledgebase</h4>
                <span class="badge badge-whmcs badge-articles"><?php echo $kbArticleCount ?? 0; ?> Articles</span>
            </div>
            <div class="panel-whmcs-body">
                <ul>
                    <li><a href="#"><i class="fas fa-question-circle"></i> FAQ - Frequently Asked Questions</a></li>
                    <li><a href="#"><i class="fas fa-upload"></i> Upload Files</a></li>
                    <li><a href="#"><i class="fas fa-code"></i> API Documentation</a></li>
                    <li><a href="#"><i class="fas fa-chart-line"></i> Top Articles</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Register Domain -->
        <div class="domain-search-panel">
            <h4><i class="fas fa-globe"></i> Register a New Domain</h4>
            <form action="<?php echo BASE_URL; ?>/domains/search" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="domain" placeholder="Enter domain name...">
                    <button type="submit" class="btn btn-register">Register</button>
                    <a href="<?php echo BASE_URL; ?>/domains/transfer" class="btn btn-transfer">Transfer</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-lg-6">
        <!-- Overdue Invoices -->
        <?php if (!empty($overdueInvoices)): ?>
        <div class="overdue-alert mb-4">
            <div class="overdue-header">
                <h4><i class="fas fa-exclamation-triangle"></i> Overdue Invoices</h4>
                <a href="<?php echo CLIENT_URL; ?>/invoices?status=overdue" class="btn btn-pay-now">Pay Now</a>
            </div>
            <p class="mb-0">
                You have <strong><?php echo count($overdueInvoices); ?></strong> overdue invoice(s) with a total balance due of 
                <strong>$<?php echo number_format(array_sum(array_column($overdueInvoices, 'total')), 2); ?> USD</strong>. 
                Pay them now to avoid any interruptions in service.
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Recent News -->
        <div class="panel-whmcs">
            <div class="panel-whmcs-header">
                <h4><i class="fas fa-newspaper"></i> Recent News</h4>
                <a href="<?php echo BASE_URL; ?>/announcements" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="panel-whmcs-body">
                <?php if (!empty($announcements)): ?>
                    <?php foreach (array_slice($announcements, 0, 3) as $announcement): ?>
                    <div class="news-item">
                        <h5><a href="<?php echo BASE_URL; ?>/announcements/<?php echo $announcement['id']; ?>"><?php echo htmlspecialchars($announcement['title']); ?></a></h5>
                        <div class="news-date"><?php echo date('d/m/Y', strtotime($announcement['created_at'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="news-item">
                        <h5>Welcome to <?php echo COMPANY_NAME; ?>!</h5>
                        <div class="news-date"><?php echo date('d/m/Y'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="panel-whmcs mt-4">
    <div class="panel-whmcs-header">
        <h4><i class="fas fa-history"></i> Your Active Products/Services</h4>
    </div>
    <div class="panel-whmcs-body">
        <?php if (!empty($services)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product/Service</th>
                        <th>Pricing</th>
                        <th>Next Due Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($services, 0, 5) as $service): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($service['product_name'] ?? 'Service'); ?></strong>
                            <?php if ($service['domain']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($service['domain']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($service['amount'], 2); ?> / <?php echo ucfirst($service['billing_cycle']); ?></td>
                        <td><?php echo $service['next_due'] ? date('d/m/Y', strtotime($service['next_due'])) : 'N/A'; ?></td>
                        <td>
                            <?php
                            $statusClass = 'secondary';
                            if ($service['status'] === 'active') $statusClass = 'success';
                            elseif ($service['status'] === 'suspended') $statusClass = 'warning';
                            elseif ($service['status'] === 'terminated') $statusClass = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($service['status']); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo CLIENT_URL; ?>/services/<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">You don't have any active services. <a href="<?php echo BASE_URL; ?>/hosting">Order now!</a></p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/whmcs.php';
?>
