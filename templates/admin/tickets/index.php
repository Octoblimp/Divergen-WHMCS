<?php
$page = 'tickets';
$title = 'Support Tickets';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-ticket-alt me-2"></i>Support Tickets</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Open Tickets</option>
                    <option value="all" <?php echo ($status ?? '') === 'all' ? 'selected' : ''; ?>>All Tickets</option>
                    <option value="open" <?php echo ($status ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="answered" <?php echo ($status ?? '') === 'answered' ? 'selected' : ''; ?>>Answered</option>
                    <option value="customer-reply" <?php echo ($status ?? '') === 'customer-reply' ? 'selected' : ''; ?>>Awaiting Reply</option>
                    <option value="on-hold" <?php echo ($status ?? '') === 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="closed" <?php echo ($status ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
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

<!-- Tickets Table -->
<div class="card">
    <div class="card-header">
        <h6><i class="fas fa-list me-2"></i>Ticket List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Client</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets['items'])): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No tickets found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets['items'] as $ticket): ?>
                        <tr class="<?php echo $ticket['status'] === 'customer-reply' ? 'table-warning' : ''; ?>">
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>">
                                    <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td>
                                <?php if ($ticket['client_id']): ?>
                                <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $ticket['client_id']; ?>">
                                    <?php echo htmlspecialchars($ticket['client_name'] ?? 'N/A'); ?>
                                </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($ticket['name']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['department_name'] ?? 'General'); ?></td>
                            <td>
                                <?php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'primary',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                                $prColor = $priorityColors[$ticket['priority']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $prColor; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
                            </td>
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
                                $stColor = $statusColors[$ticket['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $stColor; ?>"><?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?></span>
                            </td>
                            <td>
                                <?php 
                                $lastReply = strtotime($ticket['last_reply']);
                                $now = time();
                                $diff = $now - $lastReply;
                                
                                if ($diff < 3600) {
                                    echo floor($diff / 60) . ' mins ago';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' hours ago';
                                } else {
                                    echo date('M d, Y', $lastReply);
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-reply me-1"></i>Reply
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($tickets['items']) && $tickets['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $tickets['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $tickets['current_page'] ? 'active' : ''; ?>">
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
