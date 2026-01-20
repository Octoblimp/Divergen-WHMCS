<?php
$page = 'tickets';
$title = 'Support Tickets';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-life-ring me-2"></i>Support Tickets</h2>
    <a href="<?php echo CLIENT_URL; ?>/tickets/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Open New Ticket
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Tickets</h5>
                                <p class="text-muted mb-3">You haven't opened any support tickets yet.</p>
                                <a href="<?php echo CLIENT_URL; ?>/tickets/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Open Your First Ticket
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="<?php echo $ticket['status'] === 'answered' ? 'table-info' : ''; ?>">
                            <td>
                                <a href="<?php echo CLIENT_URL; ?>/tickets/<?php echo $ticket['id']; ?>">
                                    <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['department_name'] ?? 'General'); ?></td>
                            <td>
                                <?php
                                $colors = [
                                    'open' => 'success',
                                    'answered' => 'primary',
                                    'customer-reply' => 'warning',
                                    'on-hold' => 'secondary',
                                    'in-progress' => 'info',
                                    'closed' => 'dark'
                                ];
                                $color = $colors[$ticket['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($ticket['last_reply'])); ?></td>
                            <td>
                                <a href="<?php echo CLIENT_URL; ?>/tickets/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
