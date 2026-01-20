<?php
/**
 * OpenWHM Admin - Administrators Management
 */

use OpenWHM\Database;
use OpenWHM\Helper;

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    if (isset($_POST['save_admin'])) {
        $id = (int)$_POST['id'];
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'role' => $_POST['role'],
            'status' => $_POST['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Invalid email address');
            redirect('index.php?page=admins&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        
        // Check for duplicate username/email
        $existing = $db->fetch(
            "SELECT id FROM {$db->table('admins')} 
             WHERE (username = ? OR email = ?) AND id != ?",
            [$data['username'], $data['email'], $id]
        );
        
        if ($existing) {
            setFlash('error', 'Username or email already exists');
            redirect('index.php?page=admins&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        
        // Handle password
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                setFlash('error', 'Password must be at least 8 characters');
                redirect('index.php?page=admins&action=' . ($id ? 'edit&id=' . $id : 'new'));
            }
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        } elseif (!$id) {
            setFlash('error', 'Password is required for new administrators');
            redirect('index.php?page=admins&action=new');
        }
        
        if ($id) {
            $db->update('admins', $data, 'id = ?', [$id]);
            
            // Log activity
            $db->insert('activity_log', [
                'admin_id' => $_SESSION['admin_id'],
                'action' => 'admin_updated',
                'details' => json_encode(['admin_id' => $id, 'username' => $data['username']]),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            setFlash('success', 'Administrator updated successfully');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('admins', $data);
            $newId = $db->lastInsertId();
            
            // Log activity
            $db->insert('activity_log', [
                'admin_id' => $_SESSION['admin_id'],
                'action' => 'admin_created',
                'details' => json_encode(['admin_id' => $newId, 'username' => $data['username']]),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            setFlash('success', 'Administrator created successfully');
        }
        
        redirect('index.php?page=admins');
    }
    
    if (isset($_POST['delete_admin'])) {
        $id = (int)$_POST['id'];
        
        // Cannot delete yourself
        if ($id == $_SESSION['admin_id']) {
            setFlash('error', 'You cannot delete your own account');
            redirect('index.php?page=admins');
        }
        
        $admin = $db->fetch("SELECT * FROM {$db->table('admins')} WHERE id = ?", [$id]);
        
        if ($admin) {
            $db->query("DELETE FROM {$db->table('admins')} WHERE id = ?", [$id]);
            
            // Log activity
            $db->insert('activity_log', [
                'admin_id' => $_SESSION['admin_id'],
                'action' => 'admin_deleted',
                'details' => json_encode(['admin_id' => $id, 'username' => $admin['username']]),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            setFlash('success', 'Administrator deleted successfully');
        }
        
        redirect('index.php?page=admins');
    }
}

// Edit admin
if ($action === 'edit' && isset($_GET['id'])) {
    $admin = $db->fetch(
        "SELECT * FROM {$db->table('admins')} WHERE id = ?",
        [(int)$_GET['id']]
    );
    
    if (!$admin) {
        setFlash('error', 'Administrator not found');
        redirect('index.php?page=admins');
    }
}

// New admin
if ($action === 'new') {
    $admin = [
        'id' => 0,
        'username' => '',
        'email' => '',
        'first_name' => '',
        'last_name' => '',
        'role' => 'admin',
        'status' => 'active'
    ];
}
?>

<?php if ($action === 'list'): ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="fas fa-user-shield me-2"></i> Administrators</h1>
    <a href="index.php?page=admins&action=new" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Add Administrator
    </a>
</div>

<?php
$admins = $db->fetchAll("SELECT * FROM {$db->table('admins')} ORDER BY username");
?>

<div class="card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td>
                        <i class="fas fa-user-circle me-2 text-secondary"></i>
                        <?= htmlspecialchars($a['username']) ?>
                        <?php if ($a['id'] == $_SESSION['admin_id']): ?>
                        <span class="badge bg-info">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td>
                        <?php
                        $roleColors = [
                            'super_admin' => 'danger',
                            'admin' => 'primary',
                            'support' => 'info',
                            'billing' => 'success'
                        ];
                        $color = $roleColors[$a['role']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>">
                            <?= ucwords(str_replace('_', ' ', $a['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($a['status'] === 'active'): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($a['last_login']): ?>
                        <small><?= Helper::formatDateTime($a['last_login']) ?></small>
                        <?php else: ?>
                        <span class="text-muted">Never</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?page=admins&action=edit&id=<?= $a['id'] ?>" 
                           class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                        <form method="post" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this administrator?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" name="delete_admin" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Role Permissions Reference -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-key me-2"></i> Role Permissions</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Permission</th>
                        <th class="text-center">Super Admin</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Support</th>
                        <th class="text-center">Billing</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Manage Administrators</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                    </tr>
                    <tr>
                        <td>System Settings</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                    </tr>
                    <tr>
                        <td>Manage Clients</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                    </tr>
                    <tr>
                        <td>Support Tickets</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                    </tr>
                    <tr>
                        <td>Billing & Invoices</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                    </tr>
                    <tr>
                        <td>Services & Products</td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-success"><i class="fas fa-check"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                        <td class="text-center text-danger"><i class="fas fa-times"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Edit/New Admin Form -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-user-shield me-2"></i>
        <?= $action === 'new' ? 'Add Administrator' : 'Edit Administrator' ?>
    </h1>
    <a href="index.php?page=admins" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back
    </a>
</div>

<form method="post">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $admin['id'] ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required
                                   value="<?= htmlspecialchars($admin['username']) ?>"
                                   pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= htmlspecialchars($admin['email']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="<?= htmlspecialchars($admin['first_name']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="<?= htmlspecialchars($admin['last_name']) ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Password</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Password 
                                <?php if ($action === 'new'): ?>
                                <span class="text-danger">*</span>
                                <?php else: ?>
                                <span class="text-muted">(leave blank to keep current)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="password" class="form-control" 
                                   minlength="8" <?= $action === 'new' ? 'required' : '' ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" id="confirm_password" class="form-control" minlength="8">
                        </div>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Password must be at least 8 characters long
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" <?= $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : '' ?>>
                            <option value="super_admin" <?= $admin['role'] === 'super_admin' ? 'selected' : '' ?>>
                                Super Admin
                            </option>
                            <option value="admin" <?= $admin['role'] === 'admin' ? 'selected' : '' ?>>
                                Admin
                            </option>
                            <option value="support" <?= $admin['role'] === 'support' ? 'selected' : '' ?>>
                                Support
                            </option>
                            <option value="billing" <?= $admin['role'] === 'billing' ? 'selected' : '' ?>>
                                Billing
                            </option>
                        </select>
                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                        <input type="hidden" name="role" value="<?= $admin['role'] ?>">
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            You cannot change your own role
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" <?= $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : '' ?>>
                            <option value="active" <?= $admin['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $admin['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                        <input type="hidden" name="status" value="<?= $admin['status'] ?>">
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            You cannot change your own status
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($admin['id']): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Created:</strong><br>
                        <?= Helper::formatDateTime($admin['created_at']) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Last Updated:</strong><br>
                        <?= $admin['updated_at'] ? Helper::formatDateTime($admin['updated_at']) : 'Never' ?>
                    </p>
                    <p class="mb-0">
                        <strong>Last Login:</strong><br>
                        <?= $admin['last_login'] ? Helper::formatDateTime($admin['last_login']) : 'Never' ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="save_admin" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Save Administrator
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Password confirmation validation
document.querySelector('form').addEventListener('submit', function(e) {
    var password = document.querySelector('input[name="password"]').value;
    var confirm = document.getElementById('confirm_password').value;
    
    if (password && password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

<?php endif; ?>