<?php
$page = 'clients';
$title = 'Clients';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-users me-2"></i>Clients</h1>
    <a href="<?php echo ADMIN_URL; ?>/clients/add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Client
    </a>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" placeholder="Search by name, email, or company..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Clients Table -->
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list me-2"></i>Client List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Services</th>
                        <th>Balance</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients['items'])): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No clients found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients['items'] as $client): ?>
                        <tr>
                            <td><?php echo $client['id']; ?></td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>">
                                    <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['company'] ?: '-'); ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'closed' => 'dark'
                                ];
                                $color = $statusColors[$client['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($client['status']); ?></span>
                            </td>
                            <td><?php echo $client['service_count'] ?? 0; ?></td>
                            <td>
                                <?php if (($client['credit'] ?? 0) > 0): ?>
                                    <span class="text-success">$<?php echo number_format($client['credit'], 2); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
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
