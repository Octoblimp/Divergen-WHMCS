<?php
/**
 * OpenWHM Admin - Activity Logs
 */

use OpenWHM\Database;
use OpenWHM\Helper;

$db = Database::getInstance();

// Pagination
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filters
$filterAdmin = $_GET['admin_id'] ?? '';
$filterAction = $_GET['action_type'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Build query
$where = ['1=1'];
$params = [];

if ($filterAdmin) {
    $where[] = 'l.admin_id = ?';
    $params[] = $filterAdmin;
}

if ($filterAction) {
    $where[] = 'l.action = ?';
    $params[] = $filterAction;
}

if ($filterDateFrom) {
    $where[] = 'l.created_at >= ?';
    $params[] = $filterDateFrom . ' 00:00:00';
}

if ($filterDateTo) {
    $where[] = 'l.created_at <= ?';
    $params[] = $filterDateTo . ' 23:59:59';
}

$whereClause = implode(' AND ', $where);

// Get total count
$total = $db->fetch(
    "SELECT COUNT(*) as count FROM {$db->table('activity_log')} l WHERE {$whereClause}",
    $params
)['count'];
$totalPages = ceil($total / $perPage);

// Get logs
$logs = $db->fetchAll(
    "SELECT l.*, a.username, a.first_name, a.last_name
     FROM {$db->table('activity_log')} l
     LEFT JOIN {$db->table('admins')} a ON l.admin_id = a.id
     WHERE {$whereClause}
     ORDER BY l.created_at DESC
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);

// Get unique admins for filter
$admins = $db->fetchAll("SELECT id, username FROM {$db->table('admins')} ORDER BY username");

// Get unique action types for filter
$actionTypes = $db->fetchAll("SELECT DISTINCT action FROM {$db->table('activity_log')} ORDER BY action");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="fas fa-history me-2"></i> Activity Log</h1>
    <form method="post" class="d-inline" onsubmit="return confirm('Clear all activity logs?')">
        <?= csrfField() ?>
        <button type="submit" name="clear_logs" class="btn btn-danger" 
                <?= $total == 0 ? 'disabled' : '' ?>>
            <i class="fas fa-trash me-2"></i> Clear All Logs
        </button>
    </form>
</div>

<?php
// Handle clear logs
if (isset($_POST['clear_logs'])) {
    $db->query("TRUNCATE TABLE {$db->table('activity_log')}");
    setFlash('success', 'Activity logs cleared');
    redirect('index.php?page=activity_log');
}
?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="activity_log">
            
            <div class="col-md-3">
                <label class="form-label">Administrator</label>
                <select name="admin_id" class="form-select">
                    <option value="">All Admins</option>
                    <?php foreach ($admins as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $filterAdmin == $a['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['username']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Action Type</label>
                <select name="action_type" class="form-select">
                    <option value="">All Actions</option>
                    <?php foreach ($actionTypes as $action): ?>
                    <option value="<?= htmlspecialchars($action['action']) ?>" 
                            <?= $filterAction === $action['action'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $action['action']))) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo) ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="index.php?page=activity_log" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">No activity logs found.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="150">Date/Time</th>
                        <th width="150">Administrator</th>
                        <th width="150">Action</th>
                        <th>Details</th>
                        <th width="120">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <small><?= Helper::formatDateTime($log['created_at']) ?></small>
                        </td>
                        <td>
                            <?php if ($log['admin_id']): ?>
                            <i class="fas fa-user-shield me-1 text-secondary"></i>
                            <?= htmlspecialchars($log['username'] ?: 'Deleted Admin') ?>
                            <?php else: ?>
                            <span class="text-muted">System</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $actionColors = [
                                'login' => 'success',
                                'logout' => 'secondary',
                                'client_created' => 'info',
                                'client_updated' => 'primary',
                                'client_deleted' => 'danger',
                                'invoice_created' => 'info',
                                'invoice_paid' => 'success',
                                'service_created' => 'info',
                                'service_suspended' => 'warning',
                                'service_terminated' => 'danger',
                                'ticket_replied' => 'primary',
                                'admin_created' => 'info',
                                'admin_updated' => 'primary',
                                'admin_deleted' => 'danger',
                                'settings_updated' => 'warning'
                            ];
                            $color = $actionColors[$log['action']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))) ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $details = json_decode($log['details'], true);
                            if ($details): 
                            ?>
                            <div class="small">
                                <?php foreach ($details as $key => $value): ?>
                                <span class="me-3">
                                    <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                                    <?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=activity_log&p=<?= $page - 1 ?>&admin_id=<?= $filterAdmin ?>&action_type=<?= $filterAction ?>">
                        &laquo; Previous
                    </a>
                </li>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++): 
                ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=activity_log&p=<?= $i ?>&admin_id=<?= $filterAdmin ?>&action_type=<?= $filterAction ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=activity_log&p=<?= $page + 1 ?>&admin_id=<?= $filterAdmin ?>&action_type=<?= $filterAction ?>">
                        Next &raquo;
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <p class="text-center text-muted mt-2">
            Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $total) ?> of <?= number_format($total) ?> logs
        </p>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>