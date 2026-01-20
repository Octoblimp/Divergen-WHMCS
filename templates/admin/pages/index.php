<?php
/**
 * Admin Pages List
 */
$this->layout = 'admin.layouts.main';
$title = 'Pages';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Pages</h1>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/pages/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Page
        </a>
    </div>
</div>

<?php $this->flashMessages(); ?>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Menu</th>
                    <th>Updated</th>
                    <th width="200">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No pages found.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($pages as $page): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($page['title']); ?></strong>
                        </td>
                        <td>
                            <code>/<?php echo htmlspecialchars($page['slug']); ?></code>
                        </td>
                        <td><?php echo ucfirst($page['template']); ?></td>
                        <td>
                            <?php
                            $statusClass = $page['status'] === 'published' ? 'success' : 'warning';
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($page['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($page['show_in_menu']): ?>
                            <span class="badge bg-info"><?php echo $page['menu_order']; ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($page['updated_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo ADMIN_URL; ?>/pages/builder/<?php echo $page['id']; ?>" 
                                   class="btn btn-outline-primary" title="Page Builder">
                                    <i class="fas fa-cube"></i>
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/pages/edit/<?php echo $page['id']; ?>" 
                                   class="btn btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/pages/preview/<?php echo $page['id']; ?>" 
                                   class="btn btn-outline-info" title="Preview" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/<?php echo $page['slug']; ?>" 
                                   class="btn btn-outline-success" title="View Live" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/pages/delete/<?php echo $page['id']; ?>" 
                                   class="btn btn-outline-danger" title="Delete"
                                   onclick="return confirm('Are you sure you want to delete this page?');">
                                    <i class="fas fa-trash"></i>
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

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
