<?php
/**
 * Admin Page Create/Edit Form
 */
$isEdit = isset($page);
$data = $isEdit ? $page : ($data ?? []);

$this->layout = 'admin.layouts.main';
$title = $isEdit ? 'Edit Page' : 'Create Page';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?php echo $isEdit ? 'Edit' : 'Create'; ?> Page</h1>
    <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Pages
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo ADMIN_URL; ?>/pages/<?php echo $isEdit ? 'edit/' . $page['id'] : 'create'; ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Page Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Page Title *</label>
                        <input type="text" class="form-control" name="title" id="pageTitle"
                               value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo BASE_URL; ?>/</span>
                            <input type="text" class="form-control" name="slug" id="pageSlug"
                                   value="<?php echo htmlspecialchars($data['slug'] ?? ''); ?>" 
                                   placeholder="auto-generated-from-title">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Page Content</label>
                        <textarea class="form-control" name="content" id="pageContent" rows="15"><?php echo htmlspecialchars($data['content'] ?? ''); ?></textarea>
                        <div class="text-muted small mt-1">
                            For advanced layouts, use the <a href="<?php echo ADMIN_URL; ?>/pages/builder/<?php echo $data['id'] ?? 'NEW'; ?>">Page Builder</a> after saving.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">SEO Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" class="form-control" name="meta_title"
                               value="<?php echo htmlspecialchars($data['meta_title'] ?? ''); ?>" 
                               placeholder="Defaults to page title">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea class="form-control" name="meta_description" rows="3"
                                  maxlength="160"><?php echo htmlspecialchars($data['meta_description'] ?? ''); ?></textarea>
                        <div class="text-muted small">Recommended: 150-160 characters</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Publish</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="draft" <?php echo ($data['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($data['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Template</label>
                        <select class="form-select" name="template">
                            <?php foreach ($templates as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($data['template'] ?? 'default') === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="showInMenu" name="show_in_menu" 
                               <?php echo ($data['show_in_menu'] ?? false) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="showInMenu">Show in Navigation Menu</label>
                    </div>
                    
                    <div class="mb-3" id="menuOrderGroup" style="<?php echo ($data['show_in_menu'] ?? false) ? '' : 'display:none;'; ?>">
                        <label class="form-label">Menu Order</label>
                        <input type="number" class="form-control" name="menu_order" 
                               value="<?php echo $data['menu_order'] ?? 0; ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="generateFile" name="generate_file">
                        <label class="form-check-label" for="generateFile">Generate static PHP file</label>
                        <div class="text-muted small">Creates /pages/slug.php for faster loading</div>
                    </div>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i> <?php echo $isEdit ? 'Update Page' : 'Create Page'; ?>
                    </button>
                </div>
            </div>
            
            <?php if ($isEdit): ?>
            <div class="card">
                <div class="card-body">
                    <a href="<?php echo ADMIN_URL; ?>/pages/builder/<?php echo $page['id']; ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-cube me-1"></i> Open Page Builder
                    </a>
                    <a href="<?php echo BASE_URL; ?>/<?php echo $page['slug']; ?>" class="btn btn-outline-secondary w-100" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i> View Page
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
<script>
// Auto-generate slug from title
document.getElementById('pageTitle').addEventListener('blur', function() {
    const slugField = document.getElementById('pageSlug');
    if (!slugField.value) {
        slugField.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .trim();
    }
});

// Toggle menu order field
document.getElementById('showInMenu').addEventListener('change', function() {
    document.getElementById('menuOrderGroup').style.display = this.checked ? 'block' : 'none';
});

// Initialize TinyMCE
tinymce.init({
    selector: '#pageContent',
    height: 400,
    menubar: true,
    plugins: 'lists link image code table hr anchor',
    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
});
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
