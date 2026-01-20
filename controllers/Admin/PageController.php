<?php
/**
 * Admin Page Builder Controller
 * WYSIWYG page editor with block-based content
 */

namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;

class PageController extends Controller
{
    protected $table = 'pages';
    
    /**
     * List all pages
     */
    public function index()
    {
        $this->requireAdmin();
        
        $pages = $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY title ASC"
        );
        
        $this->render('admin/pages/index', [
            'pages' => $pages
        ]);
    }
    
    /**
     * Create new page
     */
    public function create()
    {
        $this->requireAdmin();
        
        if ($this->isPost()) {
            $data = [
                'title' => $_POST['title'] ?? '',
                'slug' => $this->generateSlug($_POST['title'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'blocks' => $_POST['blocks'] ?? '[]',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'template' => $_POST['template'] ?? 'default',
                'status' => $_POST['status'] ?? 'draft',
                'show_in_menu' => isset($_POST['show_in_menu']) ? 1 : 0,
                'menu_order' => intval($_POST['menu_order'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate
            $errors = $this->validatePage($data);
            
            if (empty($errors)) {
                $pageId = $this->db->insert($this->table, $data);
                
                if ($pageId) {
                    // Generate static HTML file if enabled
                    if ($_POST['generate_file'] ?? false) {
                        $this->generateStaticFile($pageId, $data);
                    }
                    
                    $this->flash('success', 'Page created successfully.');
                    $this->redirect(ADMIN_URL . '/pages');
                } else {
                    $errors[] = 'Failed to create page.';
                }
            }
            
            $this->render('admin/pages/create', [
                'data' => $data,
                'errors' => $errors,
                'templates' => $this->getTemplates(),
                'blockTypes' => $this->getBlockTypes()
            ]);
            return;
        }
        
        $this->render('admin/pages/create', [
            'templates' => $this->getTemplates(),
            'blockTypes' => $this->getBlockTypes()
        ]);
    }
    
    /**
     * Edit existing page
     */
    public function edit($id)
    {
        $this->requireAdmin();
        
        $page = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        if (!$page) {
            $this->flash('error', 'Page not found.');
            $this->redirect(ADMIN_URL . '/pages');
        }
        
        if ($this->isPost()) {
            $data = [
                'title' => $_POST['title'] ?? '',
                'slug' => $this->generateSlug($_POST['slug'] ?? $_POST['title'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'blocks' => $_POST['blocks'] ?? '[]',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'template' => $_POST['template'] ?? 'default',
                'status' => $_POST['status'] ?? 'draft',
                'show_in_menu' => isset($_POST['show_in_menu']) ? 1 : 0,
                'menu_order' => intval($_POST['menu_order'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $errors = $this->validatePage($data, $id);
            
            if (empty($errors)) {
                $updated = $this->db->update($this->table, $data, 'id = ?', [$id]);
                
                if ($updated) {
                    // Regenerate static file
                    if ($_POST['generate_file'] ?? false) {
                        $this->generateStaticFile($id, array_merge($page, $data));
                    }
                    
                    $this->flash('success', 'Page updated successfully.');
                    $this->redirect(ADMIN_URL . '/pages');
                } else {
                    $errors[] = 'Failed to update page.';
                }
            }
            
            $this->render('admin/pages/edit', [
                'page' => array_merge($page, $data),
                'errors' => $errors,
                'templates' => $this->getTemplates(),
                'blockTypes' => $this->getBlockTypes()
            ]);
            return;
        }
        
        $this->render('admin/pages/edit', [
            'page' => $page,
            'templates' => $this->getTemplates(),
            'blockTypes' => $this->getBlockTypes()
        ]);
    }
    
    /**
     * Delete page
     */
    public function delete($id)
    {
        $this->requireAdmin();
        
        $page = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        if ($page) {
            // Remove static file if exists
            $filePath = ROOT_PATH . '/pages/' . $page['slug'] . '.php';
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $this->db->delete($this->table, 'id = ?', [$id]);
            $this->flash('success', 'Page deleted successfully.');
        }
        
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
     * Page builder AJAX endpoint
     */
    public function builder($id)
    {
        $this->requireAdmin();
        
        $page = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        if (!$page) {
            $this->json(['error' => 'Page not found'], 404);
            return;
        }
        
        if ($this->isPost()) {
            // Save blocks
            $blocks = $_POST['blocks'] ?? '[]';
            $content = $this->renderBlocks(json_decode($blocks, true) ?: []);
            
            $this->db->update($this->table, [
                'blocks' => $blocks,
                'content' => $content,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->json(['success' => true, 'content' => $content]);
            return;
        }
        
        $this->render('admin/pages/builder', [
            'page' => $page,
            'blockTypes' => $this->getBlockTypes()
        ]);
    }
    
    /**
     * Preview page
     */
    public function preview($id)
    {
        $page = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
        
        if (!$page) {
            $this->notFound();
            return;
        }
        
        // Render with template
        $template = $page['template'] ?: 'default';
        $this->render("frontend/pages/{$template}", [
            'page' => $page,
            'content' => $this->renderBlocks(json_decode($page['blocks'], true) ?: [])
        ]);
    }
    
    /**
     * Validate page data
     */
    protected function validatePage(array $data, $excludeId = null): array
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($data['slug'])) {
            $errors[] = 'Slug is required.';
        } else {
            // Check for duplicate slug
            $sql = "SELECT id FROM {$this->table} WHERE slug = ?";
            $params = [$data['slug']];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $this->db->fetch($sql, $params);
            if ($existing) {
                $errors[] = 'Slug is already in use.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate URL-friendly slug
     */
    protected function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Get available page templates
     */
    protected function getTemplates(): array
    {
        return [
            'default' => 'Default',
            'full-width' => 'Full Width',
            'sidebar-left' => 'Sidebar Left',
            'sidebar-right' => 'Sidebar Right',
            'landing' => 'Landing Page',
            'blank' => 'Blank'
        ];
    }
    
    /**
     * Get available block types
     */
    protected function getBlockTypes(): array
    {
        return [
            'hero' => [
                'name' => 'Hero Section',
                'icon' => 'fas fa-image',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'subtitle' => ['type' => 'text', 'label' => 'Subtitle'],
                    'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                    'button_url' => ['type' => 'text', 'label' => 'Button URL'],
                    'background' => ['type' => 'image', 'label' => 'Background Image'],
                    'overlay' => ['type' => 'color', 'label' => 'Overlay Color']
                ]
            ],
            'text' => [
                'name' => 'Text Block',
                'icon' => 'fas fa-align-left',
                'fields' => [
                    'content' => ['type' => 'wysiwyg', 'label' => 'Content']
                ]
            ],
            'features' => [
                'name' => 'Features Grid',
                'icon' => 'fas fa-th-large',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section Title'],
                    'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => [2, 3, 4]],
                    'features' => ['type' => 'repeater', 'label' => 'Features', 'fields' => [
                        'icon' => ['type' => 'icon', 'label' => 'Icon'],
                        'title' => ['type' => 'text', 'label' => 'Title'],
                        'description' => ['type' => 'textarea', 'label' => 'Description']
                    ]]
                ]
            ],
            'pricing' => [
                'name' => 'Pricing Table',
                'icon' => 'fas fa-tags',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section Title'],
                    'product_group' => ['type' => 'select', 'label' => 'Product Group', 'source' => 'product_groups'],
                    'show_features' => ['type' => 'checkbox', 'label' => 'Show Features']
                ]
            ],
            'cta' => [
                'name' => 'Call to Action',
                'icon' => 'fas fa-bullhorn',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'description' => ['type' => 'textarea', 'label' => 'Description'],
                    'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                    'button_url' => ['type' => 'text', 'label' => 'Button URL'],
                    'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['primary', 'secondary', 'dark']]
                ]
            ],
            'testimonials' => [
                'name' => 'Testimonials',
                'icon' => 'fas fa-quote-right',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section Title'],
                    'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['carousel', 'grid']],
                    'testimonials' => ['type' => 'repeater', 'label' => 'Testimonials', 'fields' => [
                        'content' => ['type' => 'textarea', 'label' => 'Content'],
                        'author' => ['type' => 'text', 'label' => 'Author'],
                        'company' => ['type' => 'text', 'label' => 'Company'],
                        'image' => ['type' => 'image', 'label' => 'Photo']
                    ]]
                ]
            ],
            'image_text' => [
                'name' => 'Image + Text',
                'icon' => 'fas fa-columns',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Image'],
                    'image_position' => ['type' => 'select', 'label' => 'Image Position', 'options' => ['left', 'right']],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'content' => ['type' => 'wysiwyg', 'label' => 'Content']
                ]
            ],
            'faq' => [
                'name' => 'FAQ Accordion',
                'icon' => 'fas fa-question-circle',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section Title'],
                    'questions' => ['type' => 'repeater', 'label' => 'Questions', 'fields' => [
                        'question' => ['type' => 'text', 'label' => 'Question'],
                        'answer' => ['type' => 'wysiwyg', 'label' => 'Answer']
                    ]]
                ]
            ],
            'contact' => [
                'name' => 'Contact Form',
                'icon' => 'fas fa-envelope',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Section Title'],
                    'email' => ['type' => 'text', 'label' => 'Recipient Email'],
                    'show_map' => ['type' => 'checkbox', 'label' => 'Show Map'],
                    'map_embed' => ['type' => 'textarea', 'label' => 'Map Embed Code']
                ]
            ],
            'html' => [
                'name' => 'Custom HTML',
                'icon' => 'fas fa-code',
                'fields' => [
                    'html' => ['type' => 'code', 'label' => 'HTML Code']
                ]
            ],
            'spacer' => [
                'name' => 'Spacer',
                'icon' => 'fas fa-arrows-alt-v',
                'fields' => [
                    'height' => ['type' => 'number', 'label' => 'Height (px)']
                ]
            ],
            'divider' => [
                'name' => 'Divider',
                'icon' => 'fas fa-minus',
                'fields' => [
                    'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['solid', 'dashed', 'dotted']],
                    'color' => ['type' => 'color', 'label' => 'Color']
                ]
            ]
        ];
    }
    
    /**
     * Render blocks to HTML
     */
    protected function renderBlocks(array $blocks): string
    {
        $html = '';
        
        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block);
        }
        
        return $html;
    }
    
    /**
     * Render a single block
     */
    protected function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];
        
        switch ($type) {
            case 'hero':
                return $this->renderHeroBlock($data);
            case 'text':
                return $this->renderTextBlock($data);
            case 'features':
                return $this->renderFeaturesBlock($data);
            case 'pricing':
                return $this->renderPricingBlock($data);
            case 'cta':
                return $this->renderCtaBlock($data);
            case 'testimonials':
                return $this->renderTestimonialsBlock($data);
            case 'image_text':
                return $this->renderImageTextBlock($data);
            case 'faq':
                return $this->renderFaqBlock($data);
            case 'contact':
                return $this->renderContactBlock($data);
            case 'html':
                return $data['html'] ?? '';
            case 'spacer':
                $height = intval($data['height'] ?? 50);
                return "<div style=\"height: {$height}px;\"></div>";
            case 'divider':
                $style = $data['style'] ?? 'solid';
                $color = $data['color'] ?? '#ddd';
                return "<hr style=\"border-style: {$style}; border-color: {$color};\">";
            default:
                return '';
        }
    }
    
    protected function renderHeroBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $subtitle = htmlspecialchars($data['subtitle'] ?? '');
        $buttonText = htmlspecialchars($data['button_text'] ?? '');
        $buttonUrl = htmlspecialchars($data['button_url'] ?? '#');
        $bg = $data['background'] ?? '';
        $overlay = $data['overlay'] ?? 'rgba(0,0,0,0.5)';
        
        $bgStyle = $bg ? "background-image: url('{$bg}'); background-size: cover;" : '';
        
        return <<<HTML
<section class="hero-section" style="{$bgStyle}">
    <div class="hero-overlay" style="background: {$overlay};">
        <div class="container text-center text-white py-5">
            <h1 class="display-4 fw-bold">{$title}</h1>
            <p class="lead">{$subtitle}</p>
            {$buttonText ? "<a href=\"{$buttonUrl}\" class=\"btn btn-primary btn-lg\">{$buttonText}</a>" : ''}
        </div>
    </div>
</section>
HTML;
    }
    
    protected function renderTextBlock(array $data): string
    {
        $content = $data['content'] ?? '';
        return "<section class=\"text-block py-4\"><div class=\"container\">{$content}</div></section>";
    }
    
    protected function renderFeaturesBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $columns = intval($data['columns'] ?? 3);
        $colClass = 'col-md-' . (12 / $columns);
        
        $html = "<section class=\"features-section py-5\"><div class=\"container\">";
        if ($title) {
            $html .= "<h2 class=\"text-center mb-5\">{$title}</h2>";
        }
        $html .= "<div class=\"row\">";
        
        foreach ($data['features'] ?? [] as $feature) {
            $icon = htmlspecialchars($feature['icon'] ?? 'fas fa-check');
            $featureTitle = htmlspecialchars($feature['title'] ?? '');
            $desc = htmlspecialchars($feature['description'] ?? '');
            
            $html .= "<div class=\"{$colClass} mb-4\"><div class=\"text-center\">
                <i class=\"{$icon} fa-3x text-primary mb-3\"></i>
                <h4>{$featureTitle}</h4>
                <p class=\"text-muted\">{$desc}</p>
            </div></div>";
        }
        
        $html .= "</div></div></section>";
        return $html;
    }
    
    protected function renderPricingBlock(array $data): string
    {
        // Fetch products from database
        $groupId = $data['product_group'] ?? null;
        
        if ($groupId) {
            $products = $this->db->fetchAll(
                "SELECT * FROM products WHERE group_id = ? AND status = 'active' ORDER BY sort_order ASC",
                [$groupId]
            );
        } else {
            $products = [];
        }
        
        $title = htmlspecialchars($data['title'] ?? 'Our Pricing');
        
        $html = "<section class=\"pricing-section py-5 bg-light\"><div class=\"container\">
            <h2 class=\"text-center mb-5\">{$title}</h2>
            <div class=\"row justify-content-center\">";
        
        foreach ($products as $product) {
            $productName = htmlspecialchars($product['name']);
            $price = number_format($product['monthly_price'] ?? 0, 2);
            
            $html .= "<div class=\"col-md-4 mb-4\">
                <div class=\"card pricing-card h-100\">
                    <div class=\"card-header text-center\"><h4 class=\"my-0\">{$productName}</h4></div>
                    <div class=\"card-body text-center\">
                        <h1 class=\"card-title\">\${$price}<small class=\"text-muted\">/mo</small></h1>
                        <a href=\"" . BASE_URL . "/cart/add/{$product['id']}\" class=\"btn btn-primary btn-lg\">Order Now</a>
                    </div>
                </div>
            </div>";
        }
        
        $html .= "</div></div></section>";
        return $html;
    }
    
    protected function renderCtaBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? '');
        $desc = htmlspecialchars($data['description'] ?? '');
        $buttonText = htmlspecialchars($data['button_text'] ?? '');
        $buttonUrl = htmlspecialchars($data['button_url'] ?? '#');
        $style = $data['style'] ?? 'primary';
        
        return "<section class=\"cta-section py-5 bg-{$style} text-white\"><div class=\"container text-center\">
            <h2>{$title}</h2>
            <p class=\"lead\">{$desc}</p>
            <a href=\"{$buttonUrl}\" class=\"btn btn-light btn-lg\">{$buttonText}</a>
        </div></section>";
    }
    
    protected function renderTestimonialsBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? 'What Our Customers Say');
        
        $html = "<section class=\"testimonials-section py-5\"><div class=\"container\">
            <h2 class=\"text-center mb-5\">{$title}</h2>
            <div class=\"row\">";
        
        foreach ($data['testimonials'] ?? [] as $testimonial) {
            $content = htmlspecialchars($testimonial['content'] ?? '');
            $author = htmlspecialchars($testimonial['author'] ?? '');
            $company = htmlspecialchars($testimonial['company'] ?? '');
            
            $html .= "<div class=\"col-md-4 mb-4\">
                <div class=\"card h-100\">
                    <div class=\"card-body\">
                        <p class=\"card-text\">\"{$content}\"</p>
                        <footer class=\"blockquote-footer mt-3\">
                            {$author}" . ($company ? ", <cite>{$company}</cite>" : '') . "
                        </footer>
                    </div>
                </div>
            </div>";
        }
        
        $html .= "</div></div></section>";
        return $html;
    }
    
    protected function renderImageTextBlock(array $data): string
    {
        $image = htmlspecialchars($data['image'] ?? '');
        $position = $data['image_position'] ?? 'left';
        $title = htmlspecialchars($data['title'] ?? '');
        $content = $data['content'] ?? '';
        
        $imgCol = "<div class=\"col-md-6\"><img src=\"{$image}\" class=\"img-fluid rounded\" alt=\"{$title}\"></div>";
        $textCol = "<div class=\"col-md-6\"><h3>{$title}</h3>{$content}</div>";
        
        $html = "<section class=\"image-text-section py-5\"><div class=\"container\"><div class=\"row align-items-center\">";
        $html .= ($position === 'left') ? $imgCol . $textCol : $textCol . $imgCol;
        $html .= "</div></div></section>";
        
        return $html;
    }
    
    protected function renderFaqBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? 'Frequently Asked Questions');
        
        $html = "<section class=\"faq-section py-5\"><div class=\"container\">
            <h2 class=\"text-center mb-5\">{$title}</h2>
            <div class=\"accordion\" id=\"faqAccordion\">";
        
        foreach ($data['questions'] ?? [] as $i => $q) {
            $question = htmlspecialchars($q['question'] ?? '');
            $answer = $q['answer'] ?? '';
            $collapsed = $i > 0 ? 'collapsed' : '';
            $show = $i === 0 ? 'show' : '';
            
            $html .= "<div class=\"accordion-item\">
                <h2 class=\"accordion-header\">
                    <button class=\"accordion-button {$collapsed}\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#faq{$i}\">
                        {$question}
                    </button>
                </h2>
                <div id=\"faq{$i}\" class=\"accordion-collapse collapse {$show}\" data-bs-parent=\"#faqAccordion\">
                    <div class=\"accordion-body\">{$answer}</div>
                </div>
            </div>";
        }
        
        $html .= "</div></div></section>";
        return $html;
    }
    
    protected function renderContactBlock(array $data): string
    {
        $title = htmlspecialchars($data['title'] ?? 'Contact Us');
        $showMap = $data['show_map'] ?? false;
        $mapEmbed = $data['map_embed'] ?? '';
        
        $html = "<section class=\"contact-section py-5 bg-light\"><div class=\"container\">
            <h2 class=\"text-center mb-5\">{$title}</h2>
            <div class=\"row\">
                <div class=\"col-md-6\">
                    <form action=\"" . BASE_URL . "/contact/submit\" method=\"POST\">
                        <div class=\"mb-3\">
                            <label class=\"form-label\">Name</label>
                            <input type=\"text\" name=\"name\" class=\"form-control\" required>
                        </div>
                        <div class=\"mb-3\">
                            <label class=\"form-label\">Email</label>
                            <input type=\"email\" name=\"email\" class=\"form-control\" required>
                        </div>
                        <div class=\"mb-3\">
                            <label class=\"form-label\">Message</label>
                            <textarea name=\"message\" class=\"form-control\" rows=\"5\" required></textarea>
                        </div>
                        <button type=\"submit\" class=\"btn btn-primary\">Send Message</button>
                    </form>
                </div>";
        
        if ($showMap && $mapEmbed) {
            $html .= "<div class=\"col-md-6\">{$mapEmbed}</div>";
        }
        
        $html .= "</div></div></section>";
        return $html;
    }
    
    /**
     * Generate static PHP file for page
     */
    protected function generateStaticFile($pageId, array $data): bool
    {
        $pagesDir = ROOT_PATH . '/pages';
        if (!is_dir($pagesDir)) {
            mkdir($pagesDir, 0755, true);
        }
        
        $slug = $data['slug'];
        $filePath = $pagesDir . '/' . $slug . '.php';
        
        $template = $data['template'] ?? 'default';
        $title = addslashes($data['title']);
        $metaTitle = addslashes($data['meta_title'] ?: $data['title']);
        $metaDesc = addslashes($data['meta_description']);
        
        $content = <<<PHP
<?php
/**
 * Auto-generated page: {$title}
 * Generated at: {$data['updated_at']}
 */

require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/core/Autoloader.php';
new \OpenWHM\Core\Autoloader();

use OpenWHM\Core\Database;

\$db = Database::getInstance();
\$page = \$db->fetch("SELECT * FROM pages WHERE id = ?", [{$pageId}]);

if (!\$page || \$page['status'] !== 'published') {
    http_response_code(404);
    include ROOT_PATH . '/templates/errors/404.php';
    exit;
}

\$title = '{$metaTitle}';
\$metaDescription = '{$metaDesc}';

include ROOT_PATH . '/templates/frontend/pages/{$template}.php';
PHP;
        
        return file_put_contents($filePath, $content) !== false;
    }
}
