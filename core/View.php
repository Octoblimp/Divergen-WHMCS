<?php
namespace OpenWHM\Core;

/**
 * View/Template Engine
 */
class View
{
    private $data = [];
    private $layout = null;
    public $session;
    
    public function __construct()
    {
        $this->session = Application::getInstance()->getSession();
    }
    
    /**
     * Render a template
     */
    public function render($template, $data = [])
    {
        $this->data = $data;
        
        // Add common data
        $this->data['session'] = $this->session;
        $this->data['csrf_token'] = $this->session->generateCsrfToken();
        $this->data['flash'] = [
            'success' => $this->session->getFlash('success'),
            'error' => $this->session->getFlash('error'),
            'warning' => $this->session->getFlash('warning'),
            'info' => $this->session->getFlash('info')
        ];
        
        // Extract data to variables
        extract($this->data);
        
        // Determine template path
        $templatePath = $this->resolveTemplatePath($template);
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template} (looking for: {$templatePath})");
        }
        
        // Start output buffering for template content
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        // If template already includes DOCTYPE (has its own layout), output as-is
        if (stripos($content, '<!DOCTYPE') !== false || stripos($content, '<html') !== false) {
            echo $content;
            return;
        }
        
        // Determine layout based on template type
        if ($this->layout === null) {
            // Auto-detect layout from template path
            if (strpos($template, 'frontend.') === 0) {
                $this->layout = 'frontend.layouts.main';
            } elseif (strpos($template, 'client.') === 0) {
                $this->layout = 'client.layouts.main';
            } elseif (strpos($template, 'admin.') === 0) {
                $this->layout = 'admin.layouts.main';
            }
        }
        
        // If layout is set, render within layout
        if ($this->layout) {
            $layoutPath = $this->resolveTemplatePath($this->layout);
            
            if (file_exists($layoutPath)) {
                // Make content available to layout
                $this->data['content'] = $content;
                extract($this->data);
                
                ob_start();
                include $layoutPath;
                $content = ob_get_clean();
            }
            
            $this->layout = null;
        }
        
        echo $content;
    }
    
    /**
     * Resolve template path
     */
    private function resolveTemplatePath($template)
    {
        return ROOT_PATH . '/templates/' . str_replace('.', '/', $template) . '.php';
    }
    
    /**
     * Set layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
    /**
     * Disable layout
     */
    public function noLayout()
    {
        $this->layout = false;
    }
    
    /**
     * Include a partial
     */
    public function partial($template, $data = [])
    {
        extract(array_merge($this->data, $data));
        
        $templatePath = $this->resolveTemplatePath($template);
        
        if (file_exists($templatePath)) {
            include $templatePath;
        }
    }
    
    /**
     * Escape HTML
     */
    public function e($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format currency
     */
    public function formatCurrency($amount, $currency = null)
    {
        $currency = $currency ?? DEFAULT_CURRENCY;
        $symbol = CURRENCY_SYMBOL;
        
        return $symbol . number_format($amount, 2);
    }
    
    /**
     * Format date
     */
    public function formatDate($date, $format = 'M d, Y')
    {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Generate URL
     */
    public function url($path, $params = [])
    {
        $url = SYSTEM_URL . $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Generate asset URL
     */
    public function asset($path)
    {
        return SYSTEM_URL . '/assets/' . ltrim($path, '/');
    }
}

