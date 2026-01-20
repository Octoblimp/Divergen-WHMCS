<?php
namespace OpenWHM\Core;

/**
 * View/Template Engine
 */
class View
{
    private $data = [];
    private $sections = [];
    private $currentSection = null;
    private $layout = null;
    
    /**
     * Render a template
     */
    public function render($template, $data = [])
    {
        $this->data = $data;
        
        // Add common data
        $this->data['session'] = Application::getInstance()->getSession();
        $this->data['csrf_token'] = $this->data['session']->generateCsrfToken();
        $this->data['flash'] = [
            'success' => $this->data['session']->getFlash('success'),
            'error' => $this->data['session']->getFlash('error'),
            'warning' => $this->data['session']->getFlash('warning'),
            'info' => $this->data['session']->getFlash('info')
        ];
        
        // Extract data to variables
        extract($this->data);
        
        // Determine template path
        $templatePath = $this->resolveTemplatePath($template);
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }
        
        // Start output buffering
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        // If layout is set, render within layout
        if ($this->layout) {
            $layoutPath = $this->resolveTemplatePath($this->layout);
            $this->sections['content'] = $content;
            
            ob_start();
            include $layoutPath;
            $content = ob_get_clean();
            
            $this->layout = null;
        }
        
        echo $content;
    }
    
    /**
     * Resolve template path
     */
    private function resolveTemplatePath($template)
    {
        // Check if it's a full path already
        if (strpos($template, '/') === 0 || strpos($template, ':\\') !== false) {
            return $template;
        }
        
        return TEMPLATE_PATH . '/' . str_replace('.', '/', $template) . '.php';
    }
    
    /**
     * Set layout
     */
    public function layout($layout)
    {
        $this->layout = $layout;
    }
    
    /**
     * Start a section
     */
    public function section($name)
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End current section
     */
    public function endSection()
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Yield a section
     */
    public function yield($name, $default = '')
    {
        echo $this->sections[$name] ?? $default;
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
