<?php
namespace OpenWHM\Controllers\Admin;

use OpenWHM\Core\Controller;

/**
 * Admin Settings Controller
 */
class SettingsController extends Controller
{
    /**
     * Show general settings
     */
    public function index()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings();
        
        $this->render('admin.settings.general', [
            'settings' => $settings,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update general settings
     */
    public function update()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $settings = $_POST['settings'] ?? [];
        
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        
        $this->flash('success', 'General settings saved successfully');
        $this->redirect(ADMIN_URL . '/settings');
    }
    
    /**
     * Show email settings
     */
    public function email()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings();
        
        $this->render('admin.settings.email', [
            'settings' => $settings,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update email settings
     */
    public function updateEmail()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $settings = [
            'mail_enabled' => isset($_POST['mail_enabled']) ? 1 : 0,
            'mail_from_email' => $_POST['mail_from_email'] ?? '',
            'mail_from_name' => $_POST['mail_from_name'] ?? '',
            'mail_method' => $_POST['mail_method'] ?? 'mail',
            'mail_smtp_host' => $_POST['mail_smtp_host'] ?? '',
            'mail_smtp_port' => $_POST['mail_smtp_port'] ?? '587',
            'mail_smtp_user' => $_POST['mail_smtp_user'] ?? '',
            'mail_smtp_pass' => $_POST['mail_smtp_pass'] ?? '',
            'mail_smtp_secure' => $_POST['mail_smtp_secure'] ?? 'tls',
            'mail_sendgrid_api_key' => $_POST['mail_sendgrid_api_key'] ?? '',
            'mail_disable_invoice' => isset($_POST['mail_disable_invoice']) ? 1 : 0,
            'mail_disable_order' => isset($_POST['mail_disable_order']) ? 1 : 0,
            'mail_disable_support' => isset($_POST['mail_disable_support']) ? 1 : 0,
            'mail_disable_service' => isset($_POST['mail_disable_service']) ? 1 : 0,
            'mail_disable_domain' => isset($_POST['mail_disable_domain']) ? 1 : 0,
            'mail_disable_general' => isset($_POST['mail_disable_general']) ? 1 : 0,
            'mail_log_emails' => isset($_POST['mail_log_emails']) ? 1 : 0,
            'mail_cc_admin' => isset($_POST['mail_cc_admin']) ? 1 : 0,
            'mail_bcc_admin' => isset($_POST['mail_bcc_admin']) ? 1 : 0,
        ];
        
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        
        $this->flash('success', 'Email settings saved successfully');
        $this->redirect(ADMIN_URL . '/settings/email');
    }
    
    /**
     * Show payment settings
     */
    public function payment()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings();
        
        // Get payment gateways
        $gateways = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('payment_gateways')} WHERE active = 1 ORDER BY sort_order ASC"
        );
        
        $this->render('admin.settings.payment', [
            'settings' => $settings,
            'gateways' => $gateways,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update payment settings
     */
    public function updatePayment()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $settings = [
            'payment_terms' => $_POST['payment_terms'] ?? '0',
            'payment_reminder_days' => $_POST['payment_reminder_days'] ?? '7',
            'invoice_payment_url' => $_POST['invoice_payment_url'] ?? '',
            'auto_cancel_unpaid' => isset($_POST['auto_cancel_unpaid']) ? 1 : 0,
            'auto_cancel_days' => $_POST['auto_cancel_days'] ?? '30',
            'auto_suspend_services' => isset($_POST['auto_suspend_services']) ? 1 : 0,
            'auto_suspend_days' => $_POST['auto_suspend_days'] ?? '14',
            'require_invoice_signing' => isset($_POST['require_invoice_signing']) ? 1 : 0,
        ];
        
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        
        $this->flash('success', 'Payment settings saved successfully');
        $this->redirect(ADMIN_URL . '/settings/payment');
    }
    
    /**
     * Show support settings
     */
    public function support()
    {
        $this->requireAdmin();
        
        $settings = $this->getSettings();
        
        // Get departments for selection
        $departments = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('departments')} ORDER BY name ASC"
        );
        
        $this->render('admin.settings.support', [
            'settings' => $settings,
            'departments' => $departments,
            'admin' => $this->getAdmin()
        ]);
    }
    
    /**
     * Update support settings
     */
    public function updateSupport()
    {
        $this->requireAdmin();
        $this->validateCsrf();
        
        $settings = [
            'default_department' => $_POST['default_department'] ?? '1',
            'ticket_auto_close_days' => $_POST['ticket_auto_close_days'] ?? '7',
            'ticket_prefix' => $_POST['ticket_prefix'] ?? 'TKT-',
            'default_priority' => $_POST['default_priority'] ?? 'medium',
            'require_login_tickets' => isset($_POST['require_login_tickets']) ? 1 : 0,
            'auto_response_enabled' => isset($_POST['auto_response_enabled']) ? 1 : 0,
            'auto_response_message' => $_POST['auto_response_message'] ?? '',
            'ticket_rating_enabled' => isset($_POST['ticket_rating_enabled']) ? 1 : 0,
        ];
        
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        
        $this->flash('success', 'Support settings saved successfully');
        $this->redirect(ADMIN_URL . '/settings/support');
    }
    
    /**
     * Get all settings from database
     */
    private function getSettings()
    {
        $settings = [];
        $rows = $this->db->fetchAll("SELECT * FROM {$this->db->table('settings')}");
        
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        return $settings;
    }
    
    /**
     * Save a single setting
     */
    private function saveSetting($key, $value)
    {
        $existing = $this->db->fetch(
            "SELECT id FROM {$this->db->table('settings')} WHERE `key` = ?",
            [$key]
        );
        
        if ($existing) {
            $this->db->update(
                'settings',
                ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$existing['id']]
            );
        } else {
            $this->db->insert('settings', [
                'key' => $key,
                'value' => $value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
