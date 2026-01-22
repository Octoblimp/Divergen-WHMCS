<?php
$page = 'servers';
$title = 'Servers';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-server me-2"></i>Server Management</h1>
        <small class="text-muted">Manage hosting servers and accounts</small>
    </div>
    <a href="<?php echo ADMIN_URL; ?>/servers/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Server
    </a>
</div>

<!-- Servers Grid -->
<?php if (empty($servers)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-server fa-3x text-muted mb-3 d-block"></i>
            <h5>No servers configured</h5>
            <p class="text-muted mb-4">Add your first hosting server to get started</p>
            <a href="<?php echo ADMIN_URL; ?>/servers/create" class="btn btn-primary">Add Server</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($servers as $server): ?>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-<?php echo $server['active'] ? 'success' : 'warning'; ?>">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($server['name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($server['hostname']); ?>:<?php echo $server['port']; ?></small>
                    </div>
                    <span class="badge bg-<?php echo $server['active'] ? 'success' : 'warning'; ?>">
                        <?php echo $server['active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Type</small>
                            <div class="fw-bold"><?php echo strtoupper($server['type']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Accounts</small>
                            <div class="fw-bold"><?php echo $server['account_count']; ?> / <?php echo $server['max_accounts'] > 0 ? $server['max_accounts'] : '∞'; ?></div>
                        </div>
                    </div>
                    
                    <div class="progress mb-3" style="height: 5px;">
                        <?php 
                        $percentage = $server['max_accounts'] > 0 ? round(($server['account_count'] / $server['max_accounts']) * 100) : 0;
                        $colorClass = $percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?php echo $colorClass; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="<?php echo ADMIN_URL; ?>/servers/<?php echo $server['id']; ?>/edit" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-sm btn-outline-info w-100" onclick="testConnection(<?php echo $server['id']; ?>)">
                                <i class="fas fa-plug me-1"></i>Test
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <small class="text-muted">
                        Created: <?php echo date('M d, Y', strtotime($server['created_at'])); ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Test Connection Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testResult">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Testing...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection(serverId) {
    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    modal.show();
    
    fetch('<?php echo ADMIN_URL; ?>/servers/' + serverId + '/test')
        .then(r => r.json())
        .then(data => {
            const resultDiv = document.getElementById('testResult');
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Connection successful!</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Connection failed: ' + (data.error || 'Unknown error') + '</div>';
            }
        })
        .catch(e => {
            document.getElementById('testResult').innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
        });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
