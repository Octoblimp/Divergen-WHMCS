<?php
$page = 'tickets';
$title = 'Open Support Ticket';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>Open Support Ticket</h2>
    <a href="<?php echo CLIENT_URL; ?>/tickets" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Tickets
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?php echo CLIENT_URL; ?>/tickets/store">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        
                        <?php if (!empty($services)): ?>
                        <div class="col-12">
                            <label class="form-label">Related Service</label>
                            <select class="form-select" name="service_id">
                                <option value="">None</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>">
                                    <?php echo htmlspecialchars($service['product_name']); ?>
                                    <?php if ($service['domain']): ?> - <?php echo htmlspecialchars($service['domain']); ?><?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-12">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required placeholder="Brief description of your issue">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="8" required placeholder="Please describe your issue in detail..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Be specific</strong> - Include error messages and steps to reproduce the issue.
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Choose the right department</strong> - This helps us route your ticket faster.
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Include relevant details</strong> - Domain names, usernames, order numbers, etc.
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Check our knowledge base</strong> - Your question might already be answered!
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
