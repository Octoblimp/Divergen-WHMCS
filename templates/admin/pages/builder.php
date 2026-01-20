<?php
/**
 * Page Builder - Visual Editor
 */
$blocks = json_decode($page['blocks'] ?? '[]', true) ?: [];
$blockTypesJson = json_encode($blockTypes);
$blocksJson = json_encode($blocks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Builder - <?php echo htmlspecialchars($page['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 300px;
            --header-height: 56px;
        }
        
        body {
            padding-top: var(--header-height);
            overflow: hidden;
            height: 100vh;
        }
        
        /* Header */
        .builder-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: #2c3e50;
            color: #fff;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }
        
        .builder-header .page-title {
            font-size: 16px;
            margin: 0;
        }
        
        .builder-header .actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }
        
        /* Sidebar */
        .builder-sidebar {
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: 0;
            width: var(--sidebar-width);
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            z-index: 100;
        }
        
        .sidebar-section {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .sidebar-section h4 {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 15px;
        }
        
        /* Block Types */
        .block-types {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .block-type {
            padding: 15px 10px;
            text-align: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: grab;
            transition: all 0.2s;
        }
        
        .block-type:hover {
            border-color: #5bc0de;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .block-type i {
            font-size: 24px;
            color: #5bc0de;
            display: block;
            margin-bottom: 8px;
        }
        
        .block-type span {
            font-size: 11px;
            color: #666;
        }
        
        /* Canvas */
        .builder-canvas {
            position: fixed;
            left: var(--sidebar-width);
            top: var(--header-height);
            right: 0;
            bottom: 0;
            overflow-y: auto;
            background: #e9ecef;
            padding: 20px;
        }
        
        .canvas-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            min-height: 600px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        /* Blocks in canvas */
        .builder-block {
            position: relative;
            min-height: 80px;
            border: 2px dashed transparent;
            transition: all 0.2s;
        }
        
        .builder-block:hover {
            border-color: #5bc0de;
        }
        
        .builder-block.selected {
            border-color: #5bc0de;
            border-style: solid;
        }
        
        .block-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: none;
            gap: 5px;
            z-index: 10;
        }
        
        .builder-block:hover .block-actions {
            display: flex;
        }
        
        .block-actions button {
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-move {
            background: #5bc0de;
            color: #fff;
            cursor: grab;
        }
        
        .btn-edit {
            background: #f0ad4e;
            color: #fff;
        }
        
        .btn-delete {
            background: #d9534f;
            color: #fff;
        }
        
        .block-label {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #5bc0de;
            color: #fff;
            padding: 2px 8px;
            font-size: 11px;
            border-radius: 3px;
            display: none;
        }
        
        .builder-block:hover .block-label {
            display: block;
        }
        
        /* Drop zones */
        .drop-zone {
            height: 4px;
            background: transparent;
            transition: all 0.2s;
        }
        
        .drop-zone.active {
            height: 50px;
            background: rgba(91, 192, 222, 0.2);
            border: 2px dashed #5bc0de;
            margin: 10px 0;
        }
        
        /* Empty state */
        .canvas-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 300px;
            color: #999;
        }
        
        .canvas-empty i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        /* Edit Panel */
        .edit-panel {
            position: fixed;
            right: -400px;
            top: var(--header-height);
            bottom: 0;
            width: 400px;
            background: #fff;
            border-left: 1px solid #ddd;
            z-index: 200;
            transition: right 0.3s;
            overflow-y: auto;
        }
        
        .edit-panel.open {
            right: 0;
        }
        
        .edit-panel-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .edit-panel-body {
            padding: 20px;
        }
        
        /* Responsive preview */
        .preview-mode-desktop .canvas-container { max-width: 1200px; }
        .preview-mode-tablet .canvas-container { max-width: 768px; }
        .preview-mode-mobile .canvas-container { max-width: 375px; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="builder-header">
        <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-sm btn-outline-light me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="page-title"><?php echo htmlspecialchars($page['title']); ?></h1>
        
        <div class="actions">
            <div class="btn-group btn-group-sm me-2">
                <button class="btn btn-outline-light preview-btn active" data-mode="desktop" title="Desktop">
                    <i class="fas fa-desktop"></i>
                </button>
                <button class="btn btn-outline-light preview-btn" data-mode="tablet" title="Tablet">
                    <i class="fas fa-tablet-alt"></i>
                </button>
                <button class="btn btn-outline-light preview-btn" data-mode="mobile" title="Mobile">
                    <i class="fas fa-mobile-alt"></i>
                </button>
            </div>
            <button class="btn btn-sm btn-outline-light" id="previewBtn">
                <i class="fas fa-eye me-1"></i> Preview
            </button>
            <button class="btn btn-sm btn-success" id="saveBtn">
                <i class="fas fa-save me-1"></i> Save
            </button>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="builder-sidebar">
        <div class="sidebar-section">
            <h4>Add Block</h4>
            <div class="block-types">
                <?php foreach ($blockTypes as $type => $config): ?>
                <div class="block-type" draggable="true" data-type="<?php echo $type; ?>">
                    <i class="<?php echo $config['icon']; ?>"></i>
                    <span><?php echo $config['name']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h4>Page Settings</h4>
            <div class="mb-3">
                <label class="form-label small">Background Color</label>
                <input type="color" class="form-control form-control-color" id="bgColor" value="#ffffff">
            </div>
            <div class="mb-3">
                <label class="form-label small">Content Width</label>
                <select class="form-select form-select-sm" id="contentWidth">
                    <option value="1200">Default (1200px)</option>
                    <option value="960">Narrow (960px)</option>
                    <option value="100%">Full Width</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Canvas -->
    <div class="builder-canvas preview-mode-desktop">
        <div class="canvas-container" id="canvas">
            <div class="drop-zone" data-index="0"></div>
            
            <?php if (empty($blocks)): ?>
            <div class="canvas-empty" id="emptyState">
                <i class="fas fa-layer-group"></i>
                <p>Drag and drop blocks here to build your page</p>
            </div>
            <?php endif; ?>
            
            <!-- Blocks will be rendered here -->
            <div id="blocksContainer"></div>
        </div>
    </div>
    
    <!-- Edit Panel -->
    <div class="edit-panel" id="editPanel">
        <div class="edit-panel-header">
            <h5 class="mb-0" id="editPanelTitle">Edit Block</h5>
            <button class="btn btn-sm btn-outline-secondary" id="closeEditPanel">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="edit-panel-body" id="editPanelBody">
            <!-- Dynamic form fields -->
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include TinyMCE for WYSIWYG -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
    
    <script>
        // Page Builder JavaScript
        const blockTypes = <?php echo $blockTypesJson; ?>;
        let blocks = <?php echo $blocksJson; ?>;
        let selectedBlockIndex = null;
        
        // Initialize
        $(document).ready(function() {
            renderBlocks();
            initDragDrop();
            initEventHandlers();
        });
        
        // Render all blocks
        function renderBlocks() {
            const container = $('#blocksContainer');
            container.empty();
            
            if (blocks.length === 0) {
                $('#emptyState').show();
                return;
            }
            
            $('#emptyState').hide();
            
            blocks.forEach((block, index) => {
                const blockHtml = renderBlock(block, index);
                container.append(blockHtml);
                container.append(`<div class="drop-zone" data-index="${index + 1}"></div>`);
            });
        }
        
        // Render a single block
        function renderBlock(block, index) {
            const type = blockTypes[block.type];
            const typeName = type ? type.name : block.type;
            
            return `
                <div class="builder-block" data-index="${index}">
                    <span class="block-label">${typeName}</span>
                    <div class="block-actions">
                        <button class="btn-move" title="Move"><i class="fas fa-arrows-alt"></i></button>
                        <button class="btn-edit" title="Edit" onclick="editBlock(${index})"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete" title="Delete" onclick="deleteBlock(${index})"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="block-content">${getBlockPreview(block)}</div>
                </div>
            `;
        }
        
        // Get block preview HTML
        function getBlockPreview(block) {
            const data = block.data || {};
            
            switch (block.type) {
                case 'hero':
                    return `<div class="text-center py-5 bg-primary text-white">
                        <h2>${data.title || 'Hero Title'}</h2>
                        <p>${data.subtitle || 'Subtitle text here'}</p>
                    </div>`;
                    
                case 'text':
                    return `<div class="p-4">${data.content || '<p>Text content...</p>'}</div>`;
                    
                case 'features':
                    return `<div class="p-4 bg-light text-center">
                        <h4>${data.title || 'Features Section'}</h4>
                        <p class="text-muted">${(data.features || []).length} features</p>
                    </div>`;
                    
                case 'pricing':
                    return `<div class="p-4 bg-light text-center">
                        <h4>${data.title || 'Pricing Table'}</h4>
                        <p class="text-muted">Product group pricing</p>
                    </div>`;
                    
                case 'cta':
                    const bgClass = data.style || 'primary';
                    return `<div class="p-4 bg-${bgClass} text-white text-center">
                        <h4>${data.title || 'Call to Action'}</h4>
                    </div>`;
                    
                case 'testimonials':
                    return `<div class="p-4 text-center">
                        <h4>${data.title || 'Testimonials'}</h4>
                        <p class="text-muted">${(data.testimonials || []).length} testimonials</p>
                    </div>`;
                    
                case 'faq':
                    return `<div class="p-4 text-center">
                        <h4>${data.title || 'FAQ'}</h4>
                        <p class="text-muted">${(data.questions || []).length} questions</p>
                    </div>`;
                    
                case 'contact':
                    return `<div class="p-4 bg-light text-center">
                        <h4>${data.title || 'Contact Form'}</h4>
                    </div>`;
                    
                case 'html':
                    return `<div class="p-3 bg-dark text-white small">
                        <i class="fas fa-code me-2"></i>Custom HTML
                    </div>`;
                    
                case 'spacer':
                    return `<div style="height: ${data.height || 50}px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">
                        Spacer (${data.height || 50}px)
                    </div>`;
                    
                case 'divider':
                    return `<hr style="border-style: ${data.style || 'solid'}; border-color: ${data.color || '#ddd'}; margin: 20px;">`;
                    
                default:
                    return `<div class="p-3 text-center text-muted">${block.type} block</div>`;
            }
        }
        
        // Initialize drag and drop
        function initDragDrop() {
            // Draggable block types
            $('.block-type').on('dragstart', function(e) {
                e.originalEvent.dataTransfer.setData('blockType', $(this).data('type'));
            });
            
            // Drop zones
            $(document).on('dragover', '.drop-zone', function(e) {
                e.preventDefault();
                $(this).addClass('active');
            });
            
            $(document).on('dragleave', '.drop-zone', function() {
                $(this).removeClass('active');
            });
            
            $(document).on('drop', '.drop-zone', function(e) {
                e.preventDefault();
                $(this).removeClass('active');
                
                const blockType = e.originalEvent.dataTransfer.getData('blockType');
                const index = $(this).data('index');
                
                if (blockType) {
                    addBlock(blockType, index);
                }
            });
        }
        
        // Add new block
        function addBlock(type, index) {
            const newBlock = {
                type: type,
                data: {}
            };
            
            blocks.splice(index, 0, newBlock);
            renderBlocks();
            editBlock(index);
        }
        
        // Edit block
        function editBlock(index) {
            selectedBlockIndex = index;
            const block = blocks[index];
            const type = blockTypes[block.type];
            
            if (!type) return;
            
            $('#editPanelTitle').text('Edit ' + type.name);
            const body = $('#editPanelBody');
            body.empty();
            
            // Generate form fields
            Object.entries(type.fields).forEach(([fieldName, field]) => {
                body.append(generateField(fieldName, field, block.data[fieldName]));
            });
            
            // Save button
            body.append(`
                <div class="mt-4">
                    <button class="btn btn-primary w-100" onclick="saveBlockEdit()">
                        <i class="fas fa-check me-1"></i> Apply Changes
                    </button>
                </div>
            `);
            
            // Initialize WYSIWYG editors
            initWysiwyg();
            
            $('#editPanel').addClass('open');
        }
        
        // Generate form field
        function generateField(name, field, value) {
            const label = field.label || name;
            value = value || '';
            
            let input = '';
            
            switch (field.type) {
                case 'text':
                    input = `<input type="text" class="form-control" name="${name}" value="${escapeHtml(value)}">`;
                    break;
                    
                case 'textarea':
                    input = `<textarea class="form-control" name="${name}" rows="3">${escapeHtml(value)}</textarea>`;
                    break;
                    
                case 'wysiwyg':
                    input = `<textarea class="form-control wysiwyg-editor" name="${name}">${value}</textarea>`;
                    break;
                    
                case 'select':
                    const options = (field.options || []).map(opt => 
                        `<option value="${opt}" ${value === opt ? 'selected' : ''}>${opt}</option>`
                    ).join('');
                    input = `<select class="form-select" name="${name}">${options}</select>`;
                    break;
                    
                case 'color':
                    input = `<input type="color" class="form-control form-control-color" name="${name}" value="${value || '#000000'}">`;
                    break;
                    
                case 'number':
                    input = `<input type="number" class="form-control" name="${name}" value="${value || 0}">`;
                    break;
                    
                case 'checkbox':
                    input = `<div class="form-check">
                        <input type="checkbox" class="form-check-input" name="${name}" ${value ? 'checked' : ''}>
                    </div>`;
                    break;
                    
                case 'image':
                    input = `<div class="input-group">
                        <input type="text" class="form-control" name="${name}" value="${escapeHtml(value)}" placeholder="Image URL">
                        <button class="btn btn-outline-secondary" type="button" onclick="openMediaLibrary('${name}')">
                            <i class="fas fa-image"></i>
                        </button>
                    </div>`;
                    break;
                    
                case 'icon':
                    input = `<div class="input-group">
                        <span class="input-group-text"><i class="${value || 'fas fa-star'}"></i></span>
                        <input type="text" class="form-control" name="${name}" value="${escapeHtml(value)}" placeholder="fas fa-star">
                    </div>`;
                    break;
                    
                case 'code':
                    input = `<textarea class="form-control font-monospace" name="${name}" rows="6">${escapeHtml(value)}</textarea>`;
                    break;
                    
                case 'repeater':
                    input = generateRepeater(name, field, value || []);
                    break;
                    
                default:
                    input = `<input type="text" class="form-control" name="${name}" value="${escapeHtml(value)}">`;
            }
            
            return `<div class="mb-3"><label class="form-label">${label}</label>${input}</div>`;
        }
        
        // Generate repeater field
        function generateRepeater(name, field, items) {
            let html = `<div class="repeater" data-name="${name}">
                <div class="repeater-items">`;
            
            items.forEach((item, i) => {
                html += `<div class="repeater-item card mb-2">
                    <div class="card-body p-2">
                        <button class="btn btn-sm btn-outline-danger float-end" onclick="removeRepeaterItem(this)">
                            <i class="fas fa-times"></i>
                        </button>`;
                        
                Object.entries(field.fields).forEach(([subName, subField]) => {
                    html += `<div class="mb-2">
                        <label class="form-label small">${subField.label}</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="${name}[${i}][${subName}]" 
                               value="${escapeHtml(item[subName] || '')}">
                    </div>`;
                });
                
                html += `</div></div>`;
            });
            
            html += `</div>
                <button class="btn btn-sm btn-outline-primary" onclick="addRepeaterItem('${name}')">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>`;
            
            return html;
        }
        
        // Save block edit
        function saveBlockEdit() {
            if (selectedBlockIndex === null) return;
            
            const data = {};
            
            $('#editPanelBody').find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                
                if (!name) return;
                
                if ($el.attr('type') === 'checkbox') {
                    data[name] = $el.is(':checked');
                } else if ($el.hasClass('wysiwyg-editor')) {
                    data[name] = tinymce.get($el.attr('id'))?.getContent() || $el.val();
                } else {
                    // Handle repeater fields
                    const match = name.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
                    if (match) {
                        const [, arrName, idx, field] = match;
                        if (!data[arrName]) data[arrName] = [];
                        if (!data[arrName][idx]) data[arrName][idx] = {};
                        data[arrName][idx][field] = $el.val();
                    } else {
                        data[name] = $el.val();
                    }
                }
            });
            
            blocks[selectedBlockIndex].data = data;
            renderBlocks();
            closeEditPanel();
        }
        
        // Delete block
        function deleteBlock(index) {
            if (confirm('Are you sure you want to delete this block?')) {
                blocks.splice(index, 1);
                renderBlocks();
            }
        }
        
        // Close edit panel
        function closeEditPanel() {
            $('#editPanel').removeClass('open');
            selectedBlockIndex = null;
            tinymce.remove('.wysiwyg-editor');
        }
        
        // Initialize WYSIWYG
        function initWysiwyg() {
            tinymce.init({
                selector: '.wysiwyg-editor',
                height: 300,
                menubar: false,
                plugins: 'lists link image code',
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
            });
        }
        
        // Event handlers
        function initEventHandlers() {
            // Preview mode buttons
            $('.preview-btn').click(function() {
                $('.preview-btn').removeClass('active');
                $(this).addClass('active');
                const mode = $(this).data('mode');
                $('.builder-canvas').removeClass('preview-mode-desktop preview-mode-tablet preview-mode-mobile')
                    .addClass('preview-mode-' + mode);
            });
            
            // Close edit panel
            $('#closeEditPanel').click(closeEditPanel);
            
            // Preview button
            $('#previewBtn').click(function() {
                window.open('<?php echo ADMIN_URL; ?>/pages/preview/<?php echo $page['id']; ?>', '_blank');
            });
            
            // Save button
            $('#saveBtn').click(function() {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
                
                $.ajax({
                    url: '<?php echo ADMIN_URL; ?>/pages/builder/<?php echo $page['id']; ?>',
                    method: 'POST',
                    data: {
                        blocks: JSON.stringify(blocks)
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Saved!');
                        setTimeout(() => {
                            $btn.html('<i class="fas fa-save me-1"></i> Save');
                        }, 2000);
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                        alert('Failed to save. Please try again.');
                    }
                });
            });
        }
        
        // Helpers
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function addRepeaterItem(name) {
            const container = $(`.repeater[data-name="${name}"] .repeater-items`);
            const index = container.children().length;
            const field = blockTypes[blocks[selectedBlockIndex].type].fields[name];
            
            let html = `<div class="repeater-item card mb-2">
                <div class="card-body p-2">
                    <button class="btn btn-sm btn-outline-danger float-end" onclick="removeRepeaterItem(this)">
                        <i class="fas fa-times"></i>
                    </button>`;
                    
            Object.entries(field.fields).forEach(([subName, subField]) => {
                html += `<div class="mb-2">
                    <label class="form-label small">${subField.label}</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="${name}[${index}][${subName}]" value="">
                </div>`;
            });
            
            html += `</div></div>`;
            container.append(html);
        }
        
        function removeRepeaterItem(btn) {
            $(btn).closest('.repeater-item').remove();
        }
    </script>
</body>
</html>
