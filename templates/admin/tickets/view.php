<?php
$page = 'tickets';
$title = 'Ticket #' . $ticket['ticket_number'];
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-ticket-alt me-2"></i>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
    </h1>
    <a href="<?php echo ADMIN_URL; ?>/tickets" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Tickets
    </a>
</div>

<div class="row g-4">
    <!-- Ticket Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Ticket Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td class="text-end">
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
                    </tr>
                    <tr>
                        <td class="text-muted">Priority</td>
                        <td class="text-end">
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
                    </tr>
                    <tr>
                        <td class="text-muted">Department</td>
                        <td class="text-end"><?php echo htmlspecialchars($ticket['department_name'] ?? 'General'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td class="text-end"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Reply</td>
                        <td class="text-end"><?php echo date('M d, Y H:i', strtotime($ticket['last_reply'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Client Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-user me-2"></i>Client Information</h6>
            </div>
            <div class="card-body">
                <?php if ($ticket['client_id']): ?>
                <p class="mb-1">
                    <strong>
                        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $ticket['client_id']; ?>">
                            <?php echo htmlspecialchars($ticket['client_name'] ?? 'N/A'); ?>
                        </a>
                    </strong>
                </p>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($ticket['client_email'] ?? ''); ?></p>
                <?php else: ?>
                <p class="mb-1"><strong><?php echo htmlspecialchars($ticket['name']); ?></strong></p>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($ticket['email']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($ticket['status'] !== 'closed'): ?>
                    <form method="POST" action="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>/close">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Close Ticket
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ticket Thread -->
    <div class="col-lg-8">
        <!-- Subject -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-comment me-2"></i><?php echo htmlspecialchars($ticket['subject']); ?></h6>
            </div>
            <div class="card-body">
                <div class="d-flex mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($ticket['name']); ?></strong>
                        <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></small>
                        <div class="mt-2">
                            <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Replies -->
        <?php foreach ($replies as $reply): ?>
        <div class="card mb-4 <?php echo $reply['admin_id'] ? 'border-success' : ''; ?>">
            <div class="card-body">
                <div class="d-flex">
                    <div class="rounded-circle <?php echo $reply['admin_id'] ? 'bg-success' : 'bg-primary'; ?> text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fas fa-<?php echo $reply['admin_id'] ? 'headset' : 'user'; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong><?php echo htmlspecialchars($reply['name']); ?></strong>
                        <?php if ($reply['admin_id']): ?>
                        <span class="badge bg-success ms-1">Staff</span>
                        <?php endif; ?>
                        <small class="text-muted ms-2"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                        <div class="mt-2">
                            <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Reply Form -->
        <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-reply me-2"></i>Post Reply</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo ADMIN_URL; ?>/tickets/<?php echo $ticket['id']; ?>/reply">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <textarea class="form-control" name="message" rows="6" placeholder="Type your reply here..." required></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <select class="form-select form-select-sm d-inline-block w-auto" name="status">
                                <option value="answered">Set to Answered</option>
                                <option value="on-hold">Set to On Hold</option>
                                <option value="in-progress">Set to In Progress</option>
                                <option value="closed">Close Ticket</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Post Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
