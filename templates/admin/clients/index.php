<?php
$page = 'clients';
$title = 'Clients';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-users me-2"></i>Clients Management</h1>
        <small class="text-muted">Manage all client accounts and information</small>
    </div>
    <a href="<?php echo ADMIN_URL; ?>/clients/add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Client
    </a>
</div>

<!-- Advanced Search & Filters -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-muted small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Name, email, or company..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo ($status ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($status ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="closed" <?php echo ($status ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
            <?php if ($search || ($status ?? '')): ?>
            <div class="col-md-2">
                <a href="?page=1" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Clients Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Clients</small>
                <div class="h5 fw-bold"><?php echo $clients['total'] ?? 0; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-success">Active</small>
                <div class="h5 fw-bold text-success">
                    <?php echo $this->db->fetch("SELECT COUNT(*) as count FROM owh_clients WHERE status = 'active'")['count'] ?? 0; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-warning">Inactive</small>
                <div class="h5 fw-bold text-warning">
                    <?php echo $this->db->fetch("SELECT COUNT(*) as count FROM owh_clients WHERE status = 'inactive'")['count'] ?? 0; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-danger">Closed</small>
                <div class="h5 fw-bold text-danger">
                    <?php echo $this->db->fetch("SELECT COUNT(*) as count FROM owh_clients WHERE status = 'closed'")['count'] ?? 0; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clients Table -->
<div class="card shadow-sm">
    <div class="card-header bg-light border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Client List</h6>
            <small class="text-muted">Showing <?php echo count($clients['items'] ?? []); ?> of <?php echo $clients['total'] ?? 0; ?> clients</small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th class="text-center">Services</th>
                        <th class="text-right">Credit</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients['items'])): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                                No clients found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients['items'] as $client): ?>
                        <tr class="border-bottom">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2" style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo array_rand(array_flip(['#667eea', '#764ba2', '#f093fb', '#f5576c', '#fa709a', '#fee140'])); ?>; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                        <?php echo substr($client['firstname'], 0, 1) . substr($client['lastname'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">ID: <?php echo $client['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo $client['email']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($client['email']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($client['company'] ?: '-'); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'warning',
                                    'closed' => 'danger'
                                ];
                                $statusIcons = [
                                    'active' => 'check-circle',
                                    'inactive' => 'pause-circle',
                                    'closed' => 'times-circle'
                                ];
                                $color = $statusColors[$client['status']] ?? 'secondary';
                                $icon = $statusIcons[$client['status']] ?? 'info-circle';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <i class="fas fa-<?php echo $icon; ?> me-1"></i><?php echo ucfirst($client['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">
                                    <?php echo $client['service_count'] ?? 0; ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <?php if (($client['credit'] ?? 0) > 0): ?>
                                    <span class="text-success fw-bold">$<?php echo number_format($client['credit'], 2); ?></span>
                                <?php elseif (($client['credit'] ?? 0) < 0): ?>
                                    <span class="text-danger fw-bold">-$<?php echo number_format(abs($client['credit']), 2); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>" class="btn btn-outline-primary" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button id="btnGroupDrop<?php echo $client['id']; ?>" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="More">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop<?php echo $client['id']; ?>">
                                            <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/invoices?client_id=<?php echo $client['id']; ?>"><i class="fas fa-file-invoice me-2"></i>View Invoices</a></li>
                                            <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/orders?client_id=<?php echo $client['id']; ?>"><i class="fas fa-shopping-cart me-2"></i>View Orders</a></li>
                                            <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/tickets?client_id=<?php echo $client['id']; ?>"><i class="fas fa-ticket-alt me-2"></i>View Tickets</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="mailto:<?php echo $client['email']; ?>"><i class="fas fa-envelope me-2"></i>Send Email</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($clients['items']) && $clients['total_pages'] > 1): ?>
    <div class="card-footer bg-light border-top">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0 justify-content-center">
                <?php if ($clients['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $clients['current_page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $clients['current_page'] - 2);
                $end = min($clients['total_pages'], $clients['current_page'] + 2);
                if ($start > 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $i === $clients['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($end < $clients['total_pages']): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                
                <?php if ($clients['current_page'] < $clients['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $clients['current_page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $clients['total_pages']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($clients['items']) && $clients['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $clients['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $clients['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
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
